<?php

declare(strict_types=1);

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
        Schema::create('octo_repositories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('octo_connection_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('repo_id')->unique();
            $table->string('name');
            $table->string('full_name')->unique();
            $table->text('description')->nullable();
            $table->integer('forks_count')->default(0);
            $table->integer('stargazers_count')->default(0);
            $table->string('language')->nullable();
            $table->boolean('private')->default(false);
            $table->integer('open_issues_count')->default(0);
            $table->integer('watchers_count')->default(0);
            $table->timestamp('updated_at_github')->nullable();
            $table->timestamps();
        });
    }
};
