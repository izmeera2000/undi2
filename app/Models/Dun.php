<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dun extends Model
{
    //
    protected $table = 'dun';

        protected $fillable = ['kod_dun','namadun','parlimen_id'];
}
