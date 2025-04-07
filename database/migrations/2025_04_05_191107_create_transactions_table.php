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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->unsignedTinyInteger('type');
            $table->unsignedTinyInteger('status');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->json('dispensed_notes')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('transaction_time')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->index('account_id');
            $table->index('type');
            $table->index('status');
            $table->index('transaction_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
