<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantMetric extends Model
{
    protected $fillable = [
        'tenant_id', 'metric_date', 'employees_count', 'revenue',
        'products_produced', 'market_reach', 'sustainability_score'
    ];

    protected $casts = [
        'metric_date' => 'date',
        'revenue' => 'integer',
        'employees_count' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
