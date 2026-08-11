<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\TopCreator;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class TopCreatorController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'top_creator');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = TopCreator::with('creator')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($data) {
                    return $data->creator->name ?? 'N/A';
                })
                ->addColumn('email', function ($data) {
                    return $data->creator->email ?? 'N/A';
                })
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->is_top ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->is_top ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make();
        }
        return view('backend.layouts.top_creator.index');
    }

    public function create()
    {
        // Get all users who have the role 'creator' and are not already in the top_creators table
        $existingTopCreatorIds = TopCreator::pluck('creator_id')->toArray();
        $creators = User::role('creator')->whereNotNull('stripe_account_id')->where('is_bank_added', 1)->whereNotIn('id', $existingTopCreatorIds)->get();
        return view('backend.layouts.top_creator.create', compact('creators'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'creator_id' => 'required|exists:users,id|unique:top_creators,creator_id'
        ]);

        try {
            TopCreator::create([
                'creator_id' => $request->creator_id,
                'is_top' => true
            ]);
            session()->put('t-success', 'Top Creator added successfully');
        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.top-creator.index');
    }

    public function destroy($id)
    {
        try {
            $topCreator = TopCreator::findOrFail($id);
            $topCreator->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Top Creator removed successfully!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting.'
            ]);
        }
    }

    public function status($id)
    {
        $data = TopCreator::findOrFail($id);
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found.',
            ]);
        }
        $data->is_top = !$data->is_top;
        $data->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Status updated successfully!',
        ]);
    }
}
