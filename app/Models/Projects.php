<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    use HasFactory;

    public function creator() {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function tasks() {
        return $this->hasMany(Task::class, 'projects_id', 'id');
    }

    public function collaborators() {
        return $this->hasMany(ProjectCollaborator::class, 'projects_id', 'id');
    }
}
