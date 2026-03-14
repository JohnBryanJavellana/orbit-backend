<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomAvatar extends Model
{
    use HasFactory;

    protected $appends = ['total_active_users'];

    public function usersConnection() {
        return $this->hasMany(UserCustomAvatar::class);
    }

    public function getTotalActiveUsersAttribute() {
        return $this->usersConnection()->count();
    }
}
