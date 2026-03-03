<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
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
        'profile_picture',
    ];

    public function dun()
    {
        // Assuming you have a Dun model
        return $this->belongsTo(Dun::class, 'kod_dun', 'kod_dun');
    }

    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            'member_groups',
            'member_id',
            'group_id'
        )->withTimestamps();
    }

    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture
            ? asset('storage/' . $this->profile_picture)
            : asset('assets/img/avatars/avatar-placeholder.webp');
    }

    public function pengundi()
    {
        return Pengundi::where(function ($q) {
            $q->where('nokp_baru', $this->nokp_baru)
                ->orWhere('nokp_lama', $this->nokp_lama);
        })->first();
    }

    public function pengundiGroupedByElection()
    {
        // Get all distinct election type + series combinations
        $allElections = Pengundi::select('pilihan_raya_type', 'pilihan_raya_series')
            ->distinct()
            ->orderByDesc('pilihan_raya_series')
            ->get();

        // If no elections exist at all, return empty collection
        if ($allElections->isEmpty()) {
            return collect();
        }

        // Fetch pengundi records for this member
        $pengundiQuery = Pengundi::query();

        if ($this->nokp_baru || $this->nokp_lama) {
            $pengundiQuery->where(function ($q) {
                if ($this->nokp_baru) {
                    $q->where('nokp_baru', $this->nokp_baru);
                }

                if ($this->nokp_lama) {
                    $q->orWhere('nokp_lama', $this->nokp_lama);
                }
            });
        } else {
            // No IC at all → return empty collection immediately
            return collect();
        }

        $pengundi = $pengundiQuery
            ->with(['dm', 'lokaliti', 'dun', 'parlimen'])
            ->get()
            ->groupBy(
                fn($item) =>
                $item->pilihan_raya_type . '_' . $item->pilihan_raya_series
            );
        // Prepare result including empty elections
        $result = collect();

        foreach ($allElections as $election) {
            $key = $election->pilihan_raya_type . '_' . $election->pilihan_raya_series;
            $result->put($key, $pengundi->get($key, collect()));
        }

        return $result;
    }

}