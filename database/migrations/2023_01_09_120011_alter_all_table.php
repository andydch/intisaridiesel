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
        Schema::table('mst_countries', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('mst_globals', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('mst_menus', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('mst_menu_users', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('mst_provinces', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('mst_sub_districts', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('mst_urban_villages', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('tx_gudangs', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('tx_kurirs', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('tx_parts', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('tx_pelanggans', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('tx_salesmans', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('tx_suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('log_tx_gudangs', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('log_tx_kurirs', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('log_tx_parts', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('log_tx_pelanggans', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('log_tx_salesmans', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('log_tx_suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->change();
            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
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
