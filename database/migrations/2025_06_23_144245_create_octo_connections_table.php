<?php

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
        Schema::create('octo_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('github_email')->nullable();
            $table->string('github_id')->unique();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->string('username');
            $table->string('avatar_url')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'github_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('octo_connections');
    }
};
