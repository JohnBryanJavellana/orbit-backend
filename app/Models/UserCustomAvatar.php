<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCustomAvatar extends Model
{
    use HasFactory;

    public function customAvatar() {
        return $this->hasOne(CustomAvatar::class, 'id', 'custom_avatar_id');
    }
}
