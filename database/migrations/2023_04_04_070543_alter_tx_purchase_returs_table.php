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
        Schema::table('tx_purchase_returs', function (Blueprint $table) {
            $table->string('invoice_no',255)->after('supplier_name');
            $table->unsignedBigInteger('approved_by')->nullable()->after('total_after_vat');
            $table->dateTime('approved_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('canceled_by')->nullable()->after('approved_at');
            $table->dateTime('canceled_at')->nullable()->after('canceled_by');
            $table->dateTime('draft_at')->nullable()->after('canceled_at');
            $table->dateTime('draft_to_created_at')->nullable()->after('draft_at');
            $table->char('is_draft',1)->nullable()->after('draft_to_created_at');

            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('canceled_by')->references('id')->on('users');
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
