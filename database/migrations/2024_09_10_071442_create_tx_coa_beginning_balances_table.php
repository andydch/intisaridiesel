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
        Schema::create('tx_coa_beginning_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coa_id')->comment('FK ke master coa');
            $table->unsignedBigInteger('branch_id')->comment('FK ke master branch');
            $table->decimal('beginning_balance',20,2)->default(0);
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_coa_beginning_balances');
    }
};
