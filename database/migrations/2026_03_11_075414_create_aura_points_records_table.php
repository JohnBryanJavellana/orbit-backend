<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const RECORD_STATUS = [
        'INCREASE',
        'DECREASE'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aura_points_records', function (Blueprint $table) {
            $table->engine('innoDB');
            $table->id();
            $table->foreignIdFor(User::class, 'point_receiver')->constrained('users')->cascadeOnDelete();
            $table->longText('reason');
            $table->enum('status', self::RECORD_STATUS);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aura_points_records');
    }
};
