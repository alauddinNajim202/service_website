<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class LegalDocumentController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'legal_documents');
    }

    public function index()
    {
        $setting = Setting::latest('id')->first();
        return view('backend.layouts.legal.index', compact('setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'privacy_pdf' => 'nullable|mimes:pdf|max:10240',
            'terms_pdf'   => 'nullable|mimes:pdf|max:10240',
            'cookie_pdf'  => 'nullable|mimes:pdf|max:10240',
        ]);

        try {
            $setting = Setting::first();
            
            if ($request->hasFile('privacy_pdf')) {
                if ($setting && $setting->privacy_pdf && file_exists(public_path($setting->privacy_pdf))) {
                    Helper::fileDelete(public_path($setting->privacy_pdf));
                }
                $validatedData['privacy_pdf'] = Helper::fileUpload($request->file('privacy_pdf'), 'legal', time() . '_' . getFileName($request->file('privacy_pdf')));
            }

            if ($request->hasFile('terms_pdf')) {
                if ($setting && $setting->terms_pdf && file_exists(public_path($setting->terms_pdf))) {
                    Helper::fileDelete(public_path($setting->terms_pdf));
                }
                $validatedData['terms_pdf'] = Helper::fileUpload($request->file('terms_pdf'), 'legal', time() . '_' . getFileName($request->file('terms_pdf')));
            }

            if ($request->hasFile('cookie_pdf')) {
                if ($setting && $setting->cookie_pdf && file_exists(public_path($setting->cookie_pdf))) {
                    Helper::fileDelete(public_path($setting->cookie_pdf));
                }
                $validatedData['cookie_pdf'] = Helper::fileUpload($request->file('cookie_pdf'), 'legal', time() . '_' . getFileName($request->file('cookie_pdf')));
            }

            Setting::updateOrCreate(['id' => 1], $validatedData);
            return back()->with('t-success', 'Legal Documents updated successfully');
            
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }
}
