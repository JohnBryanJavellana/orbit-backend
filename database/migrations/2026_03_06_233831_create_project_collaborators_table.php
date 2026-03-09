<?php

use App\Models\Projects;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const COLLABORATOR_STATUS = [
        "PENDING",
        "ACTIVE",
        "TERMINATED",
        "DECLINED"
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_collaborators', function (Blueprint $table) {
            $table->engine('innoDB');
            $table->id();
            $table->foreignIdFor(User::class, 'added_by_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Projects::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'collaborator_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', self::COLLABORATOR_STATUS)->default(self::COLLABORATOR_STATUS[0]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_collaborators');
    }
};
