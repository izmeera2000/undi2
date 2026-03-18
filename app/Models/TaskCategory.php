<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Task;

 

class TaskCategory extends Model
{
    protected $table = 'tasks_categories';

    protected $fillable = ['name', 'description'];

    public function tasks() {
        return $this->hasMany(Task::class, 'category_id');
    }
}
