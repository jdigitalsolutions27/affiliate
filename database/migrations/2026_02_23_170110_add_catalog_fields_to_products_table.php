<?php

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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
            $table->unsignedInteger('stock')->nullable()->after('price');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->boolean('is_best_seller')->default(false)->after('is_featured');

            $table->index(['category_id', 'status']);
            $table->index(['is_featured', 'is_best_seller']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'status']);
            $table->dropIndex(['is_featured', 'is_best_seller']);
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn([
                'stock',
                'is_featured',
                'is_best_seller',
            ]);
        });
    }
};

