<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'company_name', 'industry_category', 'address', 'contact_person',
        'phone', 'email', 'website', 'registration_date', 'status', 'notes',
        'created_by_user_id'
    ];

    protected $casts = [
        'registration_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function metrics()
    {
        return $this->hasMany(TenantMetric::class);
    }
}
