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

class TransferPengundiJob implements ShouldQueue
{
    use Dispatchable, SerializesModels,  InteractsWithQueue, Queueable;

    protected int $tarikhUndian;
    protected $effectiveFrom;
    protected $effectiveTo;
    protected $pilihanRayaType;
    protected $pilihanRayaSeries;

    public function __construct($tarikhUndian, $effectiveFrom = null, $effectiveTo = null, $pilihanRayaType = null, $pilihanRayaSeries = null)
    {
        $this->tarikhUndian = $tarikhUndian;
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
                    $parlimenData = [];
                    $dunData = [];
                    $dmData = [];
                    $lokalitiData = [];
                    $pengundiData = [];

                    foreach ($rows as $row) {
                        try {
                            // 1️⃣ Collect Parlimen Data
                            $parlimen = Parlimen::firstOrCreate(
                                ['kod_par' => $row->kod_par],
                                ['nama_par' => $row->nama_par]
                            );

                            // 2️⃣ Collect Dun Data
                            $dun = Dun::where('kod_dun', $row->kod_dun)
                                ->where('status', 'active')
                                ->latest('effective_from')
                                ->first();

                            if (!$dun) {
                                $dun = Dun::create([
                                    'kod_dun' => $row->kod_dun,
                                    'parlimen_id' => $parlimen->id,
                                    'nama_dun' => $row->nama_dun,
                                    'status' => 'active',
                                    'effective_from' => $this->effectiveFrom,
                                    'effective_to' => $this->effectiveTo,
                                ]);
                            }
                            // 3️⃣ Collect Dm Data
                            $dm = Dm::where('kod_dm', $row->kod_dm)
                                ->where('status', 'active')
                                ->latest('effective_from')
                                ->first();

                            if (!$dm) {
                                $dm = Dm::create([
                                    'kod_dm' => $row->kod_dm,
                                    'kod_dun' => $dun->kod_dun,  // Updated to use kod_dun instead of dun_id
                                    'nama_dm' => $row->nama_dm,
                                    'status' => 'active',
                                    'effective_from' => $this->effectiveFrom,
                                    'effective_to' => $this->effectiveTo,
                                ]);
                            }



                            // 4️⃣ Collect Lokaliti Data
                            $lokaliti = Lokaliti::where('kod_lokaliti', $row->kodlokaliti)
                                ->where('kod_dm', $dm->kod_dm)
                                ->latest('effective_from')
                                ->first();

                            if (!$lokaliti) {
                                $lokaliti = Lokaliti::create([
                                    'kod_lokaliti' => $row->kodlokaliti,
                                    'kod_dm' => $dm->kod_dm,
                                    'nama_lokaliti' => $row->namalokaliti,
                                    'effective_from' => $this->effectiveFrom,
                                    'effective_to' => $this->effectiveTo,
                                ]);
                            }


                            // 5️⃣ Collect Pengundi Data
                            $pengundiData[] = [
                                'nokp_baru' => $row->nokp_baru,
                                'tarikh_undian' => $this->tarikhUndian,
                                'kod_lokaliti' => $row->kodlokaliti,
                                'nokp_lama' => $row->nokp_lama,
                                'nama' => $row->nama,
                                'jantina' => $row->jantina,
                                'bangsa' => $row->bangsa_spr,
                                'umur' => $row->umur,
                                'tahun_lahir' => $row->tahun_lahir,
                                'alamat_spr' => $row->alamat_spr,
                                'alamat_jpn_1' => $row->alamat_jpn_1,
                                'alamat_jpn_2' => $row->alamat_jpn_2,
                                'alamat_jpn_3' => $row->alamat_jpn_3,
                                'poskod' => $row->poskod,
                                'bandar' => $row->bandar,
                                'negeri' => $row->negeri,
                                'status_umno' => $row->status_umno,
                                'status_baru' => $row->status_baru,
                                'saluran' => null,
                                'type_data_id' => 1,

                                'pilihan_raya_type' => $this->pilihanRayaType,
                                'pilihan_raya_series' => $this->pilihanRayaSeries,


                            ];
                        } catch (\Exception $e) {
                            Log::error('Error processing row: ' . $e->getMessage(), ['row' => $row]);
                        }
                    }

                    // 1️⃣ Insert or Upsert Parlimen
                    if (count($parlimenData) > 0) {
                        Parlimen::upsert($parlimenData, ['kod_par'], ['nama_par']);
                    }

                    // 2️⃣ Insert or Upsert Dun
                    if (count($dunData) > 0) {
                        Dun::upsert($dunData, ['kod_dun'], ['nama_dun', 'effective_from', 'effective_to']);
                    }

                    // 3️⃣ Insert or Upsert Dm
                    if (count($dmData) > 0) {
                        Dm::upsert($dmData, ['kod_dm'], ['nama_dm', 'effective_from', 'effective_to']);
                    }

                    // 4️⃣ Insert or Upsert Lokaliti
                    if (count($lokalitiData) > 0) {
                        Lokaliti::upsert($lokalitiData, ['kod_lokaliti'], ['nama_lokaliti', 'effective_from', 'effective_to']);
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
