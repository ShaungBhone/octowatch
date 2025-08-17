<?php

use App\Models\Octo\Repository;
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
        Schema::create('octo_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Repository::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->string('octo_id')->unique();
            $table->text('body');
            $table->string('author_login')->nullable();
            $table->string('author_avatar_url')->nullable();
            $table->string('html_url')->nullable();
            $table->timestamp('created_at_github')->nullable();
            $table->timestamp('updated_at_github')->nullable();

            $table->enum('type', [
                'issue',
                'pull_request',
                'commit'
            ]);

            $table->index(['octo_repository_id', 'type']);
        });
    }
};
