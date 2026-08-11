<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SessionPackage;
use Exception;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class SessionPackageController extends Controller
{
    /**
     * Type labels for display
     */
    const TYPE_LABELS = [
        'vip_access' => 'VIP Access',
        'quick_advice' => 'Quick Advice',
        'personal_advice' => 'Personal Advice',
    ];

    public function __construct()
    {
        View::share('crud', 'session_package');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SessionPackage::orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('type_label', function ($data) {
                    $label = self::TYPE_LABELS[$data->type] ?? ucwords(str_replace('_', ' ', $data->type ?? 'N/A'));
                    $colors = [
                        'vip_access' => ['bg' => '#CFA267', 'text' => '#121212'],
                        'quick_advice' => ['bg' => '#6C5CE7', 'text' => '#FFFFFF'],
                        'personal_advice' => ['bg' => '#00B894', 'text' => '#FFFFFF'],
                    ];
                    $color = $colors[$data->type] ?? ['bg' => '#636e72', 'text' => '#FFFFFF'];
                    return '<span style="background-color: ' . $color['bg'] . '; color: ' . $color['text'] . '; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">' . $label . '</span>';
                })
                ->addColumn('price', function ($data) {
                    return '$' . number_format($data->price, 2);
                })
                ->addColumn('status', function ($data) {
                    $isActive = $data->status == 'active';
                    
                    $trackBg = $isActive ? '#10B981' : '#6B7280';
                    $trackBoxShadow = $isActive ? '0 0 8px rgba(16,185,129,0.5)' : 'none';
                    $toggleX = $isActive ? '24px' : '3px';
                    
                    $status = '<div style="display:flex;align-items:center;justify-content:center;gap:10px;">';
                    $status .= '<div onclick="showStatusChangeAlert(' . $data->id . ')" style="display:flex;align-items:center;cursor:pointer;position:relative;width:50px;height:26px;background:' . $trackBg . ';border-radius:13px;box-shadow:' . $trackBoxShadow . ';transition:all 0.3s cubic-bezier(0.4,0,0.2,1);">';
                    $status .= '<div style="position:absolute;top:3px;left:3px;width:20px;height:20px;background:white;border-radius:50%;box-shadow:0 2px 4px rgba(0,0,0,0.2);transform:translateX(' . $toggleX . ');transition:transform 0.3s cubic-bezier(0.4,0,0.2,1);">';
                    $status .= '<svg style="width:12px;height:12px;position:absolute;top:4px;left:4px;transition:all 0.3s;" viewBox="0 0 24 24" fill="none" stroke="' . ($isActive ? '#10B981' : '#6B7280') . '" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">';
                    if ($isActive) {
                        $status .= '<polyline points="20 6 9 17 4 12"></polyline>';
                    } else {
                        $status .= '<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>';
                    }
                    $status .= '</svg>';
                    $status .= '</div>';
                    $status .= '</div>';
                    $status .= '<span style="font-size:12px;font-weight:600;color:' . ($isActive ? '#10B981' : '#9CA3AF') . ';letter-spacing:0.3px;text-transform:uppercase;transition:color 0.3s;">' . ($isActive ? 'Active' : 'Inactive') . '</span>';
                    $status .= '</div>';
                    
                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="d-flex align-items-center justify-content-center" style="gap: 10px;">
                                <a href="#" onclick="goToShow(' . $data->id . ')" class="btn btn-sm btn-info rounded-pill px-3 shadow-sm" style="background: linear-gradient(135deg, #17a2b8, #138496); border: none; color: #fff; font-size: 12px; font-weight: 600; transition: all 0.3s ease;" title="View Details" onmouseover="this.style.transform=\'scale(1.05)\';this.style.boxShadow=\'0 4px 12px rgba(23,162,184,0.4)\'" onmouseout="this.style.transform=\'scale(1)\';this.style.boxShadow=\'none\'">
                                    <i class="fa-solid fa-eye me-1"></i> View
                                </a>
                                <a href="#" onclick="goToEdit(' . $data->id . ')" class="btn btn-sm btn-warning rounded-pill px-3 shadow-sm" style="background: linear-gradient(135deg, #ffc107, #e0a800); border: none; color: #212529; font-size: 12px; font-weight: 600; transition: all 0.3s ease;" title="Edit Package" onmouseover="this.style.transform=\'scale(1.05)\';this.style.boxShadow=\'0 4px 12px rgba(255,193,7,0.4)\'" onmouseout="this.style.transform=\'scale(1)\';this.style.boxShadow=\'none\'">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </a>
                            </div>';
                })
                ->rawColumns(['type_label', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.session_packages.index");
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $crud = 'session-packages';
        $package = SessionPackage::findOrFail($id);
        $typeLabels = self::TYPE_LABELS;
        return view('backend.layouts.session_packages.show', compact('package', 'crud', 'typeLabels'));
    }

    public function edit($id)
    {
        $crud = 'session-packages';
        $package = SessionPackage::findOrFail($id);
        return view('backend.layouts.session_packages.edit', compact('package', 'crud'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:vip_access,quick_advice,personal_advice',
            'name_en' => 'required|string|max:255',
            'name_fr' => 'required|string|max:255',
            'name_es' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_en' => 'required|string|max:255',
            'duration_fr' => 'required|string|max:255',
            'duration_es' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_fr' => 'nullable|string',
            'description_es' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'is_feature' => 'nullable|boolean',
            'feature_text' => 'required_with:is_feature|nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();
            $data['is_feature'] = $request->has('is_feature') ? 1 : 0;
            if (!$data['is_feature']) {
                $data['feature_text'] = null;
            }

            // Clean HTML tags from descriptions
            $data['description_en'] = isset($data['description_en']) ? strip_tags($data['description_en']) : '';
            $data['description_fr'] = isset($data['description_fr']) ? strip_tags($data['description_fr']) : '';
            $data['description_es'] = isset($data['description_es']) ? strip_tags($data['description_es']) : '';

            $data['name'] = $data['name_en'];
            $data['description'] = $data['description_en'];
            $data['duration'] = $data['duration_en'];

            $package = SessionPackage::findOrFail($id);
            $package->update($data);

            session()->put('t-success', 'Session Package updated successfully');
        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.session-packages.index');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function status(int $id): JsonResponse
    {
        try {
            $package = SessionPackage::findOrFail($id);
            $package->status = $package->status === 'active' ? 'inactive' : 'active';
            $package->save();

            return response()->json([
                'status' => 't-success',
                'message' => 'Status updated successfully!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 't-error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
