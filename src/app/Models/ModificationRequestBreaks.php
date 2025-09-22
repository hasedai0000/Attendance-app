<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModificationRequestBreaks extends Model
{
    use HasFactory;

    protected $table = 'modification_request_breaks';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'modification_request_id',
        'requested_start_time',
        'requested_end_time',
    ];

    protected $casts = [
        'requested_start_time' => 'datetime:H:i',
        'requested_end_time' => 'datetime:H:i',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }

    public function modificationRequest(): BelongsTo
    {
        return $this->belongsTo(ModificationRequest::class);
    }
}
