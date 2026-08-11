<?php
namespace App\Http\Controllers\Api\V1\Frontend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\SocialLink;

class SocialLinksController extends Controller{
    public function index(){
        $social_links = SocialLink::where('status', 'active')->get();
        $setting = \App\Models\Setting::first();
        $data = [
            'social_profiles' => [
                'facebook_url' => $setting->facebook_url ?? null,
                'instagram_url' => $setting->instagram_url ?? null,
                'twitter_url' => $setting->twitter_url ?? null,
                'pinterest_url' => $setting->pinterest_url ?? null,
            ]
        ];
        return Helper::jsonResponse(true, 'Social Links', 200, $data);
    }
}