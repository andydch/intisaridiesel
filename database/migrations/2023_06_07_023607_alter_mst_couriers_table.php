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
        Schema::table('mst_couriers', function (Blueprint $table) {
            $table->string('post_code',6)->nullable()->change();
            $table->string('courier_email',64)->nullable()->change();
            $table->string('phone1',32)->nullable()->change();
            $table->string('pic1_phone',32)->nullable()->change();
            $table->string('pic1_email',64)->nullable()->change();
        });

        Schema::table('mst_courier_bank_information', function (Blueprint $table) {
            $table->string('swift_code',255)->nullable()->change();
            $table->string('bsb_code',255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
