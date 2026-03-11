<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuraPointsRecord extends Model
{
    use HasFactory;

    public function pointReceiver() {
        return $this->hasOne(User::class, 'id', 'point_receiver');
    }
}
