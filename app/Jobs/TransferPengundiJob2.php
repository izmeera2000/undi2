<?php

namespace App\Jobs;

use App\Models\{PengundiRaw, Parlimen, Dun, Dm, Lokaliti, Pengundi};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

class TransferPengundiJob2 implements ShouldQueue
{
    use Dispatchable, SerializesModels, InteractsWithQueue, Queueable;

    protected $effectiveFrom;
    protected $effectiveTo;
    protected $pilihanRayaType;
    protected $pilihanRayaSeries;

    public function __construct($effectiveFrom = null, $effectiveTo = null, $pilihanRayaType = null, $pilihanRayaSeries = null)
    {
        $this->effectiveFrom = $effectiveFrom;
        $this->effectiveTo = $effectiveTo;
        $this->pilihanRayaType = $pilihanRayaType;       // 🔹 assign
        $this->pilihanRayaSeries = $pilihanRayaSeries;   // 🔹 assign
    }

    /**
     * Handle transfer with optional cache key for progress tracking
     */







    public function handle(string $cacheKey = null)
    {
        try {
            $total = PengundiRaw::count();
            $processed = 0;

            PengundiRaw::orderBy('id')->chunk(1000, function ($rows) use (&$processed, $total, $cacheKey) {
                // Start a new transaction for each chunk of data
                DB::transaction(function () use ($rows) {
                    // Initialize arrays to collect data for batch insert or upsert

                    $pengundiData = [];

                    $bangsaMap = [
                        'M' => 'melayu',
                        'C' => 'cina',
                        'I' => 'india',
                        'L' => 'lain-lain',
                    ];

                    foreach ($rows as $row) {
                        try {

                            // Normalize bangsa
                            $bangsaCode = strtoupper(trim($row->bangsa_spr ?? ''));



                            $bangsa = $bangsaMap[$bangsaCode] ?? strtolower($row->bangsa_spr ?? null);

                            $pengundiData[] = [
                                'nokp_baru' => $row->nokp_baru,
                                'kod_lokaliti' => $row->kodlokaliti,
                                'nama' => $row->nama,
                                'jantina' => $row->jantina,
                                'bangsa' => $bangsa,
                                'umur' => $row->umur,
                                'alamat_spr' => $row->alamat_spr,
                                'saluran' => $row->saluran,
                                'no_siri' => $row->no_siri,
                                'type_data_id' => 2,


                                'negeri' => $row->negeri,
                                'pilihan_raya_type' => $this->pilihanRayaType,
                                'pilihan_raya_series' => $this->pilihanRayaSeries,

                            ];

                        } catch (\Exception $e) {
                            Log::error('Error processing row: ' . $e->getMessage(), ['row' => $row]);
                        }
                    }






                    // 5️⃣ Insert or Upsert Pengundi
                    if (count($pengundiData) > 0) {
                        Pengundi::upsert($pengundiData, ['nokp_baru', 'tarikh_undian'], ['kod_lokaliti']);
                    }
                });

                // Track progress
                $processed += count($rows);
                if ($cacheKey) {
                    Cache::put($cacheKey, ['count' => $processed, 'total' => $total]);
                }
            });

            // Clear raw table
            DB::table('pengundi_raw')->truncate();

            if ($cacheKey) {
                Cache::forget($cacheKey);
            }
        } catch (\Exception $e) {
            Log::error('Error in TransferPengundiJob: ' . $e->getMessage());
        }
    }



}
