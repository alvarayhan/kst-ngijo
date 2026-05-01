<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SustainabilityData;
use Illuminate\Http\Request;

class SustainabilityDataController extends Controller
{
    public function index(Request $request)
    {
        $query = SustainabilityData::with(['creator', 'approver']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest('record_date')->paginate(20)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'record_date' => 'required|date|before_or_equal:today',
            'category' => 'required|in:energy,water,waste,emissions,social',
            'metric_name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'target_value' => 'nullable|numeric',
            'notes' => 'nullable|string|max:500'
        ]);

        $data = SustainabilityData::create(array_merge($validated, [
            'status' => 'pending',
            'created_by_user_id' => auth('api')->id()
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Sustainability data submitted successfully',
            'data' => $data
        ], 201);
    }

    public function show($id)
    {
        $data = SustainabilityData::with(['creator', 'approver'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $data = SustainabilityData::findOrFail($id);

        if ($data->status !== 'pending' && auth('api')->user()->role->name !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Cannot edit approved data'], 403);
        }

        $validated = $request->validate([
            'value' => 'numeric|min:0',
            'target_value' => 'nullable|numeric',
            'notes' => 'nullable|string|max:500'
        ]);

        $data->update($validated);
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function destroy($id)
    {
        $data = SustainabilityData::findOrFail($id);

        if ($data->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Cannot delete approved data'], 403);
        }

        $data->delete();
        return response()->json(['success' => true, 'message' => 'Data deleted successfully']);
    }
}