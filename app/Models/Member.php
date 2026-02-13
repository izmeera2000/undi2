<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{

    use SoftDeletes;


    protected $fillable = [
        'dun_id',
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
        return $this->belongsTo(Dun::class);
    }


    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture
            ? asset('storage/' . $this->profile_picture)
            : asset('assets/img/avatars/avatar-placeholder.webp');
    }

    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            'member_groups',   // pivot table
            'member_id',       // foreign key on pivot
            'group_id'         // related key on pivot
        )->withTimestamps();
    }


    public function pengundi()
    {
        return Pengundi::where(function ($q) {
            $q->where('nokp_baru', $this->nokp_baru)
                ->orWhere('nokp_lama', $this->nokp_lama);
        })->first();
    }

    public function pengundiGroupedByTarikh()
    {
        // Get all distinct tarikh_undian values in the table
        $allDates = Pengundi::select('tarikh_undian')->distinct()->pluck('tarikh_undian');

        // Fetch all pengundi records for this member
        $pengundi = Pengundi::where(function ($q) {
            $q->where('nokp_baru', $this->nokp_baru)
                ->orWhere('nokp_lama', $this->nokp_lama);
        })
            ->with('dm')
            ->get()
            ->groupBy('tarikh_undian');

        // Prepare result including empty dates
        $result = [];
        foreach ($allDates as $date) {
            // Keep all columns for each record in this date
            $result[$date] = $pengundi->get($date, collect()); // empty collection if no record
        }

        return $result; // each key is a date, value is a collection of full records
    }



    public function isPengundi(): bool
    {
        return $this->pengundi() !== null;
    }


}
