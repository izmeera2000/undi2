<?php

namespace App\Http\Controllers;

use App\Jobs\TransferPengundiJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PengundiImportController extends Controller
{
    protected string $cacheKey = 'pengundi_import_progress';
    protected int $batchSize = 300;

    protected array $headerMap = [
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
        ]);
        
        $file = $request->file('file');
        $path = $file->getRealPath();
$tarikhUndian = (int) $request->tarikh_undian; // e.g., 2022

        /** 1️⃣ Count total rows */
        $total = 0;
        if (($h = fopen($path, 'r')) !== false) {
            fgetcsv($h); // header
            while (fgetcsv($h))
                $total++;
            fclose($h);
        }

        Cache::put($this->cacheKey, [
            'count' => 0,
            'total' => $total,
        ]);

        /** 2️⃣ Import data */
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
                // Insert data in batches
                try {
                    DB::table('pengundi_raw')->insert($rows);
                    $rows = [];

                    Cache::put($this->cacheKey, [
                        'count' => $count,
                        'total' => $total,
                    ]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Database insertion failed: ' . $e->getMessage()], 500);
                }
            }
        }

        if ($rows) {
            try {
                DB::table('pengundi_raw')->insert($rows);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Database insertion failed: ' . $e->getMessage()], 500);
            }
        }

        fclose($handle);

        Cache::put($this->cacheKey, [
            'count' => $count,
            'total' => $total,
        ]);

        /** 3️⃣ Dispatch transfer job */
        try {
            $job = new TransferPengundiJob($tarikhUndian);
            $job->handle();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error dispatching transfer job: ' . $e->getMessage()], 500);
        }

        Cache::forget($this->cacheKey);

        return response()->json([
            'success' => "Imported $count rows. Transfer started."
        ]);
    } catch (ValidationException $e) {
        return response()->json(['error' => 'Invalid CSV file'], 422);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
    }
}


    /**
     * Progress polling
     */
    public function progress()
    {
        return response()->json(
            Cache::get($this->cacheKey, ['count' => 0, 'total' => 1])
        );
    }
}
