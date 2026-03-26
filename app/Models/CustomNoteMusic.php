<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomNoteMusic extends Model
{
    use HasFactory;

    public function usedInNotes() {
        return $this->hasMany(UserNote::class);
    }
}
