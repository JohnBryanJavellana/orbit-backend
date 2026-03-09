<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    public function members() {
        return $this->hasMany(Member::class);
    }

    public function creator() {
        return $this->hasOne(User::class, 'id', 'creator_id');
    }

    public function assignedProject() {
        return $this->belongsTo(Projects::class, 'projects_id', 'id');
    }
}
