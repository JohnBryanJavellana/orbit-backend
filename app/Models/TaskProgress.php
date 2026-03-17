<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskProgress extends Model
{
    use HasFactory;

    protected $appends = ['progress_attachments'];

    public function initiator() {
        return $this->hasOne(Member::class, 'id', 'member_id');
    }

    public function task() {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    public function getProgressAttachmentsAttribute() {
        return $this->attachments()->get();
    }

    public function attachments() {
        return $this->hasMany(ProgressAttachment::class, 'progress_id', 'id');
    }
}
