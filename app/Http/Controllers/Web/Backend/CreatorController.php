<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class CreatorController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'creators');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::whereHas('roles', function ($q) {
                $q->where('name', 'creator');
            })->orderBy('id', 'desc')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($data) {
                    if ($data->avatar) {
                        $url = filter_var($data->avatar, FILTER_VALIDATE_URL) ? $data->avatar : asset($data->avatar);
                        return '<img src="' . $url . '" alt="avatar" class="rounded-circle" width="50" height="50" style="object-fit: cover;">';
                    }
                    return '<img src="' . asset('default/profile.jpg') . '" alt="avatar" class="rounded-circle" width="50" height="50" style="object-fit: cover;">';
                })
                ->addColumn('name', function ($data) {
                    return $data->name ?? 'N/A';
                })
                ->addColumn('email', function ($data) {
                    return $data->email ?? 'N/A';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['image', 'action'])
                ->make();
        }

        return view('backend.layouts.creator.index');
    }

    public function destroy($id)
    {
        try {
            $creator = User::findOrFail($id);
            DB::table('model_has_roles')->where('model_id', $id)->delete();
            $creator->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Creator deleted successfully!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting creator.'
            ]);
        }
    }
}
