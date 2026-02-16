<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('status', ['todo', 'in_progress', 'done'])
                ->default('todo');

            $table->enum('priority', ['low', 'medium', 'high'])
                ->default('medium');

            $table->dateTime('due_at')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('task_categories')
                ->onDelete('set null');

            $table->json('tags')->nullable();

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
