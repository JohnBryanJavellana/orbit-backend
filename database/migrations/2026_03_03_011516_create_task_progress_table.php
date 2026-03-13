<?php

use App\Models\Member;
use App\Models\Task;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const PROGRESS_STATUS = [
        'PENDING',
        'VERIFIED',
        'NOT WORKING PROPERLY',
        'DECLINED',
        'VERIFIYING'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_progress', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->foreignIdFor(Task::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Member::class, 'member_id')->constrained('members')->cascadeOnDelete();
            $table->longText('activity');
            $table->longText('remarks')->nullable();
            $table->enum('status', self::PROGRESS_STATUS)->default(self::PROGRESS_STATUS[0]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_progress');
    }
};
