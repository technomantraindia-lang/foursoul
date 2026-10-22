<?php

namespace App\Models;

use App\Enums\ReportFormat;
use Illuminate\Database\Eloquent\Model;

class ReportExport extends Model
{
    protected $fillable = [
        'uuid',
        'admin_id',
        'report_key',
        'format',
        'filters',
        'row_count',
    ];

    protected $casts = [
        'filters' => 'array',
        'row_count' => 'integer',
        'format' => ReportFormat::class,
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}