<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModificationRequest extends Model
{
    use HasFactory;

    protected $table = 'modification_requests';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_start_time',
        'requested_end_time',
        'requested_remarks',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'requested_start_time' => 'datetime:H:i',
        'requested_end_time' => 'datetime:H:i',
        'approved_at' => 'datetime',
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

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function modificationRequestBreaks(): HasMany
    {
        return $this->hasMany(ModificationRequestBreaks::class);
    }
}
