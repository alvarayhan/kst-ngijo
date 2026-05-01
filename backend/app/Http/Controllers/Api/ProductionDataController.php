<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionData;
use Illuminate\Http\Request;

class ProductionDataController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionData::with(['creator', 'approver']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest('date')->paginate(20)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'visitor_count' => 'required|integer|min:0',
            'visitor_category' => 'required|in:individuals,groups,researchers,students',
            'time_slot' => 'required|in:morning,afternoon,evening',
            'notes' => 'nullable|string'
        ]);

        $data = ProductionData::create(array_merge($validated, [
            'status' => 'pending',
            'created_by_user_id' => auth('api')->id()
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Data submitted and pending approval',
            'data' => $data
        ], 201);
    }

    public function show($id)
    {
        $data = ProductionData::with(['creator', 'approver'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $data = ProductionData::findOrFail($id);
        
        if ($data->status === 'approved' && auth('api')->user()->role->name !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Cannot edit approved data'], 403);
        }

        $validated = $request->validate([
            'visitor_count' => 'integer|min:0',
            'notes' => 'nullable|string'
        ]);

        $data->update($validated);
        return response()->json(['success' => true, 'data' => $data]);
    }
    
    public function destroy($id)
    {
        $data = ProductionData::findOrFail($id);
        
        if ($data->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete approved/rejected data'
            ], 403);
        }
        
        $data->delete();
        return response()->json(['success' => true, 'message' => 'Data deleted successfully']);
    }

    public function mySubmissions(Request $request)
    {
        $query = ProductionData::where('created_by_user_id', auth('api')->id())
            ->with(['creator', 'approver']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest('date')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ]
        ]);
    }
}