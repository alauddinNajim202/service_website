<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Favourite;
use App\Models\User;
use Illuminate\Http\Request;
use Throwable;

class FavouriteController extends Controller
{
    public function toggle(Request $request)
    {
        try {
            $userId = $request->input('user_id');

            if (empty($userId)) {
                return Helper::jsonErrorResponse(null, 422, 'User ID is required.');
            }

            $authUser = auth('api')->user();

            if ($authUser->id == $userId) {
                return Helper::jsonErrorResponse(null, 422, 'You cannot favourite yourself.');
            }

            $target = User::findOrFail($userId);

            $existing = Favourite::where('user_id', $authUser->id)
                ->where('favourite_user_id', $userId)
                ->first();

            if ($existing) {
                $existing->delete();

                return Helper::jsonResponse(true, 'Removed from favourites.', 200);
            }

            Favourite::create([
                'user_id' => $authUser->id,
                'favourite_user_id' => $userId,
            ]);

            return Helper::jsonResponse(true, 'Added to favourites.', 200);
        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    // Get my favourites list
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();

            $query = $user->favourites()
                ->whereHas('favouriteUser',function ($q){
                    $q->whereNotNull('stripe_account_id')->where('is_bank_added',1);
                })
                ->with(['favouriteUser.category'])
                ->latest();

            // Apply search filter if present
            if ($request->has('search') && ! empty($request->input('search'))) {
                $search = $request->input('search');
                $query->whereHas('favouriteUser', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('short_bio', 'like', '%'.$search.'%');
                });
            }

            $perPage = (int) $request->input('per_page', 10);
            $page = (int) $request->input('current_page', $request->input('page', 1));
            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            $favouritesData = collect($paginator->items())
                ->filter(fn ($f) => ! is_null($f->favouriteUser))
                ->map(function ($f) {
                    $u = $f->favouriteUser;

                    return [
                        'id' => $u->id,
                        'slug' => $u->slug,
                        'name' => $u->name,
                        'email' => $u->email,
                        'avatar' => $u->avatar,
                        'category' => $u->category ? [
                            'id' => $u->category->id,
                            'name' => $u->category->name,
                        ] : null,
                        'short_bio' => $u->short_bio,
                        'role' => $u->role,
                        'status' => 'online',
                        'isfavourite' => true,
                    ];
                })->values()->all();

            $data = [
                'favourites' => $favouritesData,
                'pagination' => [
                    'total_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total_item' => $paginator->total(),
                    'current_page' => $paginator->currentPage(),
                ],
            ];

            return Helper::jsonResponse(true, 'Favourites retrieved.', 200, $data);
        } catch (Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }
}
