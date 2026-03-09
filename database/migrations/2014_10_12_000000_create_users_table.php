<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const USER_ROLES = [
        "SUPERADMIN",
        "ADMINISTRATOR",
        "MEMBER"
    ];

    public const USER_GENDERS = [
        "MALE",
        "FEMALE"
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->date('birthday')->nullable();
            $table->string('profile_picture', 255)->default('default-profile-avatar.png');
            $table->longText('bio')->nullable();
            $table->enum('gender', self::USER_GENDERS);
            $table->enum('role', self::USER_ROLES);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->bigInteger('total_points')->default(0);
            $table->timestamp('last_seen_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
