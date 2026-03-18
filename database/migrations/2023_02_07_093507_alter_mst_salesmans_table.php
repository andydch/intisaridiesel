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
        Schema::table('mst_salesmans', function (Blueprint $table) {
            $table->date('join_date')->nullable()->comment('tanggal mulai bekerja')->after('mobilephone');
            $table->date('birth_date')->nullable()->after('join_date');
            $table->decimal('sales_target', 20, 2)->default(0)->nullable()->after('birth_date');
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
