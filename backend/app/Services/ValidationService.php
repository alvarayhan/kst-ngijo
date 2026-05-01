<?php

namespace App\Services;

use App\Events\DataApproved;
use App\Models\DataValidation;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

/**
 * ValidationService - Handles data validation dan approval workflow
 * 
 * ALUR KERJA:
 * 1. Validasi data business rules (besides form validation)
 * 2. Create/update DataValidation records
 * 3. Approve/reject dengan notification ke submitter
 * 4. Trigger DataApproved event untuk sync ke Kelompok 1
 * 5. Create audit trail
 * 
 * KEUNTUNGAN:
 * - Separation of concerns: validation logic terpisah dari controller
 * - Reusable: bisa pakai dari multiple endpoints (API, Console commands, Jobs)
 * - Transaction management: gunakan DB transaction untuk data consistency
 * - Testable: business logic bisa di-unit test
 */
class ValidationService
{
    /**
     * METHOD: createValidation($validatable)
     * 
     * ALUR KERJA:
     * 1. Create DataValidation record untuk data yang disubmit
     * 2. Set status = pending, waiting untuk admin approval
     * 3. Link ke validatable object via polymorphic relationship
     * 4. Return created validation record
     * 
     * PARAMETER:
     * - $validatable: Model instance (ProductionData, ResearchProject, SustainabilityData)
     * 
     * CATATAN:
     * - Dipanggil otomatis saat data di-submit via controller
     * - atau bisa di-call manual dari service/job
     */
    public function createValidation($validatable)
    {
        // Create validation record linked ke data yang disubmit
        $validation = DataValidation::create([
            'validatable_id' => $validatable->id,
            'validatable_type' => get_class($validatable),
            'submitted_by_user_id' => auth('api')->id(),
            'status' => 'pending',
        ]);

        // Return created validation
        return $validation;
    }

    /**
     * METHOD: approveValidation($validationId, $comments = null)
     * 
     * ALUR KERJA:
     * 1. Find validation record by ID
     * 2. Start database transaction untuk atomic operation
     * 3. Update validation status = approved dengan admin comment
     * 4. Update validatable data status = approved
     * 5. Create notification untuk submitter
     * 6. Dispatch DataApproved event (trigger async sync ke Kelompok 1)
     * 7. Commit transaction
     * 8. Return updated validation
     * 
     * CATATAN:
     * - Transaction memastikan jika ada error, semua change di-rollback
     * - Event trigger async, tidak blocking API response
     */
    public function approveValidation($validationId, $comments = null)
    {
        // Start transaction untuk atomic approve operation
        return DB::transaction(function () use ($validationId, $comments) {
            // Find validation record
            $validation = DataValidation::lockForUpdate()->find($validationId);

            if (!$validation) {
                throw new \Exception('Validation record not found');
            }

            // Update validation status dan approver info
            $validation->update([
                'status' => 'approved',
                'approved_by_user_id' => auth('api')->id(),
                'approved_at' => now(),
                'admin_comments' => $comments,
            ]);

            // Update validatable (actual data) status
            $validation->validatable->update(['status' => 'approved']);

            // Create notification untuk submitter
            $this->notifySubmitter(
                $validation->submitted_by_user_id,
                'data_approved',
                'Data Anda Telah Disetujui',
                "Submission #{$validation->validatable_id} telah diapprove oleh admin"
            );

            // Dispatch event untuk trigger sync ke Kelompok 1
            // Event listener akan handle async processing via queue
            DataApproved::dispatch($validation->validatable, $this->getDataType($validation->validatable_type));

            // Return updated validation
            return $validation;
        });
    }

