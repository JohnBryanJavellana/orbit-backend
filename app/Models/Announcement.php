<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function creator() {
        return $this->hasOne(User::class, 'id', 'creator_id');
    }

    public function attachments() {
        return $this->hasMany(AnnouncementAttachment::class);
    }
}
