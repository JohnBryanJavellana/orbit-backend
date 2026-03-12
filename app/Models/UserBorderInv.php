<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBorderInv extends Model
{
    use HasFactory;

    public function customBorder() {
        return $this->hasOne(CustomBorder::class, 'id', 'custom_border_id');
    }
}
