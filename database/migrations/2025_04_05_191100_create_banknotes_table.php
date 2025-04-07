<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banknotes', function (Blueprint $table) {
            $table->id();
            $table->integer('denomination');
            $table->string('currency', 3)->default('AZN');
            $table->unsignedInteger('count')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['denomination', 'currency']);
            $table->index('denomination'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banknotes');
    }
};
