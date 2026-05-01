<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

/**
 * NotificationService - Centralized notification handler
 * 
 * ALUR KERJA:
 * 1. Create in-app notification records
 * 2. Queue email/SMS notifications untuk async processing
 * 3. Template management
 * 4. Track notification delivery status
 * 
 * KEUNTUNGAN:
 * - Separation of concerns: notification logic terpisah
 * - Async: email tidak block API response (via queue)
 * - Reusable: bisa call dari multiple services
 * - Trackable: bisa audit trail notification delivery
 * 
 * NOTIFICATION CHANNELS:
 * - In-app: Stored di notifications table, fetch via /api/notifications
 * - Email: Queued, send via mail job
 * - SMS: Optional, via third-party SMS gateway (implement later)
 */
class NotificationService
{
    /**
     * METHOD: notify()
     * 
     * ALUR KERJA:
     * 1. Create in-app notification record
     * 2. Queue email notification (if $sendEmail = true)
     * 3. Return created notification
     * 
     * PARAMETER:
     * - $userId: Target user ID
     * - $type: Notification type (data_approved, data_rejected, etc)
     * - $title: Short title untuk list view
     * - $message: Detailed message
     * - $dataReferenceId: Optional, link ke related data
     * - $sendEmail: Whether to queue email notification (default: true)
     * 
     * RETURN:
     * - Notification model instance
     */
    public function notify(
        $userId,
        $type,
        $title,
        $message,
        $dataReferenceId = null,
        $sendEmail = true
    ) {
        // Create in-app notification
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data_reference_id' => $dataReferenceId,
            'is_read' => false,
        ]);

        // Queue email notification jika requested
        if ($sendEmail) {
            $this->queueEmailNotification($userId, $type, $title, $message);
        }

        // Return created notification
        return $notification;
    }

    /**
     * METHOD: notifyMultiple()
     * 
     * ALUR KERJA:
     * 1. Create notification untuk multiple users sekaligus
     * 2. Gunakan case: notify semua admins tentang urgent action
     * 3. Use batch processing untuk efficiency
     * 
     * PARAMETER:
     * - $userIds: Array of user IDs
     * - $type: Notification type
     * - $title: Title
     * - $message: Message
     * 
     * RETURN:
     * - Array of created notifications
     */
    public function notifyMultiple($userIds, $type, $title, $message)
    {
        // Prepare batch insert data
        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Batch insert untuk efficiency (1 query bukan N queries)
        Notification::insert($notifications);

        // Queue emails untuk semua users
        foreach ($userIds as $userId) {
            $this->queueEmailNotification($userId, $type, $title, $message);
        }

        // Return count
        return count($notifications);
    }

    /**
     * METHOD: queueEmailNotification()
     * 
     * ALUR KERJA:
     * 1. Get user data dari database
     * 2. Queue email job (tidak execute immediately)
     * 3. Email akan dikirim via queue worker (php artisan queue:work)
     * 4. Jika gagal, queue akan retry
     * 
     * CATATAN:
     * - Async processing: tidak block API response
     * - Retry: jika email service down, akan retry later
     * - Implement mail template di resources/views/emails/
     */
    private function queueEmailNotification($userId, $type, $title, $message)
    {
        // Get user untuk email address
        $user = User::find($userId);
        
        if (!$user || !$user->email) {
            // Skip jika user tidak ada atau no email
            return;
        }

        // Queue email job (async)
        // Implement mail template later
        // Queue::push(new SendNotificationEmail($user, $type, $title, $message));
        
        // For now, just log to demonstrate
        // TODO: Implement actual mail sending
    }

    /**
     * METHOD: getUnreadNotifications($userId)
     * 
     * ALUR KERJA:
     * 1. Query unread notifications untuk user
     * 2. Sort by created_at descending (newest first)
     * 3. Return collection
     * 
     * USE CASE:
     * - Display notification bell icon count
     * - Display notification list di sidebar
     */
    public function getUnreadNotifications($userId, $limit = 10)
    {
        // Query unread notifications
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * METHOD: markAsRead($notificationId)
     * 
     * ALUR KERJA:
     * 1. Find notification by ID
     * 2. Update read_at timestamp
     * 3. Return updated notification
     * 
     * USE CASE:
     * - Ketika user click notification (auto-mark as read)
     * - Atau manual mark as read action
     */
    public function markAsRead($notificationId)
    {
        // Find notification
        $notification = Notification::find($notificationId);

        if (!$notification) {
            throw new \Exception('Notification not found');
        }

        // Mark as read
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        // Return updated notification
        return $notification;
    }

    /**
     * METHOD: markAllAsRead($userId)
     * 
     * ALUR KERJA:
     * 1. Find semua unread notifications untuk user
     * 2. Batch update read_at untuk semua
     * 3. Return count dari updated records
     * 
     * USE CASE:
     * - "Mark all as read" button di notification center
     */
    public function markAllAsRead($userId)
    {
        // Batch update semua unread notifications
        $updated = Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // Return count of updated rows
        return $updated;
    }

    /**
     * METHOD: deleteOldNotifications($daysOld = 30)
     * 
     * ALUR KERJA:
     * 1. Delete notifications older than specified days
     * 2. Use case: cleanup old notifications, save storage
     * 3. Typically run via scheduled command (Kernel.php)
     * 
     * PARAMETER:
     * - $daysOld: Delete notifications older than this many days (default: 30)
     */
    public function deleteOldNotifications($daysOld = 30)
    {
        // Delete old notifications
        $deleted = Notification::where('created_at', '<', now()->subDays($daysOld))
            ->delete();

        // Return count of deleted
        return $deleted;
    }

    /**
     * METHOD: getNotificationStats($userId)
     * 
     * ALUR KERJA:
     * 1. Count unread notifications
     * 2. Count total notifications (all time)
     * 3. Return stats
     * 
     * USE CASE:
     * - Display in dashboard
     * - Display badge count di bell icon
     */
    public function getNotificationStats($userId)
    {
        // Count unread
        $unreadCount = Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        // Count total
        $totalCount = Notification::where('user_id', $userId)
            ->count();

        // Return stats
        return [
            'unread_count' => $unreadCount,
            'total_count' => $totalCount,
        ];
    }

    /**
     * METHOD: sendSystemAlert($title, $message, $recipientType = 'admin')
     * 
     * ALUR KERJA:
     * 1. Send system-wide alert notification
     * 2. Recipient types: all_users, admins_only, supervisors
     * 3. Use case: maintenance alert, system downtime, critical events
     * 
     * PARAMETER:
     * - $title: Alert title
     * - $message: Alert message
     * - $recipientType: Who receives (all_users, admin, supervisor)
     */
    public function sendSystemAlert($title, $message, $recipientType = 'admin')
    {
        // Query recipients based on type
        $query = User::query();
        
        // Filter by role
        switch ($recipientType) {
            case 'all_users':
                // Send to all active users
                $query->where('status', 'active');
                break;
            case 'admin':
                // Send to admins only
                $query->whereHas('role', function ($q) {
                    $q->where('name', 'admin');
                });
                break;
            case 'supervisor':
                // Send to supervisors and admins
                $query->whereHas('role', function ($q) {
                    $q->whereIn('name', ['admin', 'supervisor']);
                });
                break;
            default:
                throw new \Exception("Unknown recipient type: $recipientType");
        }

        // Get user IDs
        $userIds = $query->pluck('id')->toArray();

        // Send notifications
        $count = $this->notifyMultiple($userIds, 'system_alert', $title, $message);

        // Return count of notifications sent
        return $count;
    }
}