<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {

            $table->id();
            $table->string('name', 255)->unique();
            $table->text('description');
            $table->string('brand', 100);
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('brand');
            $table->index('price');
            $table->index('is_active');
            $table->index('views');
            $table->index('created_at');
            $table->fullText(['name', 'description']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('size', 50)->nullable();
            $table->string('color', 50)->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('price', 10, 2);

            // indexes
            $table->index(['product_id', 'size']);
            $table->index(['product_id', 'color']);
            $table->index(['size', 'color']);
            $table->index('quantity');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('rating');
            $table->text('message')->nullable();

            // indexes
            $table->index(['product_id', 'user_id']);
            $table->index('rating');
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('message');
            $table->timestamps();
            //   
            $table->index(['product_id', 'created_at']);
            $table->index('user_id');
        });

        Schema::create('product_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('path');
            $table->enum('type', ['image', 'document', '3d_model'])->default('image');
            //
            $table->index(['product_id', 'type']);
            $table->index('type');
        });

        Schema::create('saved_products', function (Blueprint $table) {
            //
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            // 
            $table->primary(['product_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_products');
        Schema::dropIfExists('product_files');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
