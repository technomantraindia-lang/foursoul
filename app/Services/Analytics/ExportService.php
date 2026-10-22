<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Enums\ReportFormat;
use App\Models\Admin;
use App\Models\ReportExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service-based report exports (CSV / Excel / PDF).
 * Excel is emitted as CSV-compatible XLSX-ready data; PDF uses a minimal
 * HTML-to-print document so no external binary dependency is required.
 */
class ExportService
{
    public function __construct(
        protected ReportService $reports,
    ) {}

    /**
     * Export a report and persist a ReportExport record.
     */
    public function export(string $report, ReportFormat $format, array $filters, Admin $admin): ReportExport
    {
        $data = $this->reports->run($report, $filters);
        [$headers, $rows] = $this->normalizeRows($report, $data);

        $filename = sprintf(
            'exports/%s_%s_%s.%s',
            $report,
            now()->format('Ymd_His'),
            substr(md5(uniqid('', true)), 0, 8),
            $format === ReportFormat::Excel ? 'csv' : $format->value,
        );

        Storage::disk('local')->put($filename, $this->render($headers, $rows, $format));

        $export = ReportExport::create([
            'uuid' => (string) Str::uuid(),
            'admin_id' => $admin->id,
            'report_key' => $report,
            'format' => $format,
            'filters' => $filters,
            'row_count' => count($rows),
        ]);

        $export->setAttribute('file_path', $filename);

        return $export;
    }

    /**
     * Flatten report payload into header + row arrays.
     *
     * @return array{0: array<int, string>, 1: array<int, array<int, mixed>>}
     */
    protected function normalizeRows(string $report, array $data): array
    {
        if (isset($data['by_period'])) {
            $headers = ['period', 'total', 'transactions'];
            $rows = collect($data['by_period'])->map(fn ($r) => [(string) $r->period, (string) $r->total, (string) $r->transactions])->all();

            return [$headers, $rows];
        }

        if (isset($data['rows'])) {
            $first = collect($data['rows'])->first();
            if (! $first) {
                return [['no_data'], []];
            }

            $attributes = method_exists($first, 'getAttributes') ? array_keys($first->getAttributes()) : array_keys((array) $first);
            $rows = collect($data['rows'])->map(fn ($r) => array_values(method_exists($r, 'getAttributes') ? $r->getAttributes() : (array) $r))->all();

            return [$attributes, $rows];
        }

        // Scalar summary reports: key/value pairs.
        $headers = ['metric', 'value'];
        $rows = collect($data)->map(fn ($v, $k) => [$k, is_scalar($v) ? (string) $v : json_encode($v)])->values()->all();

        return [$headers, $rows];
    }

    protected function render(array $headers, array $rows, ReportFormat $format): string
    {
        return match ($format) {
            ReportFormat::Csv, ReportFormat::Excel => $this->toCsv($headers, $rows),
            ReportFormat::Pdf => $this->toPdf($headers, $rows),
        };
    }

    protected function toCsv(array $headers, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);

        return (string) stream_get_contents($handle);
    }

    protected function toPdf(array $headers, array $rows): string
    {
        $headCells = implode('', array_map(fn ($h) => '<th>'.e((string) $h).'</th>', $headers));
        $bodyRows = implode('', array_map(function ($row) {
            return '<tr>'.implode('', array_map(fn ($c) => '<td>'.e((string) $c).'</td>', $row)).'</tr>';
        }, $rows));

        return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Report</title>'
            .'<style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:6px;text-align:left}th{background:#f5f5f5}</style>'
            .'</head><body onload="window.print()"><table><thead><tr>'.$headCells.'</tr></thead><tbody>'.$bodyRows.'</tbody></table></body></html>';
    }
}