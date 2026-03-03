<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TaskAssignment;

class Task extends Model
{
    protected $fillable = ["task_name", "default_price", "is_active"];

    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }
}
