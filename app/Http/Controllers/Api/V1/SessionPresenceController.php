<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BookSession;
use App\Models\Room;
use App\Services\SessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class SessionPresenceController extends Controller
{
    protected SessionService $sessionService;

    public function __construct(SessionService $sessionService)
    {
        parent::__construct();
        $this->sessionService = $sessionService;
    }

    /**
     * POST /api/session/join
     */
    public function join(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'book_session_id' => 'required|exists:book_sessions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = auth('api')->id();
        $roomId = (int) $request->input('room_id');
        $bookSessionId = (int) $request->input('book_session_id');

        try {
            // Authorize room participant
            $room = Room::findOrFail($roomId);
            if ((int) $room->user_one_id !== $userId && (int) $room->user_two_id !== $userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized room access',
                ], 403);
            }

            // Authorize session participant
            $bookSession = BookSession::findOrFail($bookSessionId);
            if ((int) $bookSession->user_id !== $userId && (int) $bookSession->creator_id !== $userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized session access',
                ], 403);
            }

            $status = $this->sessionService->joinSession($roomId, $bookSessionId, $userId);

            return response()->json([
                'status' => true,
                'message' => 'Presence log recorded successfully',
                'data' => $status,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while joining the session',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/session/leave
     */
    public function leave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'book_session_id' => 'required|exists:book_sessions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = auth('api')->id();
        $roomId = (int) $request->input('room_id');
        $bookSessionId = (int) $request->input('book_session_id');

        try {
            // Authorize room participant
            $room = Room::findOrFail($roomId);
            if ((int) $room->user_one_id !== $userId && (int) $room->user_two_id !== $userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized room access',
                ], 403);
            }

            // Authorize session participant
            $bookSession = BookSession::findOrFail($bookSessionId);
            if ((int) $bookSession->user_id !== $userId && (int) $bookSession->creator_id !== $userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized session access',
                ], 403);
            }

            $status = $this->sessionService->leaveSession($roomId, $bookSessionId, $userId);

            return response()->json([
                'status' => true,
                'message' => 'Presence log closed successfully',
                'data' => $status,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while leaving the session',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
