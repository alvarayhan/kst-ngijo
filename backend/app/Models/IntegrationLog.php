<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    protected $fillable = [
        'endpoint', 'method', 'payload', 'response_status', 'response_body',
        'external_system', 'success', 'error_message'
    ];

    protected $casts = [
        'payload' => 'array',
        'response_body' => 'array',
        'success' => 'boolean',
    ];

    public $timestamps = false;

    protected $dates = ['created_at'];
}
