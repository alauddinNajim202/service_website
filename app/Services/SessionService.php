<?php

namespace App\Services;

use App\Models\BookSession;
use App\Models\SessionPresenceLog;
use App\Models\SessionUsage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SessionService
{
    private function limitSeconds($bookSessionId)
    {
        $session = BookSession::with('sessionPackage')->find($bookSessionId);

        if (!$session || !$session->sessionPackage) {
            return 0;
        }

        return ($session->sessionPackage->duration_value ?? 0) * 60;
    }

    /**
     * TRUE if BOTH users are currently present.
     */
    private function isSessionActive($bookSessionId)
    {
        return SessionPresenceLog::where('book_session_id', $bookSessionId)
            ->whereNull('left_at')
            ->distinct('user_id')
            ->count('user_id') === 2;
    }

    private function usage($bookSessionId)
    {
        return SessionUsage::firstOrCreate([
            'book_session_id' => $bookSessionId
        ]);
    }

    /**
     * User joined the room.
     */
    public function joinSession($roomId, $bookSessionId, $userId)
    {
        return DB::transaction(function () use ($roomId, $bookSessionId, $userId) {

            SessionPresenceLog::firstOrCreate(
                [
                    'book_session_id' => $bookSessionId,
                    'user_id' => $userId,
                    'left_at' => null,
                ],
                [
                    'room_id' => $roomId,
                    'joined_at' => now(),
                ]
            );

            $usage = $this->usage($bookSessionId);

            if ($this->isSessionActive($bookSessionId) && !$usage->is_completed) {

                if (!$usage->timer_started_at) {
                    $usage->timer_started_at = now();
                    $usage->started_at = $usage->started_at ?? now();
                    $usage->save();
                }
            }

            return $usage;
        });
    }

    /**
     * User left the room.
     */
    public function leaveSession($roomId, $bookSessionId, $userId)
    {
        return DB::transaction(function () use ($roomId, $bookSessionId, $userId) {

            $log = SessionPresenceLog::where('book_session_id', $bookSessionId)
                ->where('user_id', $userId)
                ->whereNull('left_at')
                ->latest()
                ->first();

            if ($log) {
                $log->update([
                    'left_at' => now(),
                    'duration_seconds' => Carbon::parse($log->joined_at)
                        ->diffInSeconds(now()),
                ]);
            }

            $usage = $this->usage($bookSessionId);

            if (
                !$this->isSessionActive($bookSessionId)
                && $usage->timer_started_at
                && !$usage->is_completed
            ) {

                $elapsed = Carbon::parse($usage->timer_started_at)
                    ->diffInSeconds(now());

                $usage->used_seconds += $elapsed;
                $usage->timer_started_at = null;

                $limit = $this->limitSeconds($bookSessionId);

                if ($usage->used_seconds >= $limit) {
                    $usage->used_seconds = $limit;
                    $usage->is_completed = true;
                    $usage->ended_at = now();
                }

                $usage->save();
            }

            return $usage;
        });
    }

    /**
     * Call this every 5-10 seconds while the session is open.
     */
    public function getSessionStatus($bookSessionId)
    {
        $usage = $this->usage($bookSessionId);

        $limit = $this->limitSeconds($bookSessionId);

        $usedSeconds = $usage->used_seconds;

        if ($usage->timer_started_at && !$usage->is_completed) {

            $usedSeconds += Carbon::parse($usage->timer_started_at)
                ->diffInSeconds(now());

            if ($usedSeconds >= $limit) {

                $usage->used_seconds = $limit;
                $usage->timer_started_at = null;
                $usage->is_completed = true;
                $usage->ended_at = now();
                $usage->save();

                $usedSeconds = $limit;
            }
        }

        return [
            'used_seconds'      => $usedSeconds,
            'remaining_seconds' => max(0, $limit - $usedSeconds),
            'limit_seconds'     => $limit,
            'is_completed'      => $usage->is_completed,
            'is_running'        => $usage->timer_started_at !== null,
        ];
    }
}