<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataValidation extends Model
{
    protected $fillable = [
        'validatable_id', 'validatable_type', 'submitted_by_user_id',
        'status', 'admin_comments', 'approved_by_user_id', 'approved_at'
    ];

    public function validatable()
    {
        return $this->morphTo();
    }
}