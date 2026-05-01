<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SustainabilityData extends Model
{
    protected $fillable = [
        'record_date', 'category', 'metric_name', 'value', 'unit',
        'target_value', 'notes', 'created_by_user_id', 'approved_by_user_id',
        'status', 'synced_at'
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by_user_id'); }
}