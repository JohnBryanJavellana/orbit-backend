<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomBorder extends Model
{
    use HasFactory;

    protected $appends = ['total_active_users'];

    public function borderUsers() {
        return $this->hasMany(User::class);
    }

    public function userInv() {
        return $this->hasMany(UserBorderInv::class);
    }

    public function getTotalActiveUsersAttribute() {
        return $this->borderUsers()->count() + $this->userInv()->count();
    }
}
