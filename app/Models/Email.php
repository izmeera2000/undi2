<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Email.php
class Email extends Model
{
    protected $fillable = [
        'from','to','subject','body','direction','email_date'
    ];
}
