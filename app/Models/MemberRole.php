<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberRole extends Model
{
    use HasFactory;

    protected $appends = ['total_active_users'];

    public function hasData() {
        return $this->hasMany(Member::class, 'member_role_id', 'id');
    }

    public function getTotalActiveUsersAttribute() {
        return $this->hasData()->count();
    }
}
