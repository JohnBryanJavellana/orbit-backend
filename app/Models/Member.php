<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    public function user() {
        return $this->hasOne(User::class, 'id', 'member_id');
    }

    public function added_by() {
        return $this->hasOne(User::class, 'id', 'added_by_id');
    }

    public function member_role() {
        return $this->hasOne(MemberRole::class, 'id', 'member_role_id');
    }
}
