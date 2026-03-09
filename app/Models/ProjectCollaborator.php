<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCollaborator extends Model
{
    use HasFactory;

    public function user() {
        return $this->belongsTo(User::class, 'collaborator_id', 'id');
    }
}
