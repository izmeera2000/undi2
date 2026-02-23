<?php

namespace App\Http\Controllers;

use App\Jobs\TransferMembersJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class MembersImportController extends Controller
{
    protected string $importCacheKey = 'members_import_progress';
    protected string $transferCacheKey = 'members_transfer_progress';
    protected int $batchSize = 300;

    protected array $headerMap = [
        'KODBHGN' => 'kod_bhgn',
        'NAMABHGN' => 'nama_bhgn',
        'KODDUN' => 'kod_dun',
        'NAMADUN' => 'nama_dun',
        'KODCWGN' => 'kod_cwgn',
        'NAMACWGN' => 'nama_cwgn',
        'NO_AHLI' => 'no_ahli',
        'NOKPBARU' => 'nokp_baru',
        'NOKPLAMA' => 'nokp_lama',
        'NAMA' => 'nama',
        'TAHUNLAHIR' => 'tahun_lahir',
        'UMUR' => 'umur',
        'JANTINA' => 'jantina',
        'ALAMAT_1' => 'alamat_1',
        'ALAMAT_2' => 'alamat_2',
        'ALAMAT_3' => 'alamat_3',
        'BANGSA' => 'bangsa',
        'KODDM' => 'kod_dm',
        'ALAMAT JPN 1' => 'alamat_jpn_1',
        'ALAMAT JPN 2' => 'alamat_jpn_2',
        'ALAMAT JPN 3' => 'alamat_jpn_3',
        'POSKOD' => 'poskod',
        'BANDAR' => 'bandar',
        'NEGERI' => 'negeri',
    ];

    protected array $integerColumns = ['tahun_lahir', 'umur'];

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:csv,txt|max:30720',
            ]);

            $file = $request->file('file');
            $path = $file->getRealPath();

            // Count total rows
            $total = 0;
            if (($h = fopen($path, 'r')) !== false) {
                fgetcsv($h); // skip header
                while (fgetcsv($h)) $total++;
                fclose($h);
            }

            Cache::put($this->importCacheKey, ['count' => 0, 'total' => $total]);

            // Import CSV into members_raw
            $handle = fopen($path, 'r');
            $header = array_map('strtoupper', fgetcsv($handle));
            $rows = [];
            $count = 0;

            while (($data = fgetcsv($handle)) !== false) {
                if (!array_filter($data)) continue;

                $row = [];
                foreach ($this->headerMap as $csv => $db) {
                    $idx = array_search(strtoupper($csv), $header);
                    $val = $idx !== false ? trim($data[$idx]) : null;
                    if ($val === '') $val = null;
                    if (in_array($db, $this->integerColumns) && $val !== null) {
                        $val = is_numeric($val) ? (int)$val : null;
                    }
                    $row[$db] = $val;
                }

                $rows[] = $row;
                $count++;

                if ($count % $this->batchSize === 0) {
                    DB::table('members_raw')->insert($rows);
                    $rows = [];
                    Cache::put($this->importCacheKey, ['count' => $count, 'total' => $total]);
                }
            }

            if ($rows) {
                DB::table('members_raw')->insert($rows);
            }

            fclose($handle);

            Cache::put($this->importCacheKey, ['count' => $count, 'total' => $total]);

            // Dispatch transfer job AFTER import
            TransferMembersJob::dispatch($this->transferCacheKey);

            return response()->json(['success' => "Imported $count rows. Transfer started."]);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Invalid CSV file'], 422);
        } catch (\Exception $e) {
            Log::error('Members Import Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function importProgress()
    {
        return response()->json(Cache::get($this->importCacheKey, ['count' => 0, 'total' => 1]));
    }

    public function transferProgress()
    {
        return response()->json(Cache::get($this->transferCacheKey, ['count' => 0, 'total' => 1]));
    }
}