    /**
     * METHOD: rejectValidation($validationId, $rejectionReason)
     * 
     * ALUR KERJA:
     * 1. Find validation record by ID
     * 2. Start transaction
     * 3. Update validation status = rejected dengan reason
     * 4. Update validatable data status = rejected dengan reason
     * 5. Create notification untuk submitter dengan rejection reason
     * 6. Commit transaction
     * 7. Return updated validation
     * 
     * CATATAN:
     * - Rejection reason penting untuk feedback ke submitter
     * - Data bisa di-resubmit setelah diperbaiki
     */
    public function rejectValidation($validationId, $rejectionReason)
    {
        // Validate rejection reason
        if (empty($rejectionReason)) {
            throw new \Exception('Rejection reason is required');
        }

        // Start transaction
        return DB::transaction(function () use ($validationId, $rejectionReason) {
            // Find validation record dengan lock
            $validation = DataValidation::lockForUpdate()->find($validationId);

            if (!$validation) {
                throw new \Exception('Validation record not found');
            }

            // Update validation status = rejected
            $validation->update([
                'status' => 'rejected',
                'approved_by_user_id' => auth('api')->id(),
                'admin_comments' => $rejectionReason,
            ]);

            // Update validatable status = rejected dengan reason
            $validation->validatable->update([
                'status' => 'rejected',
                'rejection_reason' => $rejectionReason,
            ]);

            // Create notification dengan rejection reason
            $this->notifySubmitter(
                $validation->submitted_by_user_id,
                'data_rejected',
                'Data Anda Ditolak',
                "Submission #{$validation->validatable_id} ditolak. Alasan: {$rejectionReason}"
            );

            // Return updated validation
            return $validation;
        });
    }

    /**
     * METHOD: notifySubmitter()
     * 
     * ALUR KERJA:
     * 1. Create notification record untuk user
     * 2. Notification ini akan di-fetch via /api/notifications endpoint
     * 3. User bisa mark as read ketika sudah lihat
     * 
     * PARAMETER:
     * - $userId: User ID yang di-notify
     * - $type: Notification type (data_approved, data_rejected, etc)
     * - $title: Notification title (untuk list view)
     * - $message: Detail message (untuk detail view)
     */
    private function notifySubmitter($userId, $type, $title, $message)
    {
        // Create notification record
        Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);

        // Note: Bisa tambah email/SMS notification di sini
        // Contoh: Mail::queue(new NotificationMail($userId, $message));
    }

    /**
     * METHOD: getDataType()
     * 
     * ALUR KERJA:
     * 1. Convert full class path ke data type string
     * 2. Digunakan untuk event dispatcher dan sync ke Kelompok 1
     * 
     * MAPPING:
     * - App\\Models\\ProductionData => 'production'
     * - App\\Models\\ResearchProject => 'research'
     * - App\\Models\\SustainabilityData => 'sustainability'
     */
    private function getDataType($validatableType)
    {
        // Extract class name dari full namespace
        $className = class_basename($validatableType);

        // Map ke data type string
        return match ($className) {
            'ProductionData' => 'production',
            'ResearchProject' => 'research',
            'SustainabilityData' => 'sustainability',
            default => strtolower($className),
        };
    }

    /**
     * METHOD: getPendingValidations($limit = 20)
     * 
     * ALUR KERJA:
     * 1. Query pending validations dari database
     * 2. Include relasi ke validatable, submitter, approver
     * 3. Sort by created_at descending (terbaru first)
     * 4. Paginate dengan limit
     * 5. Return collection
     * 
     * USE CASE:
     * - Display di admin panel "Pending Approvals" widget
     * - Show count di dashboard
     */
    public function getPendingValidations($limit = 20)
    {
        // Query pending validations dengan relasi
        return DataValidation::where('status', 'pending')
            ->with(['validatable', 'submitter' => function ($q) {
                $q->select('id', 'name', 'email');
            }])
            ->latest('created_at')
            ->paginate($limit);
    }

    /**
     * METHOD: getValidationStats()
     * 
     * ALUR KERJA:
     * 1. Count validations by status (pending, approved, rejected)
     * 2. Return summary stats
     * 
     * USE CASE:
     * - Display stats widget di dashboard
     * - KPI reporting
     */
    public function getValidationStats()
    {
        // Count by status
        $stats = DataValidation::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Return formatted stats
        return [
            'pending' => $stats['pending'] ?? 0,
            'approved' => $stats['approved'] ?? 0,
            'rejected' => $stats['rejected'] ?? 0,
            'total' => array_sum($stats),
        ];
    }
}