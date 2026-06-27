<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->dateTime('due_date');
            $table->foreignId('status_id')->constrained('statuses');
            $table->foreignId('priority_id')->constrained('priorities');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index('due_date');
            $table->index('status_id');
            $table->index('priority_id');
            $table->index('category_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
