<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchOutput extends Model
{
    protected $fillable = [
        'research_project_id', 'output_type', 'title', 'description',
        'date_produced', 'link'
    ];

    protected $casts = [
        'date_produced' => 'date',
    ];

    public function researchProject()
    {
        return $this->belongsTo(ResearchProject::class);
    }
}
