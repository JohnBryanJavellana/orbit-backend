<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomAvatar extends Model
{
    use HasFactory;

    public function usersConnection() {
        return $this->hasMany(UserCustomAvatar::class);
    }
}
