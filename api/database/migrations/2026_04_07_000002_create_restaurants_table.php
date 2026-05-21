<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->text('description');
            $table->decimal('score', 2, 1);
            $table->integer('price_score');

            $table->string('tags')->nullable();

            $table->foreignId('type_id')
                ->constrained('restaurant_types');

            $table->foreignId('owner_id')
                ->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
