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

                    $parlimen = Parlimen::updateOrCreate(
                        ['kod_par' => $row->kod_par],
                        ['namapar' => $row->namapar]
                    );

                    $dun = Dun::updateOrCreate(
                        ['kod_dun' => $row->kod_dun],
                        [
                            'parlimen_id' => $parlimen->id,
                            'namadun' => $row->namadun
                        ]
                    );

                    // Update or create based only on 'koddm' to prevent duplicates of the same 'koddm'
                    $dm = Dm::updateOrCreate(
                        ['koddm' => $row->koddm],  // Only use 'koddm' for uniqueness check
                        [
                            'dun_id' => $dun->id,  // Attach the dun_id to the dm record
                            'namadm' => $row->namadm
                        ]
                    );


                    Pengundi::updateOrCreate(
                        [
                            'nokp_baru' => $row->nokp_baru,
                            'tarikh_undian' => $this->tarikhUndian,
                        ],
                        [
                            'dm_id' => $dm->id,
                            'koddm' => $row->koddm,
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
