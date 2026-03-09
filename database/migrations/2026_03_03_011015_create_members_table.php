<?php

use App\Models\MemberRole;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const MEMBER_STATUS = [
        'PENDING',
        'ACTIVE',
        'TERMINATED',
        'DECLINED',
        'CANCELLED'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->foreignIdFor(Task::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'added_by_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'member_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(MemberRole::class)->nullable()->constrained()->cascadeOnDelete();
            $table->enum('status', self::MEMBER_STATUS)->default(self::MEMBER_STATUS[0]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
