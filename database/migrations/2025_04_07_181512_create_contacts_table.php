<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Contact information
            $table->string('name', 100)->nullable();//
            $table->string('phone', 20);
            $table->string('email', 150)->nullable();//
            $table->text('notes')->nullable();
            
            // Relationship details
            $table->enum('type', [
                'personal', 
                'emergency', 
                'business',  
                'family', 
                'medical'
            ])->nullable();
            
            $table->boolean('is_primary')->default(false);
            
            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('created_at');
            $table->index(['user_id', 'is_primary']);
            $table->fullText(['name', 'email', 'notes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};