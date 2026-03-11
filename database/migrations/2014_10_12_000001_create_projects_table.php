<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const PROJECT_STATUS = [
        'OPEN',
        'CLOSED',
        'IN PROGRESS',
        'COMPLETED',
        'ABANDONED'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->longText('ctrl');
            $table->foreignIdFor(User::class, 'creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 255);
            $table->longText('description');
            $table->bigInteger('completion_points');
            $table->enum('status', self::PROJECT_STATUS)->default(self::PROJECT_STATUS[0]);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
