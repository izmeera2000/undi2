<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parliament extends Model
{
    use HasFactory;

    protected $table = 'parliaments';

    protected $fillable = [
        'name',
        'code'
    ];

    /**
     * Relasi ke DUN
     * Satu Parliament boleh ada banyak DUN
     */
    public function duns()
    {
        return $this->hasMany(DUN::class,'parliament_id');
    }

 
}
