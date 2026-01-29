<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locality extends Model
{
    use HasFactory;

    protected $table = 'localities';

    protected $fillable = [
        'name',
         'dm_id',
         'code'
    ];

  

    public function dm()
    {
        return $this->belongsTo(DM::class, 'dm_id');
    }

    

    public function pengundi()
    {
        return $this->hasMany(Pengundi::class);
    }
}
