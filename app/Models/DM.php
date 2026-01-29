<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DM extends Model
{
    use HasFactory;

    protected $table = 'dms'; // your DM table

    protected $fillable = [
        'name',
        'dun_id',
        'code',
    ];



    public function dun()
    {
        return $this->belongsTo(DUN::class, 'dun_id');
    }


}
