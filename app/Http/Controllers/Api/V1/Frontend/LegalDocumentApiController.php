<?php

namespace App\Http\Controllers\Api\V1\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class LegalDocumentApiController extends Controller
{
    /**
     * Get the Legal Document PDFs
     */
    public function index(): JsonResponse
    {
        $setting = Setting::latest('id')->first();

        if (!$setting) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Legal documents not found.',
                'data'    => null,
            ], 404);
        }

        $data = [
            'privacy_pdf' => $setting->privacy_pdf ? asset($setting->privacy_pdf) : null,
            'terms_pdf'   => $setting->terms_pdf ? asset($setting->terms_pdf) : null,
            'cookie_pdf'  => $setting->cookie_pdf ? asset($setting->cookie_pdf) : null,
        ];

        return response()->json([
            'status'  => 'success',
            'message' => 'Legal documents retrieved successfully.',
            'data'    => $data,
        ], 200);
    }
}
