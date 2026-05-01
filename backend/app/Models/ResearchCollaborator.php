<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchCollaborator extends Model
{
    protected $fillable = [
        'research_project_id', 'collaborator_name', 'institution', 'role'
    ];

    public function researchProject()
    {
        return $this->belongsTo(ResearchProject::class);
    }
}
