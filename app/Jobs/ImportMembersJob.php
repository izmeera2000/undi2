<?php

namespace App\Jobs;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportMembersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;
    protected string $cacheKey = 'members_import_progress';
    protected int $batchSize = 300;

    protected array $groupCache = [];
    protected array $integerColumns = ['tahun_lahir', 'umur'];

    protected array $headerMap = [
        'kod_cwgn' => ['KODCWGN'],
        'nama_cwgn' => ['NAMACWGN', 'CAWANGAN'],
        'no_ahli' => ['NO_AHLI', 'NO AHLI'],
        'nokp_baru' => ['NOKPBARU', 'KP BARU'],
        'nokp_lama' => ['NOKPLAMA'],
        'nama' => ['NAMA'],
        'tahun_lahir' => ['TAHUNLAHIR'],
        'umur' => ['UMUR'],
        'jantina' => ['JANTINA'],
        'alamat_1' => ['ALAMAT_1', 'ALAMAT'],
        'alamat_2' => ['ALAMAT_2'],
        'alamat_3' => ['ALAMAT_3'],
        'bangsa' => ['BANGSA'],
        'kod_dm' => ['KODDM'],
        'alamat_jpn_1' => ['ALAMAT JPN 1'],
        'alamat_jpn_2' => ['ALAMAT JPN 2'],
        'alamat_jpn_3' => ['ALAMAT JPN 3'],
        'poskod' => ['POSKOD'],
        'bandar' => ['BANDAR'],
        'negeri' => ['NEGERI'],
        'phone' => ['NO TEL'],
        'status_ahli' => ['STATUS'],
        'kategori' => ['KATEGORI'],
    ];

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function handle()
    {
        $file = Storage::path($this->path);
        if (!file_exists($file))
            return;

        $handle = fopen($file, 'r');
        if (!$handle)
            return;

        // Read header
        $header = array_map(fn($h) => strtoupper(trim($h)), fgetcsv($handle, 0, ',', '"'));

        // Count total rows
        $total = 0;
        while (fgetcsv($handle, 0, ',', '"'))
            $total++;
        rewind($handle);
        fgetcsv($handle, 0, ',', '"'); // skip header

        Cache::put($this->cacheKey, ['count' => 0, 'total' => $total]);

        $rows = [];
        $count = 0;

        while (($data = fgetcsv($handle, 0, ',', '"')) !== false) {
            if (!array_filter($data))
                continue; // skip empty rows

            $row = $this->mapRow($header, $data);

            // skip if no IC
            if (empty($row['nokp_baru']))
                continue;

            // Split ALAMAT if necessary
            if (!empty($row['alamat_1']) && empty($row['alamat_2']) && empty($row['alamat_3'])) {
                $parts = array_map('trim', explode(',', $row['alamat_1'], 3));
                $row['alamat_1'] = $parts[0] ?? null;
                $row['alamat_2'] = $parts[1] ?? null;
                $row['alamat_3'] = $parts[2] ?? null;
            }

            // Resolve group
            $row['_group_id'] = !empty($row['kategori']) ? $this->resolveGroupId($row['kategori']) : null;
            unset($row['kategori']);

            $row['created_at'] = now();
            $row['updated_at'] = now();

            $rows[] = $row;
            $count++;

            if ($count % $this->batchSize === 0) {
                $this->insertBatch($rows);
                $rows = [];
                Cache::put($this->cacheKey, ['count' => $count, 'total' => $total]);
            }
        }

        if ($rows)
            $this->insertBatch($rows);

        fclose($handle);
        Cache::forget($this->cacheKey);
    }

    protected function mapRow(array $header, array $data): array
    {
        $row = [];
        foreach ($this->headerMap as $db => $aliases) {
            $idx = false;
            foreach ($aliases as $alias) {
                $idx = array_search($alias, $header);
                if ($idx !== false)
                    break;
            }
            $val = $idx !== false ? trim($data[$idx]) : null;
            if ($val === '')
                $val = null;
            if (in_array($db, $this->integerColumns) && $val !== null)
                $val = is_numeric($val) ? (int) $val : null;
            $row[$db] = $val;
        }
        return $row;
    }

    protected function insertBatch(array $rows): void
    {
        $memberRows = collect($rows)->map(fn($r) => array_diff_key($r, ['_group_id' => '']))->toArray();

        DB::table('members')->upsert(
            $memberRows,
            ['nokp_baru'],
            ['nama', 'no_ahli', 'alamat_1', 'alamat_2', 'alamat_3', 'umur', 'updated_at']
        );

        $nokps = collect($rows)->pluck('nokp_baru')->filter();
        $members = DB::table('members')->whereIn('nokp_baru', $nokps)->get()->keyBy('nokp_baru');

        $pivot = [];
        foreach ($rows as $r) {
            if (!empty($r['_group_id']) && isset($members[$r['nokp_baru']])) {
                $pivot[] = [
                    'member_id' => $members[$r['nokp_baru']]->id,
                    'group_id' => $r['_group_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if ($pivot)
            DB::table('member_groups')->insertOrIgnore($pivot);
    }

    protected function resolveGroupId(string $kategori): ?int
    {
        $kategori = strtoupper(trim($kategori));
        if (!$kategori)
            return null;
        if (isset($this->groupCache[$kategori]))
            return $this->groupCache[$kategori];

        $group = Group::firstOrCreate(['name' => $kategori], ['description' => null, 'created_by' => 1]);
        $this->groupCache[$kategori] = $group->id;

        return $group->id;
    }

    public function failed(\Throwable $exception)
    {
        Cache::forget($this->cacheKey);
        \Log::error('Members import failed', ['error' => $exception->getMessage()]);
    }
}