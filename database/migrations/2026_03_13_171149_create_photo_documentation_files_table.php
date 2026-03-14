<?php

use App\Models\PhotoDocumentation;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('photo_documentation_files', function (Blueprint $table) {
            $table->engine('innoDB');
            $table->id();
            $table->foreignIdFor(User::class, 'uploader')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(PhotoDocumentation::class)->constrained()->cascadeOnDelete();
            $table->longText('filename');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_documentation_files');
    }
};
