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
        Schema::create('tx_part_tmp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('part_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->integer('qty')->nullable();
            $table->decimal('avg_cost',20,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_part_tmp');
    }
};
