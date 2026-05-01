<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionData extends Model
{
    protected $casts = [
        'date' => 'date',
    ];

    protected $fillable = [
        'date', 'visitor_count', 'visitor_category', 'time_slot',
        'notes', 'status', 'created_by_user_id', 'approved_by_user_id',
        'rejection_reason', 'synced_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function validation()
    {
        return $this->morphOne(DataValidation::class, 'validatable');
    }
}