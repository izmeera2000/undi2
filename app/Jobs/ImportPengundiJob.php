<?php

namespace App\Jobs;

use App\Models\Pengundi; // Or Member if you have that model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportPengundiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected int $tarikhUndian;
    protected string $pilihanRayaType;
    protected int $pilihanRayaSeries;
    protected string $cacheKey;
    protected int $batchSize;

    protected array $headerMap = [

        'KOD LOKALITI' => 'kodlokaliti',
        'LOKALITI' => 'namalokaliti',
        'PM' => 'alamat_spr',
        'NO SIRI' => 'no_siri',
        'SALURAN' => 'saluran',
        'NAMA' => 'nama',
        'NO KP' => 'nokp_baru',
        'JANTINA' => 'jantina',
        'UMUR' => 'umur',
        'BANGSA' => 'bangsa_spr',

    ];

    public function __construct(
        string $filePath,
        int $tarikhUndian,
        string $pilihanRayaType,
        int $pilihanRayaSeries,
        string $cacheKey = 'pengundi_import_progress',
        int $batchSize = 300
    ) {
        $this->filePath = $filePath;
        $this->tarikhUndian = $tarikhUndian;
        $this->pilihanRayaType = $pilihanRayaType;
        $this->pilihanRayaSeries = $pilihanRayaSeries;
        $this->cacheKey = $cacheKey;
        $this->batchSize = $batchSize;
    }

    public function handle()
    {
        try {
            if (!file_exists($this->filePath)) {
                throw new \Exception("CSV file not found: {$this->filePath}");
            }

            $handle = fopen($this->filePath, 'r');
            if (!$handle) {
                throw new \Exception("Cannot open CSV file: {$this->filePath}");
            }

            $header = fgetcsv($handle);
            $header = array_map('strtoupper', $header);

            // Count total rows
            $total = 0;
            while (($data = fgetcsv($handle)) !== false) {
                if (array_filter($data)) $total++;
            }

            rewind($handle);
            fgetcsv($handle); // skip header

            Cache::put($this->cacheKey, ['count' => 0, 'total' => $total]);

            $rows = [];
            $count = 0;

            while (($data = fgetcsv($handle)) !== false) {
                if (!array_filter($data)) continue;

                $row = [];
                foreach ($this->headerMap as $csv => $db) {
                    $idx = array_search(strtoupper($csv), $header);
                    $row[$db] = $idx !== false ? trim($data[$idx]) : null;
                }

                // Additional fields
                $row['tarikh_undian'] = $this->tarikhUndian;
                $row['pilihan_raya_type'] = $this->pilihanRayaType;
                $row['pilihan_raya_series'] = $this->pilihanRayaSeries;
                $row['type_data_id'] = 2;

                $rows[] = $row;
                $count++;

                if ($count % $this->batchSize === 0) {
                    Pengundi::upsert(
                        $rows,
                        ['nokp_baru', 'tarikh_undian'],
                        ['nama', 'kodlokaliti', 'bangsa', 'umur', 'alamat_spr', 'saluran', 'no_siri']
                    );
                    $rows = [];
                    Cache::put($this->cacheKey, ['count' => $count, 'total' => $total]);
                }
            }

            if ($rows) {
                Pengundi::upsert(
                    $rows,
                    ['nokp_baru', 'tarikh_undian'],
                    ['nama', 'kodlokaliti', 'bangsa', 'umur', 'alamat_spr', 'saluran', 'no_siri']
                );
            }

            fclose($handle);

            Cache::forget($this->cacheKey);

        } catch (\Exception $e) {
            Log::error("ImportMembersJob failed: " . $e->getMessage());
        }
    }
}