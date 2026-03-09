<?php

use App\Models\Projects;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const TASK_STATUS = [
        "OPEN",
        "CLOSED",
        "IN PROGRESS",
        "COMPLETED"
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->longText('ctrl');
            $table->foreignIdFor(User::class, 'creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Projects::class)->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->longText('description');
            $table->enum('status', self::TASK_STATUS)->default(self::TASK_STATUS[0]);
            $table->bigInteger('task_completion_points');
            $table->bigInteger('task_progress_points');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
