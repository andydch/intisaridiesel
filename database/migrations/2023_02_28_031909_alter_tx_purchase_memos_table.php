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
        Schema::table('mst_parts', function (Blueprint $table) {
            $table->dropColumn('is_draft');
        });

        Schema::table('tx_purchase_memos', function (Blueprint $table) {
            $table->char('is_draft', 1)->default('Y')->nullable()->after('cancel_at');
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
