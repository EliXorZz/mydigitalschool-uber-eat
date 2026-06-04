<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('city')->nullable()->change();
            $table->string('image')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->decimal('score', 2, 1)->nullable()->change();
            $table->integer('price_score')->nullable()->change();
        });

        Schema::table('dishes', function (Blueprint $table) {
            $table->string('image')->nullable()->change();
            $table->text('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('city')->nullable(false)->change();
            $table->string('image')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->decimal('score', 2, 1)->nullable(false)->change();
            $table->integer('price_score')->nullable(false)->change();
        });

        Schema::table('dishes', function (Blueprint $table) {
            $table->string('image')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
        });
    }
};
