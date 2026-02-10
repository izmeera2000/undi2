<?php

namespace App\Jobs;

use App\Models\{PengundiRaw, Parlimen, Dun, Dm, Pengundi};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class TransferPengundiJob
{
    use Dispatchable, SerializesModels;

    protected int $tarikhUndian;

    public function __construct(int $tarikhUndian)
    {
        $this->tarikhUndian = $tarikhUndian;
    }

    public function handle()
    {
        PengundiRaw::orderBy('id')->chunk(1000, function ($rows) {

            DB::transaction(function () use ($rows) {

                foreach ($rows as $row) {

                    $parlimen = Parlimen::firstOrCreate(
                        ['kod_par' => $row->kod_par],
                        ['namapar' => $row->namapar]
                    );

                    $dun = Dun::firstOrCreate(
                        [
                            'parlimen_id' => $parlimen->id,
                            'kod_dun' => $row->kod_dun
                        ],
                        ['namadun' => $row->namadun]
                    );

                    $dm = Dm::firstOrCreate(
                        [
                            'dun_id' => $dun->id,
                            'koddm' => $row->koddm
                        ],
                        ['namadm' => $row->namadm]
                    );

                    Pengundi::updateOrCreate(
                        [
                            'nokp_baru' => $row->nokp_baru,
                            'tarikh_undian' => $this->tarikhUndian,
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
                }
            });



        });


        DB::table('pengundi_raw')->truncate();


    }
}
