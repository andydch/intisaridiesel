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
        Schema::table('tx_acceptance_plan_per_invoices', function (Blueprint $table) {
            $table->string('payment_receipt_no', 15)->nullable()->after('invoice_no');
            $table->date('payment_date')->nullable()->after('payment_receipt_no');
            $table->decimal('payment_total', 20, 2)->nullable()->after('payment_date')->comment('Total Payment setelah PPN jika ada');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tx_acceptance_plan_per_invoices', function (Blueprint $table) {
            $table->dropColumn('payment_date');
            $table->dropColumn('payment_receipt_no');
            $table->dropColumn('payment_total');
        });
    }
};
