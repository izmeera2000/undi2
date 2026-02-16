<?php

namespace App\Jobs;

use App\Models\{PengundiRaw, Parlimen, Dun, Dm, Lokaliti, Pengundi};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferPengundiJob
{
    use Dispatchable, SerializesModels;

    protected int $tarikhUndian;

    public function __construct(int $tarikhUndian)
    {
        $this->tarikhUndian = $tarikhUndian; // 2022
    }

    public function handle()
    {
        try {
            PengundiRaw::orderBy('id')->chunk(1000, function ($rows) {
                DB::transaction(function () use ($rows) {
                    foreach ($rows as $row) {
                        try {
                            // 1️⃣ Parlimen (Fixed Structure)
                            $parlimen = Parlimen::firstOrCreate(
                                ['kod_par' => $row->kod_par],
                                ['namapar' => $row->namapar]
                            );

                            // 2️⃣ DUN
                            $dun = Dun::firstOrCreate(
                                [
                                    'kod_dun' => $row->kod_dun,
                                    'status' => 'active'
                                ],
                                [
                                    'parlimen_id' => $parlimen->id,
                                    'namadun' => $row->namadun,
                                    'effective_from' => now()
                                ]
                            );

                            // 3️⃣ DM
                            $dm = Dm::firstOrCreate(
                                [
                                    'koddm' => $row->koddm,
                                    'status' => 'active'
                                ],
                                [
                                    'dun_id' => $dun->id,
                                    'namadm' => $row->namadm,
                                    'effective_from' => now()
                                ]
                            );

                            // 4️⃣ Lokaliti
                            $lokaliti = Lokaliti::firstOrCreate(
                                [
                                    'nama_lokaliti' => $row->namalokaliti,
                                    'dm_id' => $dm->id
                                ],
                                [
                                    'kod_lokaliti' => $row->kodlokaliti
                                ]
                            );

                            // 5️⃣ Pengundi (Now Uses lokaliti_id)
                            Pengundi::updateOrCreate(
                                [
                                    'nokp_baru' => $row->nokp_baru,
                                    'tarikh_undian' => $this->tarikhUndian,
                                    
                                ],
                                [
                                    'lokaliti_id' => $lokaliti->id,
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
                                ]
                            );
                        } catch (\Exception $e) {
                            Log::error('Error processing row: ' . $e->getMessage(), ['row' => $row]);
                            // You can also continue processing other rows after logging the error
                        }
                    }
                });
            });

            // Truncate the raw data after processing is complete
            DB::table('pengundi_raw')->truncate();
        } catch (\Exception $e) {
            // Log the exception and rethrow it if necessary
            Log::error('Error in TransferPengundiJob: ' . $e->getMessage());
            // Optionally, you could dispatch a failure notification or send an email
        }
    }
}
