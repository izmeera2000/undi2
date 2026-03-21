<?php

namespace App\Http\Controllers;

use App\Jobs\TransferPengundiJob;
use App\Jobs\TransferPengundiJob2;
use App\Models\{Dun, Dm, Lokaliti, Parlimen};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;
use App\Jobs\ImportPengundiJob;

class PengundiImportController extends Controller
{
    protected string $importCacheKey = 'pengundi_import_progress';
    protected string $transferCacheKey = 'pengundi_transfer_progress';
    protected int $batchSize = 300;

    protected array $headerMap = [
        'KOD PAR' => 'kod_par',
        'NAMAPAR' => 'nama_par',
        'KOD DUN' => 'kod_dun',
        'NAMADUN' => 'nama_dun',
        'KODDM' => 'kod_dm',
        'NAMADM' => 'nama_dm',
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

    protected array $headerMap2 = [

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


    protected array $integerColumns = [
        'tahun_lahir',
        'umur',
    ];

    /**
     * Import CSV → pengundi_raw
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:csv,txt|max:30720',
                'tarikh_undian' => 'required|integer',
                'effective_from' => 'nullable|date',
                'effective_to' => 'nullable|date',

                'pilihan_raya_type' => 'required|string|in:PRU,PRN,PRK',
                'pilihan_raya_series' => 'required|integer|min:1',
            ]);

            $file = $request->file('file');
            $path = $file->getRealPath();
            $tarikhUndian = (int) $request->tarikh_undian;

            // Use defaults if not provided
            $effectiveFrom = $request->input('effective_from')
                ? $request->input('effective_from')
                : now();
            $effectiveTo = $request->input('effective_to')
                ? $request->input('effective_to')
                : null;

            // 1️⃣ Count total rows
            $total = 0;
            if (($h = fopen($path, 'r')) !== false) {
                fgetcsv($h); // skip header
                while (fgetcsv($h))
                    $total++;
                fclose($h);
            }

            Cache::put($this->importCacheKey, [
                'count' => 0,
                'total' => $total,
            ]);

            // 2️⃣ Import data
            $handle = fopen($path, 'r');
            $header = array_map('strtoupper', fgetcsv($handle));

            $rows = [];
            $count = 0;

            while (($data = fgetcsv($handle)) !== false) {
                if (!array_filter($data))
                    continue;

                $row = [];
                foreach ($this->headerMap as $csv => $db) {
                    $idx = array_search(strtoupper($csv), $header);
                    $val = $idx !== false ? trim($data[$idx]) : null;

                    if ($val === '')
                        $val = null;
                    if (in_array($db, $this->integerColumns) && $val !== null) {
                        $val = is_numeric($val) ? (int) $val : null;
                    }

                    $row[$db] = $val;
                }

                $rows[] = $row;
                $count++;

                if ($count % $this->batchSize === 0) {
                    DB::table('pengundi_raw')->insert($rows);
                    $rows = [];
                    Cache::put($this->importCacheKey, [
                        'count' => $count,
                        'total' => $total,
                    ]);
                }
            }

            if ($rows) {
                DB::table('pengundi_raw')->insert($rows);
            }

            fclose($handle);

            Cache::put($this->importCacheKey, [
                'count' => $count,
                'total' => $total,
            ]);

            // 3️⃣ Dispatch transfer job (pass effective dates and transfer cache)
            try {
                $job = new TransferPengundiJob(
                    $tarikhUndian,
                    $effectiveFrom,
                    $effectiveTo,
                    $request->pilihan_raya_type,
                    $request->pilihan_raya_series
                );

                $job->handle($this->transferCacheKey);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Error dispatching transfer job: ' . $e->getMessage()], 500);
            }

            Cache::forget($this->importCacheKey);

            return response()->json([
                'success' => "Imported $count rows. Transfer started."
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Invalid CSV file'], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }




    public function import2(Request $request)
    {
        try {
            Log::info('Import2 request data:', $request->all());

            $request->merge([
                'enable_effective' => $request->has('enable_effective'),
            ]);

            $request->validate([
                'file' => 'required|mimes:csv,txt|max:30720',
                'election_id' => 'required|exists:elections,id',
                'enable_effective' => 'nullable|boolean',
                'effective_from' => 'date|nullable',
                'effective_to' => 'date|nullable',
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            Log::info('Uploaded file info:', [
                'original_name' => $originalName,
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);

            $path = $file->store('imports');
            Log::info('File stored at path:', ['path' => $path]);

            $enableEffective = $request->enable_effective;

            $effectiveFrom = $request->effective_from;
            $effectiveTo = $request->effective_to;

            Log::info('Effective settings:', [
                'enable_effective' => $enableEffective,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
            ]);

            $cacheKey = 'import_' . Str::uuid();
            Log::info('Dispatching ImportPengundiJob with cache key:', ['cache_key' => $cacheKey]);

            ImportPengundiJob::dispatch(
                $path,
                $request->election_id,
                $cacheKey,
                auth()->id(),
                $originalName,
                $enableEffective,
                $effectiveFrom,
                $effectiveTo
            );

            return response()->json([
                'success' => 'Import started',
                'cache_key' => $cacheKey
            ]);
        } catch (\Exception $e) {
            Log::error('Import2 failed:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Import progress
     */
    public function importProgress()
    {
        return response()->json(
            Cache::get($this->importCacheKey, ['count' => 0, 'total' => 1])
        );
    }

    /**
     * Transfer progress
     */
    public function transferProgress()
    {
        return response()->json(
            Cache::get($this->transferCacheKey, ['count' => 0, 'total' => 1])
        );
    }
}
