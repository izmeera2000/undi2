<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dm extends Model
{
    //
    protected $table = 'dm';

        protected $fillable = ['koddm','namadm','dun_id'];



            public function dun()
    {
        return $this->belongsTo(Dun::class, 'dun_id');
    }


     
}
