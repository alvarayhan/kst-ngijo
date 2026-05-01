<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataValidation;
use App\Models\ProductionData;
use App\Models\SustainabilityData;
use App\Models\Notification;
use Illuminate\Http\Request;

class ValidationController extends Controller
{
    public function pending(Request $request)
    {
        $validations = DataValidation::where('status', 'pending')
            ->with(['validatable', 'submitter', 'approver'])
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $validations->items(),
            'pagination' => [
                'total' => $validations->total(),
                'per_page' => $validations->perPage(),
                'current_page' => $validations->currentPage(),
                'last_page' => $validations->lastPage(),
            ]
        ]);
    }

    public function approve(Request $request, $id)
    {
        $validation = DataValidation::find($id);

        if (!$validation) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $validation->update([
            'status' => 'approved',
            'approved_by_user_id' => auth('api')->id(),
            'approved_at' => now(),
            'admin_comments' => $request->comments ?? null,
        ]);

        $validation->validatable->update(['status' => 'approved']);

        // Notify submitter
        Notification::create([
            'user_id' => $validation->submitted_by_user_id,
            'type' => 'data_approved',
            'title' => 'Data Approved',
            'message' => 'Your submitted data has been approved by admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data approved successfully',
            'data' => $validation
        ]);
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $validation = DataValidation::find($id);

        if (!$validation) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $validation->update([
            'status' => 'rejected',
            'approved_by_user_id' => auth('api')->id(),
            'admin_comments' => $validated['rejection_reason'],
        ]);

        $validation->validatable->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null
        ]);

        // Notify submitter
        Notification::create([
            'user_id' => $validation->submitted_by_user_id,
            'type' => 'data_rejected',
            'title' => 'Data Rejected',
            'message' => "Your data was rejected. Reason: {$validated['rejection_reason']}",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data rejected successfully',
            'data' => $validation
        ]);
    }
}
