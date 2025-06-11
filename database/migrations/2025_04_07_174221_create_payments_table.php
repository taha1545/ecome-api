<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Payment details
            $table->string('method', 50); 
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('DA');
            $table->enum('status', [
                'pending', 
                'succeeded', 
                'failed', 
                'refunded',
                'requires_action'
            ])->default('pending');
            
            // Gateway information
            $table->string('gateway_id', 100)->comment('Payment processor transaction ID');
            $table->text('gateway_response')->nullable()->comment('Raw gateway response');
            
            // Additional tracking
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('method');
            $table->index('gateway_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};