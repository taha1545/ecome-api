<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->enum('type', [
                'bar', 
                'line', 
                'pie', 
                'doughnut', 
                'radar', 
                'polarArea'
            ])->default('bar');
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('created_at');
        });

        Schema::create('chart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chart_id')->constrained()->onDelete('cascade');
            $table->string('label', 80);
            $table->decimal('value', 12, 4)->default(0);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('chart_id');
            $table->index('position');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_items');
        Schema::dropIfExists('charts');
    }
};