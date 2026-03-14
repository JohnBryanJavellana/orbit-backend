<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const DAILY_ROULETTE = [
        'TAKEN',
        'PENDING'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_activities_rewards', function (Blueprint $table) {
            $table->engine('innoDB');
            $table->id();
            $table->foreignIdFor(User::class, 'initiator')->constrained('users')->cascadeOnDelete();
            $table->enum('daily_roulette', self::DAILY_ROULETTE)->default(self::DAILY_ROULETTE[1]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_activities_rewards');
    }
};
