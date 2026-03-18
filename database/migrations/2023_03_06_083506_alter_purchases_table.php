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
        Schema::table('tx_purchase_quotations', function (Blueprint $table) {
            $table->dropForeign('tx_purchase_quotations_company_id_foreign');
            $table->dropForeign('tx_purchase_quotations_revised_by_foreign');

            $table->dropColumn('company_id');
            $table->dropColumn('company_address');
            $table->dropColumn('revised_by');
            $table->dropColumn('revised_at');

            $table->dateTime('draft_at')->nullable()->after('is_draft');
            $table->dateTime('draft_to_created_at')->nullable()->after('draft_at');
        });

        Schema::table('tx_purchase_memos', function (Blueprint $table) {
            $table->dateTime('draft_at')->nullable()->change();
            $table->dateTime('draft_to_created_at')->nullable()->change();
        });

        Schema::table('tx_purchase_orders', function (Blueprint $table) {
            $table->dateTime('draft_at')->nullable()->change();
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
