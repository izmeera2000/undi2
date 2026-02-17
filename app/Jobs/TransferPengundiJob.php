<?php

namespace App\Jobs;

use App\Models\{PengundiRaw, Parlimen, Dun, Dm, Lokaliti, Pengundi};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferPengundiJob
{
    use Dispatchable, SerializesModels;

    protected int $tarikhUndian;
    protected $effectiveFrom;
    protected $effectiveTo;

    public function __construct(int $tarikhUndian, $effectiveFrom = null, $effectiveTo = null)
    {
        $this->tarikhUndian = $tarikhUndian;
        $this->effectiveFrom = $effectiveFrom ?? now();
        $this->effectiveTo = $effectiveTo; // null by default
    }

    /**
     * Handle transfer with optional cache key for progress tracking
     */


    // public function handleWithCache(string $cacheKey = null)
    // {
    //     try {
    //         $total = PengundiRaw::count();
    //         $processed = 0;

    //         PengundiRaw::orderBy('id')->chunk(1000, function ($rows) use (&$processed, $total, $cacheKey) {
    //             DB::transaction(function () use ($rows) {
    //                 foreach ($rows as $row) {
    //                     try {
    //                         // 1️⃣ Parlimen
    //                         $parlimen = Parlimen::firstOrCreate(
    //                             ['kod_par' => $row->kod_par],
    //                             ['namapar' => $row->namapar]
    //                         );

    //                         // 2️⃣ DUN with effective_from / effective_to
    //                         $dun = Dun::where('kod_dun', $row->kod_dun)
    //                             ->where('status', 'active')
    //                             ->latest('effective_from')
    //                             ->first();

    //                         if ($dun && $dun->namadun !== $row->namadun) {
    //                             $dun->update(['effective_to' => $this->effectiveFrom]);
    //                             $dun = Dun::create([
    //                                 'kod_dun' => $row->kod_dun,
    //                                 'parlimen_id' => $parlimen->id,
    //                                 'namadun' => $row->namadun,
    //                                 'status' => 'active',
    //                                 'effective_from' => $this->effectiveFrom,
    //                                 'effective_to' => $this->effectiveTo,
    //                             ]);
    //                         } elseif (!$dun) {
    //                             $dun = Dun::create([
    //                                 'kod_dun' => $row->kod_dun,
    //                                 'parlimen_id' => $parlimen->id,
    //                                 'namadun' => $row->namadun,
    //                                 'status' => 'active',
    //                                 'effective_from' => $this->effectiveFrom,
    //                                 'effective_to' => $this->effectiveTo,
    //                             ]);
    //                         }

    //                         // 3️⃣ DM with effective_from / effective_to
    //                         $dm = Dm::where('koddm', $row->koddm)
    //                             ->where('status', 'active')
    //                             ->latest('effective_from')
    //                             ->first();

    //                         if ($dm && $dm->namadm !== $row->namadm) {
    //                             $dm->update(['effective_to' => $this->effectiveFrom]);
    //                             $dm = Dm::create([
    //                                 'koddm' => $row->koddm,
    //                                 'dun_id' => $dun->id,
    //                                 'namadm' => $row->namadm,
    //                                 'status' => 'active',
    //                                 'effective_from' => $this->effectiveFrom,
    //                                 'effective_to' => $this->effectiveTo,
    //                             ]);
    //                         } elseif (!$dm) {
    //                             $dm = Dm::create([
    //                                 'koddm' => $row->koddm,
    //                                 'dun_id' => $dun->id,
    //                                 'namadm' => $row->namadm,
    //                                 'status' => 'active',
    //                                 'effective_from' => $this->effectiveFrom,
    //                                 'effective_to' => $this->effectiveTo,
    //                             ]);
    //                         }

    //                         // 4️⃣ Lokaliti with effective_from / effective_to
    //                         $lokaliti = Lokaliti::where('kod_lokaliti', $row->kodlokaliti)
    //                             ->where('dm_id', $dm->id)
    //                             ->latest('effective_from')
    //                             ->first();

    //                         if ($lokaliti && $lokaliti->nama_lokaliti !== $row->namalokaliti) {
    //                             $lokaliti->update(['effective_to' => $this->effectiveFrom]);
    //                             $lokaliti = Lokaliti::create([
    //                                 'kod_lokaliti' => $row->kodlokaliti,
    //                                 'dm_id' => $dm->id,
    //                                 'nama_lokaliti' => $row->namalokaliti,
    //                                 'effective_from' => $this->effectiveFrom,
    //                                 'effective_to' => $this->effectiveTo,
    //                             ]);
    //                         } elseif (!$lokaliti) {
    //                             $lokaliti = Lokaliti::create([
    //                                 'kod_lokaliti' => $row->kodlokaliti,
    //                                 'dm_id' => $dm->id,
    //                                 'nama_lokaliti' => $row->namalokaliti,
    //                                 'effective_from' => $this->effectiveFrom,
    //                                 'effective_to' => $this->effectiveTo,
    //                             ]);
    //                         }

    //                         // 5️⃣ Pengundi (store only kod_lokaliti)
    //                         Pengundi::updateOrCreate(
    //                             [
    //                                 'nokp_baru' => $row->nokp_baru,
    //                                 'tarikh_undian' => $this->tarikhUndian,
    //                             ],
    //                             [
    //                                 'kod_lokaliti' => $row->kodlokaliti,
    //                                 'nokp_lama' => $row->nokp_lama,
    //                                 'nama' => $row->nama,
    //                                 'jantina' => $row->jantina,
    //                                 'bangsa' => $row->bangsa_spr,
    //                                 'umur' => $row->umur,
    //                                 'tahun_lahir' => $row->tahun_lahir,
    //                                 'alamat_spr' => $row->alamat_spr,
    //                                 'alamat_jpn_1' => $row->alamat_jpn_1,
    //                                 'alamat_jpn_2' => $row->alamat_jpn_2,
    //                                 'alamat_jpn_3' => $row->alamat_jpn_3,
    //                                 'poskod' => $row->poskod,
    //                                 'bandar' => $row->bandar,
    //                                 'negeri' => $row->negeri,
    //                                 'status_umno' => $row->status_umno,
    //                                 'status_baru' => $row->status_baru,
    //                             ]
    //                         );
    //                     } catch (\Exception $e) {
    //                         Log::error('Error processing row: ' . $e->getMessage(), ['row' => $row]);
    //                     }
    //                 }
    //             });

    //             $processed += count($rows);
    //             if ($cacheKey) {
    //                 Cache::put($cacheKey, ['count' => $processed, 'total' => $total]);
    //             }
    //         });

    //         // Clear raw table
    //         DB::table('pengundi_raw')->truncate();

    //         if ($cacheKey) {
    //             Cache::forget($cacheKey);
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Error in TransferPengundiJob: ' . $e->getMessage());
    //     }
    // }


    public function handleWithCache(string $cacheKey = null)
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
                                ['namapar' => $row->namapar]
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
                                    'namadun' => $row->namadun,
                                    'status' => 'active',
                                    'effective_from' => $this->effectiveFrom,
                                    'effective_to' => $this->effectiveTo,
                                ]);
                            }
                            // 3️⃣ Collect Dm Data
                            $dm = Dm::where('koddm', $row->koddm)
                                ->where('status', 'active')
                                ->latest('effective_from')
                                ->first();

                            if (!$dm) {
                                $dm = Dm::create([
                                    'koddm' => $row->koddm,
                                    'kod_dun' => $dun->kod_dun,  // Updated to use kod_dun instead of dun_id
                                    'namadm' => $row->namadm,
                                    'status' => 'active',
                                    'effective_from' => $this->effectiveFrom,
                                    'effective_to' => $this->effectiveTo,
                                ]);
                            }



                            // 4️⃣ Collect Lokaliti Data
                            $lokaliti = Lokaliti::where('kod_lokaliti', $row->kodlokaliti)
                                ->where('koddm', $dm->koddm)
                                ->latest('effective_from')
                                ->first();

                            if (!$lokaliti) {
                                $lokaliti = Lokaliti::create([
                                    'kod_lokaliti' => $row->kodlokaliti,
                                    'koddm' => $dm->koddm,
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
                            ];
                        } catch (\Exception $e) {
                            Log::error('Error processing row: ' . $e->getMessage(), ['row' => $row]);
                        }
                    }

                    // 1️⃣ Insert or Upsert Parlimen
                    if (count($parlimenData) > 0) {
                        Parlimen::upsert($parlimenData, ['kod_par'], ['namapar']);
                    }

                    // 2️⃣ Insert or Upsert Dun
                    if (count($dunData) > 0) {
                        Dun::upsert($dunData, ['kod_dun'], ['namadun', 'effective_from', 'effective_to']);
                    }

                    // 3️⃣ Insert or Upsert Dm
                    if (count($dmData) > 0) {
                        Dm::upsert($dmData, ['koddm'], ['namadm', 'effective_from', 'effective_to']);
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
