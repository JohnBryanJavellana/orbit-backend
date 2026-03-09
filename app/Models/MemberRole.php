<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberRole extends Model
{
    use HasFactory;

    public function hasData() {
        return $this->hasMany(Member::class, 'member_role_id', 'id');
    }
}
