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
            // 
            $table->string('desc', 300)->nullable();
            //
            $table->string('order_number', 50)->nullable();
            $table->string('transaction_id', 50)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('recu_path', 50)->nullable();
            //
            $table->enum('status', [
                'pending',
                'succeeded',
                'failed',
                'refunded',
            ])->default('pending');
            //
            $table->text('gateway_response')->nullable()->comment('Raw gateway response');
            $table->text('error_message')->nullable();
            //
            $table->timestamp('processed_at')->nullable();
            //
            $table->timestamps();
            // Indexes
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
