<?php

namespace App\Jobs;

use App\Models\PengundiRaw;
use App\Models\Parlimen;
use App\Models\Dun;
use App\Models\Dm;
use App\Models\Pengundi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Bus\Dispatchable;


class TransferPengundiJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 0; // no timeout
    public $tries = 3;

    public function handle()
    {
        PengundiRaw::orderBy('id')
            ->chunk(1000, function ($rows) {

                foreach ($rows as $row) {

                    DB::transaction(function () use ($row) {

                        // 1️⃣ Parlimen
                        $parlimen = Parlimen::firstOrCreate(
                            ['kod_par' => $row->kod_par],
                            ['namapar' => $row->namapar]
                        );

                        // 2️⃣ DUN
                        $dun = Dun::firstOrCreate(
                            [
                                'parlimen_id' => $parlimen->id,
                                'kod_dun' => $row->kod_dun
                            ],
                            ['namadun' => $row->namadun]
                        );

                        // 3️⃣ DM
                        $dm = Dm::firstOrCreate(
                            [
                                'dun_id' => $dun->id,
                                'koddm' => $row->koddm
                            ],
                            ['namadm' => $row->namadm]
                        );

                        // 4️⃣ Pengundi
                        Pengundi::updateOrCreate(
                            [
                                'nokp_baru' => $row->nokp_baru,
                                'tarikh_undian' => 2022
                            ],
                            [
                                'dm_id' => $dm->id,
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
                    });
                }
            });
    }
}
