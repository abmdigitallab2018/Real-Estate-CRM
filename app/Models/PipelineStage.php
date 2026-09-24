<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipelineStage extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'pipeline_id',
        'name',
        'slug',
        'order',
        'probability',
        'color',
        'is_won',
        'is_lost',
    ];

    protected $casts = [
        'order' => 'integer',
        'probability' => 'integer',
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
    ];

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'stage_id');
    }
}
