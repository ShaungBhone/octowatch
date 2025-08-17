<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('octo_issues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('octo_connection_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('octo_repository_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('issue_id')->unique();
            $table->integer('number');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('state');
            $table->string('author_login')->nullable();
            $table->string('author_avatar_url')->nullable();
            $table->json('labels')->nullable();
            $table->json('assignees')->nullable();
            // $table->string('milestone')->nullable();
            $table->integer('comments_count')->default(0);
            // $table->boolean('locked')->default(false);
            // $table->string('html_url');
            $table->timestamp('created_at_github')->nullable();
            $table->timestamp('updated_at_github')->nullable();
            $table->timestamp('closed_at_github')->nullable();
            $table->timestamps();
        });
    }
};
