<?php

namespace App\Http\Controllers\Api\V1\Frontend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\BookSession;
use App\Models\TopCreator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CreatorController extends Controller
{
    public function topCreators(Request $request): JsonResponse
    {
        try {
            $topCreatorIds = TopCreator::where('is_top', true)->pluck('creator_id');

            $query = User::role('creator', 'api')
                ->whereNotNull('stripe_account_id')
                ->where('is_bank_added', 1)
                ->whereIn('id', $topCreatorIds)
                ->with('category');

            if ($request->filled('search')) {
                $search = $request->input('search');

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('short_bio', 'like', "%$search%");
                });
            }

            $creators = $query->get();

            $data = $creators->map(function ($user) {
                return [
                    'id' => $user->id,
                    'slug' => $user->slug,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'category' => $user->category ? [
                        'id' => $user->category->id,
                        'name' => $user->category->name,
                    ] : null,
                    'short_bio' => $user->short_bio,
                    'role' => $user->role,
                    'status' => 'online',
                    'isfavourite' => auth('api')->check()
                        && auth('api')->user()->favourites()
                            ->where('favourite_user_id', $user->id)
                            ->exists(),
                ];
            });

            return Helper::jsonResponse(true, 'Top creators fetched successfully', 200, [
                'top_creators' => $data
            ]);

        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {

            $query = User::role('creator', 'api')
                ->whereNotNull('stripe_account_id')
                ->where('is_bank_added', 1)
                ->with('category');

            if ($request->filled('search')) {
                $search = $request->input('search');

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('short_bio', 'like', "%$search%");
                });
            }

            $perPage = (int) $request->input('per_page', 10);
            $page = (int) $request->input('current_page', 1);

            $creatorsPaginator = $query->paginate($perPage, ['*'], 'page', $page);

            $creatorsData = collect($creatorsPaginator->items())->map(function ($user) {

                $activeSession = null;

                if (auth('api')->check()) {

                    $activeSession = BookSession::with(['sessionPackage', 'sessionUsage'])
                        ->where('user_id', auth('api')->id())
                        ->where('creator_id', $user->id)
                        ->where('payment_status', 'paid')
                        ->latest()
                        ->first();

                    if ($activeSession && $activeSession->sessionPackage) {

                        $package = $activeSession->sessionPackage;

                        if ($package->type === 'vip_access') {

                            $expiry = Carbon::parse($activeSession->created_at)->addMonth();

                            if ($expiry->isPast()) {
                                $activeSession = null;
                            }

                        } else {

                            if (
                                $activeSession?->sessionUsage?->is_completed
                            ) {
                                $activeSession = null;
                            }
                        }
                    }
                }

                return [
                    'id' => $user->id,
                    'slug' => $user->slug,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'category' => $user->category ? [
                        'id' => $user->category->id,
                        'name' => $user->category->name,
                    ] : null,
                    'short_bio' => $user->short_bio,
                    'role' => $user->role,
                    'status' => 'online',
                    'isfavourite' => auth('api')->check()
                        && auth('api')->user()->favourites()
                            ->where('favourite_user_id', $user->id)
                            ->exists(),
                    'active_package' => $activeSession ? [
                        'has_active_package' => true,
                        'active_package_name' => $activeSession->sessionPackage?->name,
                    ] : null,
                ];
            });

            return Helper::jsonResponse(true, 'Creators fetched successfully', 200, [
                'creators' => $creatorsData,
                'pagination' => [
                    'total_page' => $creatorsPaginator->lastPage(),
                    'per_page' => $creatorsPaginator->perPage(),
                    'total_item' => $creatorsPaginator->total(),
                    'current_page' => $creatorsPaginator->currentPage(),
                ],
            ]);

        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function show($id): JsonResponse
    {
        try {

            $creator = User::role('creator', 'api')
                ->with(['category', 'posts' => fn ($q) => $q->latest()])
                ->where(fn ($q) => $q->where('id', $id)->orWhere('slug', $id))
                ->first();

            if (! $creator) {
                return Helper::jsonErrorResponse('Creator not found', 404);
            }

            $activeSession = null;

            if (auth('api')->check()) {

                $activeSession = BookSession::with(['sessionPackage', 'sessionUsage'])
                    ->where('user_id', auth('api')->id())
                    ->where('creator_id', $creator->id)
                    ->where('payment_status', 'paid')
                    ->latest()
                    ->first();

                if ($activeSession && $activeSession->sessionPackage) {

                    $package = $activeSession->sessionPackage;

                    if ($package->type === 'vip_access') {

                        $expiry = Carbon::parse($activeSession->created_at)->addMonth();

                        if ($expiry->isPast()) {
                            $activeSession = null;
                        }

                    } else {

                        if (
                            !$activeSession->sessionUsage ||
                            $activeSession->sessionUsage->is_completed
                        ) {
                            $activeSession = null;
                        }
                    }
                }
            }

            $creatorData = [
                'id' => $creator->id,
                'slug' => $creator->slug,
                'name' => $creator->name,
                'display_name' => $creator->name,
                'email' => $creator->email,
                'avatar' => $creator->avatar,
                'category' => $creator->category ? [
                    'id' => $creator->category->id,
                    'name' => $creator->category->name,
                ] : null,
                'short_bio' => $creator->short_bio,
                'role' => $creator->role,
                'status' => $creator->status ?? 'online',
                'isfavourite' => true,
                'active_package' => $activeSession ? [
                    'has_active_package' => true,
                    'active_package_name' => $activeSession->sessionPackage?->name,
                ] : null,
            ];

            return Helper::jsonResponse(true, 'Creator details fetched successfully', 200, $creatorData);

        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }
}