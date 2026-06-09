<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchProject extends Model
{
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $fillable = [
        'title', 'description', 'start_date', 'end_date', 'status',
        'category', 'principal_investigator_id', 'created_by_user_id',
        'budget', 'synced_at', 'trl_level'
    ];

    public function principalInvestigator()
    {
        return $this->belongsTo(User::class, 'principal_investigator_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function collaborators()
    {
        return $this->hasMany(ResearchCollaborator::class);
    }

    public function outputs()
    {
        return $this->hasMany(ResearchOutput::class);
    }
}