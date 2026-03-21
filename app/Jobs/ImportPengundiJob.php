<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Pengundi;
use App\Notifications\PengundiImportDone;

class ImportPengundiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $electionId;
    protected $cacheKey;
    protected $userId;
    protected $originalName;

    protected $enableEffective;
    protected $effectiveFrom;
    protected $effectiveTo;

    protected $batchSize = 1000;

    public function __construct(
        $path,
        $electionId,
        $cacheKey,
        $userId,
        $originalName,
        $enableEffective = false,
        $effectiveFrom = null,
        $effectiveTo = null
    ) {
        $this->path = $path;
        $this->electionId = $electionId;
        $this->cacheKey = $cacheKey;
        $this->userId = $userId;
        $this->originalName = $originalName;

        $this->enableEffective = $enableEffective;
        $this->effectiveFrom = $effectiveFrom;
        $this->effectiveTo = $effectiveTo;
    }

    public function handle()
    {
        try {

            $fullPath = Storage::path($this->path);

            if (!file_exists($fullPath)) {
                Log::error("File not found: {$fullPath}");
                return;
            }

            $total = 0;
            if (($h = fopen($fullPath, 'r')) !== false) {
                fgetcsv($h);
                while (fgetcsv($h))
                    $total++;
                fclose($h);
            }

            Cache::put($this->cacheKey, ['count' => 0, 'total' => $total]);

            $handle = fopen($fullPath, 'r');

            $header = array_map(
                fn($h) =>
                strtoupper(str_replace([' ', '_'], '', trim($h))),
                fgetcsv($handle)
            );

            $headerIndex = array_flip($header);

            $get = function ($row, $keys) use ($headerIndex) {
                foreach ($keys as $key) {
                    $k = strtoupper(str_replace([' ', '_'], '', $key));
                    if (isset($headerIndex[$k])) {
                        $val = trim($row[$headerIndex[$k]]);
                        return $val !== '' ? $val : null;
                    }
                }
                return null;
            };

            $formatCode = fn($v, $len) =>
                $v ? str_pad(preg_replace('/\D/', '', $v), $len, '0', STR_PAD_LEFT) : null;

            $bangsaMap = [
                'M' => 'melayu',
                'C' => 'cina',
                'I' => 'india',
                'L' => 'lain-lain'
            ];

            $count = 0;

            $rows = [];

            $parlimen = [];
            $dun = [];
            $dm = [];
            $lokaliti = [];
            $normalize = fn($v) => $v ? trim($v) : null;

            while (($data = fgetcsv($handle)) !== false) {

                try {
                    if (!array_filter($data))
                        continue;

                    $bangsaRaw = $get($data, [ 'BANGSA_SPR','BANGSA']);
                    $bangsaCode = strtoupper(trim($bangsaRaw ?? ''));
                    $bangsa = $bangsaMap[$bangsaCode] ?? strtolower($bangsaRaw ?? null);

                    // =========================
                    // HIERARCHY DATA (ONLY IF ENABLED)
                    // =========================
                        $kodLokaliti = $normalize($formatCode($get($data, ['KOD LOKALITI', 'KODLOKALITI']), 10));

                    if ($this->enableEffective) {


                        $kodPar = $normalize($formatCode($get($data, ['KOD PAR', 'KODPAR']), 3));
                        $kodDun = $normalize($formatCode($get($data, ['KOD DUN', 'KODDUN']), 5));
                        $kodDm = $normalize($formatCode($get($data, ['KOD DM', 'KODDM']), 7));

 
                            $parlimen[$kodPar] = [
                                'kod_par' => $kodPar,
                                'nama_par' => $get($data, ['NAMA PAR', 'NAMAPAR']) ?: "PAR $kodPar",
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        

                        // Avoid duplication for dun
                             $dun[$kodDun] = [
                                'kod_dun' => $kodDun,
                                'nama_dun' => $get($data, ['NAMA DUN', 'NAMADUN']) ?: "DUN $kodDun",
                                'kod_par' => $kodPar,
                                'effective_from' => $this->effectiveFrom,
                                'effective_to' => $this->effectiveTo,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                  

                        // Avoid duplication for dm
                             $dm[$kodDm] = [
                                'kod_dm' => $kodDm,
                                'nama_dm' => $get($data, ['NAMA DM', 'NAMADM']) ?: "DM $kodDm",
                                'kod_dun' => $kodDun,
                                'effective_from' => $this->effectiveFrom,
                                'effective_to' => $this->effectiveTo,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                      
                        // Avoid duplication for lokaliti
                             $lokaliti[$kodLokaliti] = [
                                'kod_lokaliti' => $kodLokaliti,
                                'nama_lokaliti' => $get($data, ['NAMA LOKALITI', 'NAMALOKALITI']) ?: "LOKALITI $kodLokaliti",
                                'kod_dm' => $kodDm,
                                'effective_from' => $this->effectiveFrom,
                                'effective_to' => $this->effectiveTo,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        
                    }

                    // =========================
                    // PENGUNDI ROW
                    // =========================
                    $nokp = $get($data, ['NO KP', 'NOKP_BARU', 'IC']);
                    if (!$nokp)
                        continue;

                    $rows[] = [
                        'kod_lokaliti' => $kodLokaliti,
                        'nokp_baru' => $nokp,
                        'nokp_lama' => $get($data, ['NOKP_LAMA']),
                        'nama' => $get($data, ['NAMA']),
                        'jantina' => $get($data, ['JANTINA']),
                        'bangsa' => $bangsa,
                        'umur' => $get($data, ['UMUR']),
                        'alamat_spr' => $get($data, ['PM', 'ALAMAT_SPR']),
                        'saluran' => $get($data, ['SALURAN']),
                        'no_siri' => $get($data, ['NO_SIRI']),
                        'type_data_id' => 2,
                        'poskod' => $get($data, ['POSKOD']),
                        'bandar' => $get($data, ['BANDAR']),
                        'negeri' => $get($data, ['NEGERI']),

                        'election_id' => $this->electionId,
                        'status_baru' => $get($data, ['STATUS_BARU']),

                        'status_umno' => $get($data, ['STATUS_UMNO']),
                        'alamat_jpn_1' => $get($data, ['ALAMAT_JPN_1']),
                        'alamat_jpn_2' => $get($data, ['ALAMAT_JPN_2']),
                        'alamat_jpn_3' => $get($data, ['ALAMAT_JPN_3']),
                


                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $count++;

                    // =========================
                    // BATCH FLUSH
                    // =========================
                    if ($count % $this->batchSize === 0) {

                        DB::table('pengundi')->upsert(
                            $rows,
                            ['nokp_baru', 'election_id'],
                            [
                                'nokp_lama',
                                'nama',
                                'jantina',
                                'bangsa',
                                'umur',
                                'alamat_spr',
                                'saluran',
                                'no_siri',
                                'type_data_id',
                                'negeri',
                                'updated_at'
                            ]
                        );

                        $rows = [];

                        Cache::put($this->cacheKey, [
                            'count' => $count,
                            'total' => $total
                        ]);
                    }

                } catch (\Exception $e) {
                    Log::error('Row failed', [
                        'error' => $e->getMessage(),
                        'row' => $data
                    ]);
                }
            }

            // FINAL FLUSH
            if ($rows) {
                DB::table('pengundi')->upsert(
                    $rows,
                    ['nokp_baru', 'election_id'],
                    [
                        'nokp_lama',
                        'nama',
                        'jantina',
                        'bangsa',
                        'umur',
                        'alamat_spr',
                        'saluran',
                        'no_siri',
                        'type_data_id',
                        'negeri',
                        'updated_at'
                    ]
                );
            }
            if ($this->enableEffective) {


                $this->flushReference($parlimen, $dun, $dm, $lokaliti);
            }

            fclose($handle);

            Cache::forget($this->cacheKey);

            $user = $this->userId ? User::find($this->userId) : null;

            $election = \DB::table('elections')
                ->where('id', $this->electionId)
                ->first(['type', 'number', 'year']);


            activity()
                ->performedOn(new Pengundi())
                ->causedBy($user)
                ->withProperties([
                    'election_id' => $this->electionId,
                    'election_type' => $election->type ?? null,
                    'election_series' => $election->number ?? null,
                    'election_year' => $election->year ?? null,
                    'file' => $this->originalName,
                    'total_inserted' => $count,
                ])
                ->log('Import pengundi completed');


            if ($user) {
                $url = route('pengundi.list');

                $user->notify(new PengundiImportDone($url));
            }
        } catch (\Exception $e) {
            Log::error('Import failed', [
                'file' => $this->path,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function flushReference(&$parlimen, &$dun, &$dm, &$lokaliti)
    {
        if ($parlimen) {
            DB::table('parlimen')->upsert(
                array_map(fn($row) => [
                    'kod_par' => $row['kod_par'],
                    'nama_par' => $row['nama_par'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ], array_values($parlimen)),
                ['kod_par'],
                ['nama_par']
            );
            $parlimen = [];
        }

        if ($dun) {
            DB::table('dun')->upsert(
                array_map(fn($row) => [
                    'kod_dun' => $row['kod_dun'],
                    'kod_par' => $row['kod_par'],
                    'nama_dun' => $row['nama_dun'],
                    'effective_from' => $row['effective_from'] ?? null,
                    'effective_to' => $row['effective_to'] ?? null,
                               'created_at' => now(),
                    'updated_at' => now(),
                ], array_values($dun)),
                ['kod_dun','kod_par', 'nama_dun', 'effective_from', 'effective_to'],   // ✅ FIX
                            ['created_at','updated_at']

            );
            $dun = [];
        }

        if ($dm) {
            DB::table('dm')->upsert(
                array_map(fn($row) => [
                    'kod_dm' => $row['kod_dm'],
                    'kod_dun' => $row['kod_dun'],
                    'nama_dm' => $row['nama_dm'],
                    'effective_from' => $row['effective_from'] ?? null,
                    'effective_to' => $row['effective_to'] ?? null,
                               'created_at' => now(),
                    'updated_at' => now(),
                ], array_values($dm)),
                ['kod_dm','kod_dun', 'nama_dm', 'effective_from', 'effective_to'],  // ✅ FIX
                              ['created_at','updated_at']

            );
            $dm = [];
        }

        if ($lokaliti) {
            DB::table('lokaliti')->upsert(
                array_map(fn($row) => [
                    'kod_lokaliti' => $row['kod_lokaliti'],
                    'kod_dm' => $row['kod_dm'],
                    'nama_lokaliti' => $row['nama_lokaliti'],
                    'effective_from' => $row['effective_from'] ?? null,
                    'effective_to' => $row['effective_to'] ?? null,
                               'created_at' => now(),
                    'updated_at' => now(),
                ], array_values($lokaliti)),
                ['kod_lokaliti','kod_dm', 'nama_lokaliti', 'effective_from', 'effective_to'], // ✅ FIX
                ['created_at','updated_at']
            );
            $lokaliti = [];
        }
    }
}