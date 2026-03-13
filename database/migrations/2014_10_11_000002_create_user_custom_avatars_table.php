<?php

use App\Models\CustomAvatar;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const SHOWN_AVATAR = [
        "MAIN",
        "CUSTOM"
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_custom_avatars', function (Blueprint $table) {
            $table->engine('innoDB');
            $table->id();
            $table->string('profile_picture', 255)->default('default-profile-avatar.png');
            $table->foreignIdFor(CustomAvatar::class)->nullable()->constrained()->cascadeOnDelete();
            $table->enum('shown_avatar', self::SHOWN_AVATAR)->default(self::SHOWN_AVATAR[0]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_custom_avatars');
    }
};
