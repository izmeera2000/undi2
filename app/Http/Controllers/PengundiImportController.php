<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PengundiImportController extends Controller
{
    protected $cacheKey = 'pengundi_import_progress';
    protected $batchSize = 300;

    protected $headerMap = [
        'KOD PAR' => 'kod_par',
        'NAMAPAR' => 'namapar',
        'KOD DUN' => 'kod_dun',
        'NAMADUN' => 'namadun',
        'KODDM' => 'koddm',
        'NAMADM' => 'namadm',
        'KODLOKALITI' => 'kodlokaliti',
        'NAMALOKALITI' => 'namalokaliti',
        'NOKP BARU' => 'nokp_baru',
        'NOKP LAMA' => 'nokp_lama',
        'NAMA' => 'nama',
        'ALAMAT SPR' => 'alamat_spr',
        'ALAMAT JPN 1' => 'alamat_jpn_1',
        'ALAMAT JPN 2' => 'alamat_jpn_2',
        'ALAMAT JPN 3' => 'alamat_jpn_3',
        'POSKOD' => 'poskod',
        'BANDAR' => 'bandar',
        'BANGSA' => 'bangsa',
        'BANGSA SPR' => 'bangsa_spr',
        'JANTINA' => 'jantina',
        'STATUS BARU' => 'status_baru',
        'KODPAR PRU12' => 'kodpar_pru12',
        'TAHUN LAHIR' => 'tahun_lahir',
        'UMUR' => 'umur',
        'STATUS UMNO' => 'status_umno',
        'NEGERI' => 'negeri',
    ];

    protected $integerColumns = [
        'tahun_lahir',
        'umur',
        'kod_par',
        'kod_dun',
        'koddm',
        'kodlokaliti',
        'kodpar_pru12',
        'poskod'
    ];

    /**
     * Import CSV into pengundi_raw table
     */

    public function import(Request $request)
    {
        $count = 0;
        $rows = [];

        $total = count($rows);

        Cache::put($this->cacheKey, 0);

        try {
            // Catch validation manually to log it
            try {
                $request->validate([
                    'file' => 'required|mimes:csv,txt|max:30720', // 30MB
                ]);
            } catch (ValidationException $ve) {
                Log::error('CSV Validation Error', [
                    'errors' => $ve->errors(),
                    'request' => $request->all(),
                ]);

                return response()->json([
                    'error' => 'Validation failed',
                    'details' => $ve->errors(),
                ], 422);
            }

            $file = $request->file('file');

            if (!$file || !$file->isValid()) {
                Log::error('File upload error', [
                    'file' => $file ? $file->getClientOriginalName() : null,
                    'error' => $file ? $file->getError() : 'No file',
                ]);

                return response()->json([
                    'error' => 'The file failed to upload.'
                ], 422);
            }

            Cache::put($this->cacheKey, 0);

            if (($handle = fopen($file->getRealPath(), 'r')) === false) {
                throw new \Exception('Failed to open uploaded CSV file.');
            }

            $header = null;

            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                if (!$header) {
                    $header = array_map(fn($h) => trim(strtoupper($h)), $data);
                    continue;
                }

                if (!array_filter($data, fn($v) => trim($v) !== '')) {
                    continue;
                }

                $rowData = [];
                foreach ($this->headerMap as $csvKey => $dbColumn) {
                    $index = array_search(strtoupper($csvKey), $header);
                    $value = $index !== false ? trim($data[$index]) : null;

                    if ($value === '') {
                        $value = null;
                    }

                    if (in_array($dbColumn, $this->integerColumns) && $value !== null) {
                        $value = is_numeric($value) ? (int) $value : null;
                    }

                    $rowData[$dbColumn] = $value;
                }

                $rows[] = $rowData;
                $count++;

                if ($count % $this->batchSize === 0) {
                    DB::table('pengundi_raw')->insert($rows);
                    $rows = [];
                    Cache::put($this->cacheKey, $count);
                }
            }

            if (!empty($rows)) {
                DB::table('pengundi_raw')->insert($rows);
            }

            fclose($handle);
            Cache::put($this->cacheKey, $count);

            return response()->json([
                'success' => "Imported $count rows successfully!"
            ]);
        } catch (\Exception $e) {
            Log::error('CSV Import Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get current import progress for JS polling
     */
    public function progress()
    {
        return response()->json([
            'count' => Cache::get($this->cacheKey, 0)
        ]);
    }
}
