<?php

namespace App\Jobs;

use App\Models\MembersRaw;
use App\Models\Parlimen;
use App\Models\Dun;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Bus\Dispatchable;

class TransferMembersJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 0; // no timeout
    public $tries = 3;

    public function handle()
    {
        MembersRaw::orderBy('id')
            ->chunk(1000, function ($rows) {

                foreach ($rows as $row) {

                    DB::transaction(function () use ($row) {

                        // 1️⃣ Parlimen
                        $parlimen = Parlimen::firstOrCreate(
                            ['kod_par' => $row->kod_bhgn],
                            ['namapar' => $row->nama_bhgn]
                        );

                        // 2️⃣ DUN
                        $dun = Dun::firstOrCreate(
                            [
                                'parlimen_id' => $parlimen->id,
                                'kod_dun' => $row->kod_dun
                            ],
                            [
                                'nama_dun' => $row->nama_dun
                            ]
                        );

                        // 3️⃣ Member (SIMPAN dun_id)
                        Member::updateOrCreate(
                            [
                                'nokp_baru' => $row->nokp_baru
                            ],
                            [
                                'dun_id' => $dun->id,   // ✅ INI JAWAPAN UTAMA ANDA
    
                                'kod_cwgn' => $row->kod_cwgn,
                                'nama_cwgn' => $row->nama_cwgn,
                                'no_ahli' => $row->no_ahli,
                                'nokp_lama' => $row->nokp_lama,
                                'nama' => $row->nama,
                                'tahun_lahir' => $row->tahun_lahir,
                                'umur' => $row->umur,
                                'jantina' => $row->jantina,
                                'alamat_1' => $row->alamat_1,
                                'alamat_2' => $row->alamat_2,
                                'alamat_3' => $row->alamat_3,
                                'bangsa' => $row->bangsa,
                                'kod_dm' => $row->kod_dm,
                                'alamat_jpn_1' => $row->alamat_jpn_1,
                                'alamat_jpn_2' => $row->alamat_jpn_2,
                                'alamat_jpn_3' => $row->alamat_jpn_3,
                                'poskod' => $row->poskod,
                                'bandar' => $row->bandar,
                                'negeri' => $row->negeri,
                            ]
                        );

                    });
                }
            });
    }
}
