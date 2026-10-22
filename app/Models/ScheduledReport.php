<?php

namespace App\Models;

use App\Enums\ReportFormat;
use App\Enums\ReportFrequency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduledReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'admin_id',
        'name',
        'report_key',
        'format',
        'frequency',
        'filters',
        'recipients',
        'is_active',
        'last_generated_at',
        'next_run_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'recipients' => 'array',
        'is_active' => 'boolean',
        'last_generated_at' => 'datetime',
        'next_run_at' => 'datetime',
        'format' => ReportFormat::class,
        'frequency' => ReportFrequency::class,
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}