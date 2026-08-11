<?php

namespace App\Http\Controllers\Api\V1\Frontend;

use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\TopCreator;
use App\Models\User;
use App\Traits\CMSData;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use CMSData;

    public function index(Request $request)
    {
        $data = [];

        $cmsData = CMSData::all()->makeHidden(['created_at', 'updated_at']);

        $data['home_example'] = $cmsData->where('page', PageEnum::HOME)->where('section', SectionEnum::EXAMPLE)->first();
        $data['home_examples'] = $cmsData->where('page', PageEnum::HOME)->where('section', SectionEnum::EXAMPLES)->values();
        $data['home_about'] = $cmsData->where('page', PageEnum::HOME)->where('section', SectionEnum::ABOUT)->first();

        $safe_space = $cmsData->where('page', PageEnum::HOME)->where('section', SectionEnum::SAFE_SPACE)->first();
        $lang = $request->lang;

        if ($safe_space) {
            $title = $safe_space->title;
            $description = $safe_space->description;

            if ($lang === 'fr') {
                $title = $safe_space->title_fr ?? $title;
                $description = strip_tags($safe_space->description_fr ?? $description);
            } elseif ($lang === 'es') {
                $title = $safe_space->title_es ?? $title;
                $description = strip_tags($safe_space->description_es ?? $description);
            } elseif ($lang === 'en') {
                $title = $safe_space->title_en ?? $title;
                $description = strip_tags($safe_space->description_en ?? $description);
            } else {
                $description = strip_tags($description);
            }

            $data['home_safe_space'] = [
                'title' => $title,
                'description' => $description,
            ];
        } else {
            $data['home_safe_space'] = null;
        }

        $data['total_users'] = User::count();

        // Settings
        $setting = Setting::first();
        // Top Creators
        $topCreatorIds = TopCreator::where('is_top', true)->pluck('creator_id');
        $topCreators = User::role('creator', 'api')
            ->whereNotNull('stripe_account_id')
            ->where('is_bank_added', 1)
            ->whereIn('id', $topCreatorIds)
            ->with('category')
            ->get();

        $data['best_creators'] = $topCreators->map(function ($user) {
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
                'isfavourite' => auth('api')->check() && auth('api')->user()->favourites()->where('favourite_user_id', $user->id)->exists(),
            ];
        })->all();

        $data['hero_section'] = [];

        $data['our_offers'] = \App\Models\SessionPackage::where('status', 'active')->orderBy('id','desc')->get()->map(function ($package) use ($request) {
        $name = $package->name;
        $description = $package->description;
        $description = strip_tags($description);
        if($request->lang == 'fr'){
            $name = $package->name_fr;
            $description = strip_tags($package->description_fr);
        }
        if($request->lang == 'es'){
            $name = $package->name_es;
            $description = strip_tags($package->description_es);
        }
        if($request->lang == 'en'){
            $name = $package->name_en;
            $description = strip_tags($package->description_en);
        }
        return [
            'id' => $package->id,
            'package_name' => $name,
            'type' => $package->type,
            'money' => $package->price,
            'duration'=>$package->duration,
            'description'=>$description,
            'is_featured'=>$package->is_feature ? true : false,
            'featured_text'=>$package->feature_text,
        ];
        })->all();

        $data['footer'] = [
            'social_links' => [
                'facebook' => $setting->facebook_url ?? "",
                'instagram' => $setting->instagram_url ?? "",
                'twitter' => $setting->twitter_url ?? "",
                'pinterest' => $setting->pinterest_url ?? ""
            ],
            'documents' => [
                'privacy_policy' => $setting->privacy_pdf ? asset($setting->privacy_pdf) : "",
                'terms_of_service' => $setting->terms_pdf ? asset($setting->terms_pdf) : "",
                'cookie_policy' => $setting->cookie_pdf ? asset($setting->cookie_pdf) : ""
            ]
        ];
        
        // Remove the unused individual keys to match the requested JSON format exactly
        unset($data['home_example'], $data['home_examples'], $data['home_about']);

        return Helper::jsonResponse(true, 'Home Page', 200, $data);

    }
}
