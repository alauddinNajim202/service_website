<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\TestNotificationEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TestNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function test(){

        $user = auth('api')->user();
        $admin = User::role('admin', 'web')->first();

        $notiData = [
            'user_id' => $user->id,
            'title' => 'Test Notification Title.',
            'body' => 'Your Test Notification Body.',
            'icon'  => config('settings.logo')
        ];

        $admin->notify(new TestNotification($notiData, $admin->id));
        $user->notify(new TestNotification($notiData, $user->id));
        
        if(config('settings.reverb') == 'on'){
            broadcast(new TestNotificationEvent($notiData, $admin->id))->toOthers();
        }

        return true;
    }

    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();

            switch ($request->query('status')) {
                case 'unread':
                    $query = $user->unreadNotifications();
                    break;
                case 'read':
                    $query = $user->readNotifications();
                    break;
                default:
                    $query = $user->notifications();
            }

            $total_unread_notifications = $user->unreadNotifications()->count();
            
       
            $perPage = $request->input('per_page', 10);
            $currentPage = $request->input('current_page', 1);
            $notifications = $query->paginate($perPage, ['*'], 'page', $currentPage);


            return response()->json([
                'status'     => true,
                'message'    => 'All Notifications',
                'total_unread_notifications' => $total_unread_notifications,
                'code'       => 200,
                'data'       => $notifications->map(function($notification) {
                    return [
                        'id'=> $notification->id,
                        'is_read'=> $notification->read_at != null ? true : false,
                        'data'=> $notification->data,
                    ];
                }),
                'pagination' => [
                    'total_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'current_page' => $notifications->currentPage(),
                    'total_item' => $notifications->total(),
                ]
            ], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return back();
        }
    }

    public function read($id = null)
    {
        try {
            if($id != null) {
               $notification = auth('api')->user()->notifications()->find($id);
                if($notification) {
                    $notification->markAsRead();
                }
                return response()->json([
                    'status'     => true,
                    'message'    => 'Single Notification',
                    'code'       => 200,
                    'data'       => $notification
                ], 200); 
            }else {
                auth('api')->user()->unreadNotifications->markAsRead();
                return response()->json([
                    'status'     => true,
                    'message'    => 'All Notifications Marked As Read',
                    'code'       => 200,
                    'data'       => null
                ], 200);
            }
            
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return back();
        }
    }

}
