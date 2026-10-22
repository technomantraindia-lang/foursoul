<?php

namespace App\Services\ImportExport;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use League\Csv\Writer;

/**
 * Phase 12 — Part 7: Import / Export framework.
 *
 * CSV-first bulk processing with chunked reads, per-row validation,
 * transactional rollback, and queued execution for large datasets.
 */
class ImportExportService
{
    public const CHUNK_SIZE = 500;

    /**
     * Bulk import rows from a CSV string. Each chunk runs in its own
     * transaction so a bad chunk rolls back without losing prior chunks
     * (configurable to all-or-nothing via $atomic).
     *
     * @param  callable(array<string, mixed>): void  $rowHandler  Receives validated row array.
     * @return array{imported: int, failed: int, errors: array<int, string>}
     */
    public function importCsv(string $csvContent, string $rulesClass, bool $atomic = false, ?callable $rowHandler = null): array
    {
        $result = ['imported' => 0, 'failed' => 0, 'errors' => []];

        try {
            $reader = Reader::createFromString($csvContent);
            $reader->setHeaderOffset(0);
            $records = $reader->getRecords();
        } catch (\Throwable $e) {
            $result['errors'][] = "Invalid CSV: {$e->getMessage()}";

            return $result;
        }

        $rulesInstance = app($rulesClass);
        $chunk = [];

        if ($atomic) {
            DB::beginTransaction();
        }

        foreach ($records as $offset => $record) {
            $validator = Validator::make($record, method_exists($rulesInstance, 'rules') ? $rulesInstance->rules() : []);

            if ($validator->fails()) {
                $result['failed']++;
                $result['errors'][] = "Row {$offset}: ".$validator->errors()->first();

                continue;
            }

            $chunk[] = $validator->validated();

            if (count($chunk) >= self::CHUNK_SIZE) {
                $this->flushChunk($chunk, $rowHandler, $atomic, $result);
                $chunk = [];
            }
        }

        if ($chunk !== []) {
            $this->flushChunk($chunk, $rowHandler, $atomic, $result);
        }

        if ($atomic) {
            $result['failed'] > 0 ? DB::rollBack() : DB::commit();
        }

        Log::channel('production')->info('import.completed', [
            'rules' => $rulesClass,
            'imported' => $result['imported'],
            'failed' => $result['failed'],
        ]);

        return $result;
    }

    /**
     * Stream an export to a callback in chunks — never materializes the full
     * dataset in memory (Part 16 performance guardrail).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  callable(object): array<string, mixed>  $rowMapper
     */
    public function exportCsv($query, callable $rowMapper, callable $write): int
    {
        $count = 0;
        $headerWritten = false;

        $query->chunkById(self::CHUNK_SIZE, function ($rows) use (&$count, &$headerWritten, $rowMapper, $write): void {
            $csv = Writer::createFromString();

            foreach ($rows as $row) {
                $data = $rowMapper($row);

                if (! $headerWritten) {
                    $csv->insertOne(array_keys($data));
                    $headerWritten = true;
                }

                $csv->insertOne(array_map(fn ($v) => is_scalar($v) || $v === null ? (string) $v : json_encode($v), $data));
                $count++;
            }

            $write((string) $csv);
        });

        return $count;
    }

    /** Queue a large export so it never blocks a web request. */
    public function queueExport(string $label, $query, callable $rowMapper): void
    {
        dispatch(function () use ($label, $query, $rowMapper): void {
            $chunks = [];

            $this->exportCsv($query, $rowMapper, function (string $csvPart) use (&$chunks): void {
                $chunks[] = $csvPart;
            });

            Log::channel('production')->info('export.queued_completed', [
                'label' => $label,
                'bytes' => strlen(implode('', $chunks)),
            ]);
        })->onQueue(\App\Support\QueueDomains::EXPORTS);
    }

    protected function flushChunk(array $chunk, ?callable $rowHandler, bool $atomic, array &$result): void
    {
        try {
            if (! $atomic) {
                DB::beginTransaction();
            }

            foreach ($chunk as $row) {
                $rowHandler?($row);
                $result['imported']++;
            }

            if (! $atomic) {
                DB::commit();
            }
        } catch (\Throwable $e) {
            if (! $atomic && DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            $result['failed'] += count($chunk);
            $result['errors'][] = "Chunk failed: {$e->getMessage()}";
        }
    }
}