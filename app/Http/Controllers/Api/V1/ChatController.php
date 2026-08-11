<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageSendEvent;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\BlockedUser;
use App\Models\BookSession;
use App\Models\Chat;
use App\Models\PinnedChat;
use App\Models\Room;
use App\Models\User;
use App\Notifications\MessageNotifications;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function index() {}

    public function list(Request $request): JsonResponse
    {
        // Get the authenticated user
        $authUser = Auth::guard('api')->user();
        $search = $request->input('search');

        // Fetch users who are connected as senders or receivers with the authenticated user
        $usersQuery = User::select('id', 'name', 'email', 'avatar', 'last_activity_at')
            ->where(function ($query) use ($authUser) {
                $query->whereHas('senders', function ($q) use ($authUser) {
                    $q->where('receiver_id', $authUser->id);
                })
                    ->orWhereHas('receivers', function ($q) use ($authUser) {
                        $q->where('sender_id', $authUser->id);
                    });
            })
            ->where('id', '!=', $authUser->id);

        if (! empty($search)) {
            $usersQuery->where('name', 'like', "%{$search}%");
        }

        $users = $usersQuery->get();

        $usersWithMessages = $users->map(function ($user) use ($authUser) {
            $lastChat = Chat::where(function ($query) use ($user, $authUser) {
                $query->where('sender_id', $authUser->id)
                    ->where('receiver_id', $user->id);
            })
                ->orWhere(function ($query) use ($user, $authUser) {
                    $query->where('sender_id', $user->id)
                        ->where('receiver_id', $authUser->id);
                })
                ->latest()
                ->first();

            $user->last_chat = $lastChat;
            $user->is_pinned = PinnedChat::where('user_id', $authUser->id)->where('pinned_user_id', $user->id)->exists();
            $user->is_blocked = BlockedUser::where([
                ['user_id', $authUser->id],
                ['blocked_user_id', $user->id],
            ])->orWhere([
                ['user_id', $user->id],
                ['blocked_user_id', $authUser->id],
            ])->exists();
            $user->blocked_by_me = BlockedUser::where('user_id', $authUser->id)->where('blocked_user_id', $user->id)->exists();

            return $user;
        });

        $sortedUsers = $usersWithMessages->sortByDesc(function ($user) {
            return [
                $user->is_pinned ? 1 : 0,
                optional($user->last_chat)->created_at,
            ];
        })->values();

        $data = [
            'users' => $sortedUsers,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Chat retrieved successfully',
            'data' => $data,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function togglePin($user_id): JsonResponse
    {
        $authUser = Auth::guard('api')->user();

        $pinnedChat = PinnedChat::where('user_id', $authUser->id)
            ->where('pinned_user_id', $user_id)
            ->first();

        if ($pinnedChat) {
            $pinnedChat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Chat unpinned successfully',
                'is_pinned' => false,
            ], 200);
        } else {
            PinnedChat::create([
                'user_id' => $authUser->id,
                'pinned_user_id' => $user_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chat pinned successfully',
                'is_pinned' => true,
            ], 200);
        }
    }

    public function pinnedList(Request $request): JsonResponse
    {
        $authUser = Auth::guard('api')->user();
        $search = $request->input('search');

        $usersQuery = User::select('id', 'name', 'email', 'avatar', 'last_activity_at')->whereHas('pinnedChats', function ($query) use ($authUser) {
            $query->where('user_id', $authUser->id);
        });

        if (! empty($search)) {
            $usersQuery->where('name', 'like', "%{$search}%");
        }

        $users = $usersQuery->get();

        $usersWithMessages = $users->map(function ($user) use ($authUser) {
            $lastChat = Chat::where(function ($query) use ($user, $authUser) {
                $query->where('sender_id', $authUser->id)
                    ->where('receiver_id', $user->id);
            })
                ->orWhere(function ($query) use ($user, $authUser) {
                    $query->where('sender_id', $user->id)
                        ->where('receiver_id', $authUser->id);
                })
                ->latest()
                ->first();

            $user->last_chat = $lastChat;
            $user->is_pinned = true;
            $user->is_blocked = BlockedUser::where([
                ['user_id', $authUser->id],
                ['blocked_user_id', $user->id],
            ])->orWhere([
                ['user_id', $user->id],
                ['blocked_user_id', $authUser->id],
            ])->exists();
            $user->blocked_by_me = BlockedUser::where('user_id', $authUser->id)->where('blocked_user_id', $user->id)->exists();

            return $user;
        });

        $sortedUsers = $usersWithMessages->sortByDesc(function ($user) {
            return optional($user->last_chat)->created_at;
        })->values();

        $data = [
            'users' => $sortedUsers,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Pinned chat list retrieved successfully',
            'data' => $data,
        ], 200);
    }

    public function search(Request $request): JsonResponse
    {
        $user_id = Auth::guard('api')->id();

        $keyword = $request->get('keyword');
        $users = User::select('id', 'name', 'email', 'avatar', 'last_activity_at')
            ->where('id', '!=', $user_id)
            ->where('name', 'LIKE', "%{$keyword}%")->orWhere('email', 'LIKE', "%{$keyword}%")
            ->get();

        $data = [
            'users' => $users,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Chat retrieved successfully',
            'data' => $data,
        ], 200);
    }

    /**
     ** Get messages between the authenticated user and another user
     *
     * @param  User  $user
     */
    public function conversation($receiver_id, Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 50);
        $page = (int) $request->input('current_page', 1);

        $sender_id = Auth::guard('api')->id();

        if ($receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'Cannot chat with yourself', 'data' => [], 'code' => 403]);
        }

        Chat::where('receiver_id', $sender_id)->where('sender_id', $receiver_id)->update(['status' => 'read']);

        $chat = Chat::query()
            ->where(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);
            })
            ->orWhere(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);
            })
            ->with([
                'sender:id,name,email,avatar,last_activity_at',
                'receiver:id,name,email,avatar,last_activity_at',
                'room:id,user_one_id,user_two_id',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->first();

        if (! $room) {
            $room = Room::create([
                'user_one_id' => $sender_id,
                'user_two_id' => $receiver_id,
            ]);
        }

        $isBlocked = BlockedUser::where([
            ['user_id', $sender_id],
            ['blocked_user_id', $receiver_id],
        ])
            ->orWhere([
                ['user_id', $receiver_id],
                ['blocked_user_id', $sender_id],
            ])
            ->exists();

        $isPinned = PinnedChat::where('user_id', $sender_id)
            ->where('pinned_user_id', $receiver_id)
            ->exists();

        $blocked_by_me = BlockedUser::where('user_id', auth()->guard('api')->user()->id)
            ->where('blocked_user_id', $receiver_id)
            ->exists();

        $activeSession = BookSession::with(['sessionPackage', 'sessionUsage'])
            ->where('payment_status', 'paid')
            ->where(function ($query) use ($sender_id, $receiver_id) {
                $query->where(function ($q) use ($sender_id, $receiver_id) {
                    $q->where('user_id', $sender_id)
                        ->where('creator_id', $receiver_id);
                })->orWhere(function ($q) use ($sender_id, $receiver_id) {
                    $q->where('user_id', $receiver_id)
                        ->where('creator_id', $sender_id);
                });
            })
            ->latest()
            ->first();

        if ($activeSession) {
            $package = $activeSession->sessionPackage;

            if ($package) {
                if ($package->type === 'vip_access') {

                    // VIP package is valid for 1 month
                    $endDate = Carbon::parse($activeSession->created_at)->addMonth();

                    if ($endDate->isPast()) {
                        $activeSession = null;
                    }

                } else {

                    // Non-VIP package must have an unfinished session
                    if (
             
                        $activeSession?->sessionUsage?->is_completed
                    ) {
                        $activeSession = null;
                    }

                }
            } else {
                $activeSession = null;
            }
        }
        $isRunningPackage = null;

        if ($activeSession && $activeSession->sessionPackage) {
            $package = $activeSession->sessionPackage;
            $usage = $activeSession->sessionUsage;

            if ($package->type === 'vip_access') {
                $startedDate = $usage ? ($usage->started_at ?? $activeSession->created_at) : $activeSession->created_at;
                $endDate = Carbon::parse($startedDate)->addMonth()->setTimezone('UTC');

                $isRunningPackage = [
                    'id' => $activeSession->id,
                    'name' => $package->name,
                    'type' => $package->type,
                    'duration_value' => $package->duration_value,
                    'remaining' => $endDate->format('Y-m-d H:i:s'),
                    'remaining_iso' => $endDate->format('Y-m-d\TH:i:s\Z'),
                ];
            } else {
                $limitSeconds = ((int) ($package->duration_value ?? 0)) * 60;

                $usage = $activeSession->sessionUsage;

                $usedSeconds = $usage?->used_seconds ?? 0;

                // add live running time if session is currently active
                if (
                    $usage &&
                    ! $usage->is_completed &&
                    $usage->timer_started_at
                ) {
                    $usedSeconds += Carbon::parse($usage->timer_started_at)
                        ->diffInSeconds(now());
                }

                $remainingSeconds = max(0, $limitSeconds - $usedSeconds);

                $hours = floor($remainingSeconds / 3600);
                $minutes = floor(($remainingSeconds % 3600) / 60);
                $seconds = $remainingSeconds % 60;
                $remainingCustom = sprintf('0000-00-00 %02d:%02d:%02d', $hours, $minutes, $seconds);
                $remainingIso = sprintf('0000-00-00T%02d:%02d:%02dZ', $hours, $minutes, $seconds);

                $isRunningPackage = [
                    'id' => $activeSession->id,
                    'name' => $package->name,
                    'type' => $package->type,
                    'duration_value' => $package->duration_value,
                    'remaining' => $remainingCustom,
                    'remaining_iso' => $remainingIso,
                ];
            }
        }

        $data = [
            'receiver' => User::select('id', 'name', 'email', 'avatar', 'last_activity_at')->where('id', $receiver_id)->first(),
            'sender' => User::select('id', 'name', 'email', 'avatar', 'last_activity_at')->where('id', $sender_id)->first(),
            'room' => $room,
            'is_blocked' => $isBlocked,
            'is_pinned' => $isPinned,
            'blocked_by_me' => $blocked_by_me,
            'is_running_package' => $isRunningPackage ?? false,
            'chat' => $chat->items(),
            'pagination' => [
                'total_page' => $chat->lastPage(),
                'per_page' => $chat->perPage(),
                'total_item' => $chat->total(),
                'current_page' => $chat->currentPage(),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data' => $data,
            'code' => 200,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /**
     *! Send a message to another user
     *
     * @param  User  $user
     */
    public function send($receiver_id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:255',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }
        $sender_id = Auth::guard('api')->id();
        $isBlocked = BlockedUser::where([
            ['user_id', $sender_id],
            ['blocked_user_id', $receiver_id],
        ])
            ->orWhere([
                ['user_id', $receiver_id],
                ['blocked_user_id', $sender_id],
            ])
            ->exists();
        if ($isBlocked) {
            return response()->json(['success' => false, 'message' => 'You are blocked by this user', 'data' => [], 'code' => 403]);
        }
        $sender_id = Auth::guard('api')->id();

        $receiver_exist = User::where('id', $receiver_id)->first();
        if (! $receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot chat with yourself', 'data' => [], 'code' => 200]);
        }

        $room = Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->first();

        if (! $room) {
            $room = Room::create([
                'user_one_id' => $sender_id,
                'user_two_id' => $receiver_id,
            ]);
        }

        $file = null;
        if ($request->hasFile('file')) {
            $file = Helper::fileUpload($request->file('file'), 'chat', time().'_'.getFileName($request->file('file')));
        }

        $chat = Chat::create([
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'text' => $request->text,
            'file' => $file,
            'room_id' => $room->id,
            'status' => 'sent',
        ]);

        // * Load the sender's information
        $chat->load([
            'sender:id,name,email,avatar,last_activity_at',
            'receiver:id,name,email,avatar,last_activity_at',
            'room:id,user_one_id,user_two_id',
        ]);

        $data = [
            'receiver' => User::select('id', 'name', 'email', 'avatar', 'last_activity_at')->where('id', $receiver_id)->first(),
            'sender' => User::select('id', 'name', 'email', 'avatar', 'last_activity_at')->where('id', $sender_id)->first(),
            'room' => $room,
            'chat' => $chat,
        ];

        broadcast(new MessageSendEvent($data))->toOthers();

        $user = User::find($receiver_id);

        if ($user->new_message_notification == 1) {
            $user->notify(new MessageNotifications($data));
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $data,
            'code' => 200,
        ]);
    }

    public function seenAll($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        $receiver_exist = User::where('id', $receiver_id)->first();
        if (! $receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot chat with yourself', 'data' => [], 'code' => 200]);
        }

        $chat = Chat::where('receiver_id', $sender_id)->where('sender_id', $receiver_id)->update(['status' => 'read']);

        $data = [
            'chat' => $chat,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Message seen successfully',
            'data' => $data,
            'code' => 200,
        ]);
    }

    public function seenSingle($chat_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        $chat = Chat::where('id', $chat_id)->where('receiver_id', $sender_id)->update(['status' => 'read']);

        $data = [
            'chat' => $chat,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Message seen successfully',
            'data' => $data,
            'code' => 200,
        ]);
    }

    public function room($receiver_id)
    {
        $sender_id = Auth::guard('api')->id();

        $receiver_exist = User::where('id', $receiver_id)->first();

        if (! $receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot chat with yourself', 'data' => [], 'code' => 200]);
        }

        $room = Room::with(['userOne:id,name,email,avatar,last_activity_at', 'userTwo:id,name,email,avatar,last_activity_at'])
            ->where(function ($query) use ($receiver_id, $sender_id) {
                $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
            })->orWhere(function ($query) use ($receiver_id, $sender_id) {
                $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
            })->first();

        if (! $room) {
            $room = Room::create([
                'user_one_id' => $sender_id,
                'user_two_id' => $receiver_id,
            ]);
        }

        $data = [
            'room' => $room,
        ];

        return response()->json(['success' => true, 'message' => 'Group retrieved successfully', 'data' => $data, 'code' => 200]);
    }

    public function deleteConversation($receiver_id): JsonResponse
    {
        $sender_id = Auth::guard('api')->id();

        $receiver_exist = User::where('id', $receiver_id)->first();
        if (! $receiver_exist || $receiver_id == $sender_id) {
            return response()->json(['success' => false, 'message' => 'User not found or cannot delete conversation with yourself', 'data' => [], 'code' => 403]);
        }

        // Delete all chat messages between the two users
        Chat::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);
        })->delete();

        // Delete the room between the two users
        Room::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->delete();

        // Remove pinned chat entry if exists
        PinnedChat::where('user_id', $sender_id)->where('pinned_user_id', $receiver_id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully',
            'code' => 200,
        ]);
    }

    public function toggleBlock($user_id): JsonResponse
    {
        $authUser = Auth::guard('api')->user();

        if ($user_id == $authUser->id) {
            return response()->json(['success' => false, 'message' => 'You cannot block yourself', 'code' => 403]);
        }

        $targetUser = User::find($user_id);
        if (! $targetUser) {
            return response()->json(['success' => false, 'message' => 'User not found', 'code' => 404]);
        }

        $blocked = BlockedUser::where('user_id', $authUser->id)
            ->where('blocked_user_id', $user_id)
            ->first();

        if ($blocked) {
            $blocked->delete();

            return response()->json([
                'success' => true,
                'message' => 'User unblocked successfully',
                'is_blocked' => false,
                'code' => 200,
            ]);
        }

        BlockedUser::create([
            'user_id' => $authUser->id,
            'blocked_user_id' => $user_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User blocked successfully',
            'is_blocked' => true,
            'code' => 200,
        ]);
    }

    public function blockedList(): JsonResponse
    {
        $authUser = Auth::guard('api')->user();

        $blockedUsers = BlockedUser::where('user_id', $authUser->id)
            ->with('blockedUser:id,name,email,avatar')
            ->latest()
            ->get()
            ->pluck('blockedUser');

        return response()->json([
            'success' => true,
            'message' => 'Blocked users retrieved successfully',
            'data' => ['users' => $blockedUsers],
            'code' => 200,
        ]);
    }
}
