<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_vasilhame_model')) {
                $table->boolean('is_vasilhame_model')->default(false)->after('manages_stock');
                $table->index('is_vasilhame_model');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'is_vasilhame_model')) {
                $table->dropIndex(['is_vasilhame_model']);
                $table->dropColumn('is_vasilhame_model');
            }
        });
    }
};