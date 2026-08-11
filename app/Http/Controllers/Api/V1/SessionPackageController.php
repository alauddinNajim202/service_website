<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CreatorSessionPrice;
use App\Models\SessionPackage;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SessionPackageController extends Controller
{
    /**
     * Get active session packages.
     */
    public function index(Request $request)
    {
        $query = SessionPackage::query();

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        } else {
            $query->where('status', 'active');
        }

        $packages = $query->orderby('price', 'asc')->get();

        $type = $request->query('type') ?? $request->query('lang') ?? $request->query('locale');
        if ($type !== null) {
            $typeLower = strtolower(trim($type));
            $lang = 'en';
            if (in_array($typeLower, ['fr', 'french', 'francais', 'français', '1'])) {
                $lang = 'fr';
            } elseif (in_array($typeLower, ['es', 'spanish', 'espanol', 'español', '3'])) {
                $lang = 'es';
            } elseif (in_array($typeLower, ['en', 'english', '2'])) {
                $lang = 'en';
            }

            $packages->each(function ($package) use ($lang) {
                if ($lang === 'fr') {
                    $package->name = $package->name_fr ?: $package->name;
                    $package->description = $package->description_fr ?: $package->description;
                    $package->duration = $package->duration_fr ?: $package->duration;
                    $package->badge = $package->badge_fr ?: $package->badge;
                } elseif ($lang === 'es') {
                    $package->name = $package->name_es ?: $package->name;
                    $package->description = $package->description_es ?: $package->description;
                    $package->duration = $package->duration_es ?: $package->duration;
                    $package->badge = $package->badge_es ?: $package->badge;
                } else {
                    $package->name = $package->name_en ?: $package->name;
                    $package->description = $package->description_en ?: $package->description;
                    $package->duration = $package->duration_en ?: $package->duration;
                    $package->badge = $package->badge_en ?: $package->badge;
                }
            });
        }

        $user = User::find($request->user_id);
        $formattedPackages = $packages->map(function ($package) use ($user) {
            return [
                'id' => $package->id,
                'name' => $package->name,
                'type' => $package->type,
                'price' => $package->type == 'vip_access' ? ($user?->userPackgae()->where('session_package_id', $package->id)->value('price') ?? $package->price) : $package->price,
                'duration' => $package->duration,
                'description' => strip_tags($package->description),
                'badge' => $package->badge,
                'status' => $package->status,
                'featured' => (bool) $package->is_feature,
                'featuredText' => $package->feature_text,
                'created_at' => $package->created_at,
                'updated_at' => $package->updated_at,
            ];
        });

        return Helper::jsonResponse(true, 'Session packages fetched successfully', 200, [
            'packages' => $formattedPackages,
        ]);
    }

    /**
     * Store a new session package.
     *
     * Expected payload:
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
            'is_feature' => 'nullable|boolean',
            'feature_text' => 'required_with:is_feature|nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return Helper::jsonResponse(false, 'Validation failed', 422, [
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $data = $validator->validated();
            $data['status'] = $data['status'] ?? 'active';
            $data['is_feature'] = $request->boolean('is_feature');

            $package = SessionPackage::create($data);

            return Helper::jsonResponse(true, 'Session package created successfully', 201, [
                'package' => $package,
            ]);
        } catch (Exception $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 500);
        }
    }

    /**
     * Update VIP package price (per creator).
     *
     * Each creator can set their own price. Defaults remain from session_packages.
     *
     * Expected payload:
     *   - price (numeric, required, min:0)
     */
    public function updateVipPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return Helper::jsonResponse(false, 'Validation failed', 422, [
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $creator = Auth::guard('api')->user();

            $package = SessionPackage::where('type', 'vip_access')->first();

            if (! $package) {
                return Helper::jsonResponse(false, 'VIP package not found', 404);
            }

            $customPrice = CreatorSessionPrice::updateOrCreate(
                [
                    'creator_id' => $creator->id,
                    'session_package_id' => $package->id,
                ],
                [
                    'price' => $request->input('price'),
                ]
            );

            return Helper::jsonResponse(true, 'VIP package price updated successfully', 200, [
                'package' => [
                    'id' => $package->id,
                    'type' => $package->type,
                    'default_price' => (float) $package->price,
                    'your_price' => (float) $customPrice->price,
                    'duration' => $package->duration,
                    'updated_at' => $customPrice->updated_at,
                ],
            ]);
        } catch (Exception $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 500);
        }
    }
}
