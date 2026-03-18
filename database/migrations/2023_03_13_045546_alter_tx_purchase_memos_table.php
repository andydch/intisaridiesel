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
        Schema::table('tx_purchase_memos', function (Blueprint $table) {
            $table->char('is_received')->nullable()->default('N')->after('remark')->comment('jika Y maka part sudah diterima');
            $table->dateTime('received_at')->nullable()->after('is_received')->comment('tanggal/jam part diterima');
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
