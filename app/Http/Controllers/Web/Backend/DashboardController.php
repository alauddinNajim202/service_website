<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SessionPackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'dashboard');
    }

    public function index()
    {
        // Months array for ordering
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        // Helper to query and format counts by month
        $getMonthlyData = function ($query) use ($months) {
            $data = $query->select(
                DB::raw('MONTHNAME(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
                ->whereNotNull('created_at')
                ->groupBy('month')
                ->get()
                ->pluck('total', 'month')
                ->toArray();

            return collect($months)->map(function ($month) use ($data) {
                return $data[$month] ?? 0;
            })->toArray();
        };

        // Counts
        $packagesCount = SessionPackage::count();
        $categoriesCount = Category::count();
        $usersCount = User::role('user', 'api')->count();
        $creatorsCount = User::role('creator', 'api')->count();

        // Monthly trends
        $userTrends = $getMonthlyData(User::role('user', 'api'));
        $packageTrends = $getMonthlyData(SessionPackage::query());
        $creatorTrends = $getMonthlyData(User::role('creator', 'api'));

        $categoryDistribution = Category::get()->map(function ($category) {
            return [
                'name' => $category->name,
                'count' => User::role('creator', 'api')->where('category_id', $category->id)->count(),
            ];
        })->toArray();

        return view('backend.layouts.dashboard', compact(
            'packagesCount',
            'categoriesCount',
            'usersCount',
            'creatorsCount',
            'months',
            'userTrends',
            'packageTrends',
            'creatorTrends',
            'categoryDistribution'
        ));
    }
}
