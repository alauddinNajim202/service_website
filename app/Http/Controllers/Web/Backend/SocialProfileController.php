<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class SocialProfileController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'social_profile');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $setting = Setting::first();
        return view("backend.layouts.social_profile.index", compact('setting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'facebook_url'  => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url'   => 'nullable|url|max:255',
            'pinterest_url' => 'nullable|url|max:255',
        ]);

        try {
            $setting = Setting::first();
            if (!$setting) {
                $setting = new Setting();
            }

            $setting->facebook_url  = $request->facebook_url;
            $setting->instagram_url = $request->instagram_url;
            $setting->twitter_url   = $request->twitter_url;
            $setting->pinterest_url = $request->pinterest_url;

            $setting->save();

            return redirect()->back()->with('t-success', 'Social profiles updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }
}
