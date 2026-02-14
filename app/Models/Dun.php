<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dun extends Model
{
    //
    protected $table = 'dun';

        protected $fillable = ['kod_dun','namadun','parlimen_id'];


        
    public function parlimen()
    {
        return $this->belongsTo(Parlimen::class, 'parlimen_id');
    }

        public function dms()
    {
        return $this->hasMany(Dm::class, 'dun_id');
    }
}
