<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function sender() {
        return $this->hasOne(User::class, 'id', 'from_user');
    }

    public function receiver() {
        return $this->hasOne(User::class, 'id', 'to_user');
    }
}
