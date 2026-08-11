<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageSendEvent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Room;
use App\Models\User;
use App\Notifications\MessageNotifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GeminiImageController extends Controller
{
    /**
     * Generate an image using the Gemini API based on a user-provided prompt
     * and send it as a chat message in the given room.
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt'      => 'required|string|max:1000',
            'room_id'     => 'required|integer|exists:rooms,id',
            'receiver_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'code'    => 422,
            ], 422);
        }

        $senderId   = Auth::guard('api')->id();
        $receiverId = $request->input('receiver_id');
        $roomId     = $request->input('room_id');

        // Verify the sender belongs to this room
        $room = Room::where('id', $roomId)
            ->where(function ($query) use ($senderId) {
                $query->where('user_one_id', $senderId)
                      ->orWhere('user_two_id', $senderId);
            })->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'You do not belong to this room.',
                'code'    => 403,
            ], 403);
        }

        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            Log::error('Gemini API key is not configured.');

            return response()->json([
                'success' => false,
                'message' => 'Image generation service is not configured.',
                'code'    => 500,
            ], 500);
        }

        try {
            $model = config('services.gemini.image_model', 'gemini-2.0-flash-exp');

            $response = Http::timeout(120)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $request->input('prompt'),
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'responseModalities' => ['TEXT', 'IMAGE'],
                    ],
                ]
            );

            if ($response->failed()) {
                Log::error('Gemini API request failed.', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate image. Please try again later.',
                    'code'    => 502,
                ], 502);
            }

            $data = $response->json();

            $imageData    = null;
            $mimeType     = null;
            $textResponse = null;

            if (isset($data['candidates'][0]['content']['parts'])) {
                foreach ($data['candidates'][0]['content']['parts'] as $part) {
                    if (isset($part['inlineData'])) {
                        $imageData = $part['inlineData']['data'];
                        $mimeType  = $part['inlineData']['mimeType'] ?? 'image/png';
                    }
                    if (isset($part['text'])) {
                        $textResponse = $part['text'];
                    }
                }
            }

            if (!$imageData) {
                return response()->json([
                    'success' => false,
                    'message' => 'No image was generated. Try a different prompt.',
                    'data'    => [
                        'text_response' => $textResponse,
                    ],
                    'code'    => 422,
                ], 422);
            }

            $decodedImage = base64_decode($imageData);

            $extension = match ($mimeType) {
                'image/png'  => 'png',
                'image/jpeg' => 'jpg',
                'image/webp' => 'webp',
                'image/gif'  => 'gif',
                default      => 'png',
            };

            $folder   = 'gemini-images';
            $fileName = 'gemini_' . time() . '_' . Str::random(8) . '.' . $extension;
            $savePath = public_path('uploads/' . $folder);

            if (!file_exists($savePath)) {
                mkdir($savePath, 0777, true);
            }

            file_put_contents($savePath . '/' . $fileName, $decodedImage);

            $imageUrl = 'uploads/' . $folder . '/' . $fileName;

            // Save the generated image as a chat message in the room
            $chat = Chat::create([
                'sender_id'   => $senderId,
                'receiver_id' => $receiverId,
                'text'        => $request->input('prompt'),
                'file'        => $imageUrl,
                'room_id'     => $roomId,
            ]);

            $chat->load([
                'sender:id,name,email,avatar,last_activity_at',
                'receiver:id,name,email,avatar,last_activity_at',
                'room:id,user_one_id,user_two_id',
            ]);

            $broadcastData = [
                'receiver' => User::select('id', 'name', 'email', 'avatar', 'last_activity_at')->where('id', $receiverId)->first(),
                'sender'   => User::select('id', 'name', 'email', 'avatar', 'last_activity_at')->where('id', $senderId)->first(),
                'room'     => $room,
                'chat'     => $chat,
            ];

            broadcast(new MessageSendEvent($broadcastData))->toOthers();

            $receiver = User::find($receiverId);
            if ($receiver && $receiver->new_message_notification == 1) {
                $receiver->notify(new MessageNotifications($broadcastData));
            }

            return response()->json([
                'success' => true,
                'message' => 'Image generated and sent successfully.',
                'data'    => [
                    'image_url'     => $imageUrl,
                    'full_url'      => asset($imageUrl),
                    'prompt'        => $request->input('prompt'),
                    'text_response' => $textResponse,
                    'chat'          => $chat,
                    'room'          => $room,
                    'sender_id'     => $senderId,
                    'receiver_id'   => $receiverId,
                ],
                'code'    => 200,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Gemini image generation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the image.',
                'code'    => 500,
            ], 500);
        }
    }
}
