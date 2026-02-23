<?php

namespace App\Jobs;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransferMembersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue;

    protected string $tempTable = 'members_raw';
    protected int $batchSize = 300;
    protected string $cacheKey;

    // Only transfer these columns to members table
    protected array $allowedColumns = [
        'kod_dun',
        'kod_cwgn',
        'nama_cwgn',
        'no_ahli',
        'nokp_baru',
        'nokp_lama',
        'nama',
        'tahun_lahir',
        'umur',
        'jantina',
        'alamat_1',
        'alamat_2',
        'alamat_3',
        'bangsa',
        'kod_dm',
        'alamat_jpn_1',
        'alamat_jpn_2',
        'alamat_jpn_3',
        'poskod',
        'bandar',
        'negeri',
    ];

    public function __construct(string $cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    public function handle()
    {
        $total = DB::table($this->tempTable)->count();
        $count = 0;

        DB::table($this->tempTable)
            ->orderBy('id')
            ->chunk($this->batchSize, function ($rows) use (&$count, $total) {
                $insertData = [];

                foreach ($rows as $row) {
                    $rowArray = (array) $row;

                    // Filter only allowed columns
                    $filtered = array_intersect_key($rowArray, array_flip($this->allowedColumns));

                    // Add timestamps (assuming you want the current timestamp for both created_at and updated_at)
                    $filtered['created_at'] = now();
                    $filtered['updated_at'] = now();

                    $insertData[] = $filtered;
                }
                if ($insertData) {
                    Member::insert($insertData);
                }

                $count += count($rows);

                // Update progress cache
                Cache::put($this->cacheKey, ['count' => $count, 'total' => $total]);
            });

        // Clear temp table after transfer
        DB::table($this->tempTable)->truncate();

        Cache::put($this->cacheKey, ['count' => $total, 'total' => $total]);
    }
}