<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoDocumentation extends Model
{
    use HasFactory;

    public function uploader() {
        return $this->hasOne(User::class, 'id', 'uploader');
    }

    public function task() {
        return $this->hasOne(Task::class, 'id', 'task_id');
    }

    public function uploadedFiles() {
        return $this->hasMany(PhotoDocumentationFiles::class);
    }
}
