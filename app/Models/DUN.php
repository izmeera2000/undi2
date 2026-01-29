<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DUN extends Model
{
    use HasFactory;

    protected $table = 'duns'; // make sure your table is named 'duns'

    protected $fillable = [
        'name',
        'parliament_id',
        'code',
    ];

 

    public function parliament()
    {
        return $this->belongsTo(Parliament::class, 'parliament_id');
    }



}
