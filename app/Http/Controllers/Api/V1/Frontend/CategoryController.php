<?php

namespace App\Http\Controllers\Api\V1\Frontend;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $lang = $request->query('lang');
            $category = Category::where('status', 'active')->get();

            if ($lang && in_array($lang, ['en', 'fr', 'sp'])) {
                $category->each(function ($item) use ($lang) {
                    $langField = 'name_' . $lang;
                    if (!empty($item->$langField)) {
                        $item->name = $item->$langField;
                    }
                });
            }

            $data = [
                'categories' => $category
            ];
          
            return Helper::jsonResponse(true, 'Category', 200, $data);
        } catch (\Throwable $e) {
            return Helper::jsonErrorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }
}
