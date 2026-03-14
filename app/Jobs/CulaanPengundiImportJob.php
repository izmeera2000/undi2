<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CulaanPengundiImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $culaanId;
    public string $path;
    protected int $batchSize = 300;

    public function __construct(int $culaanId, string $path)
    {
        $this->culaanId = $culaanId;
        $this->path = $path;
    }

    public function handle()
    {
        $fullPath = Storage::path($this->path);

        $headerMap = [
            'KOD LOKALITI' => 'kod_lokaliti',
            'LOKALITI' => 'lokaliti',
            'PM' => 'pm',
            'NO SIRI' => 'no_siri',
            'SALURAN' => 'saluran',
            'NAMA' => 'nama',
            'NO KP' => 'no_kp',
            'JANTINA' => 'jantina',
            'UMUR' => 'umur',
            'BANGSA' => 'bangsa',
            'KATEGORI PENGUNDI' => 'kategori_pengundi',
            'STATUS PENGUNDI' => 'status_pengundi',
            'STATUS CULAAN' => 'status_culaan',
            'CAWANGAN' => 'cawangan',
            'NO AHLI' => 'no_ahli',
            'ALAMAT' => 'alamat',
            'STATUS' => 'status_ahli',
            'KATEGORI' => 'kategori_ahli'
        ];

        // Count total rows
        $total = 0;
        if (($h = fopen($fullPath, 'r')) !== false) {
            fgetcsv($h);
            while (fgetcsv($h)) {
                $total++;
            }
            fclose($h);
        }

        $count = 0;

        $cacheKey = "culaan_import_progress_{$this->culaanId}";
        Cache::put($cacheKey, [
            'count' => 0,
            'total' => $total
        ], 3600);

        $handle = fopen($fullPath, 'r');
        $header = array_map('strtoupper', fgetcsv($handle));

        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {

            if (!array_filter($data)) {
                continue;
            }

            $row = ['culaan_id' => $this->culaanId];

            foreach ($headerMap as $csv => $db) {
                $idx = array_search($csv, $header);
                $row[$db] = $idx !== false ? trim($data[$idx]) : null;
            }

            // ✅ Ensure kod_lokaliti starts with '022'
            if (!str_starts_with($row['kod_lokaliti'], '0')) {
                $row['kod_lokaliti'] = '0' . ltrim($row['kod_lokaliti'], '0');
                // optional: ltrim leading zeros to avoid double zeros
            }

            $rows[] = $row;
            $count++;

            if (count($rows) >= $this->batchSize) {
                $this->insertWithoutDuplicates($rows);
                $rows = [];

                Cache::put($cacheKey, [
                    'count' => $count,
                    'total' => $total
                ], 3600);
            }
        }
        if (!empty($rows)) {
            $this->insertWithoutDuplicates($rows);
        }

        Cache::put($cacheKey, [
            'count' => $count,
            'total' => $total
        ], 3600);

        fclose($handle);
        Cache::forget($cacheKey);
        Storage::delete($this->path);
    }


    protected function insertWithoutDuplicates(array $rows)
    {
        if (empty($rows)) {
            return;
        }

        // Remove duplicates inside CSV batch
        $uniqueRows = [];
        $seen = [];

        foreach ($rows as $r) {

            $key = $r['no_kp'] . '_' . $r['kod_lokaliti'];

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $uniqueRows[] = $r;
        }

        // Check existing in DB
        $existing = DB::table('culaan_pengundis')
            ->where('culaan_id', $this->culaanId)
            ->whereIn('no_kp', array_column($uniqueRows, 'no_kp'))
            ->get(['no_kp', 'kod_lokaliti']);

        $existingMap = [];

        foreach ($existing as $e) {
            $existingMap[$e->no_kp . '_' . $e->kod_lokaliti] = true;
        }

        $insert = [];

        foreach ($uniqueRows as $r) {

            $key = $r['no_kp'] . '_' . $r['kod_lokaliti'];

            if (isset($existingMap[$key])) {
                continue;
            }

            $insert[] = $r;
        }

        if (!empty($insert)) {
            DB::table('culaan_pengundis')->insert($insert);
        }
    }
}