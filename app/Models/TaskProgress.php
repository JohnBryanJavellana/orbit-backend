<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskProgress extends Model
{
    use HasFactory;

    public function initiator() {
        return $this->hasOne(Member::class, 'id', 'member_id');
    }

    public function task() {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }
}
