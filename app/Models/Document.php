<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'documentable_type',
        'documentable_id',
        'title',
        'document_type',
        'file_path',
        'file_size',
        'file_extension',
        'is_private',
        'uploaded_by',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'file_size' => 'integer',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
