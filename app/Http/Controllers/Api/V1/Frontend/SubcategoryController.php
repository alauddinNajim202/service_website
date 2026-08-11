<?php

namespace App\Http\Controllers\Api\V1\Frontend;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Subcategory;

class SubcategoryController extends Controller
{
    public function index()
    {
        $subcategories = Subcategory::where('status', 'active')->get();
        $data = [
            'subcategories' => $subcategories
        ];
        return Helper::jsonResponse(true, 'Category', 200, $data);

    }
}