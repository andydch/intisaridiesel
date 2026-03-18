<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tx_payment_plans', function (Blueprint $table) {
            $table->char('is_draft',1)->nullable()->after('beginning_balance');
            $table->dateTime('draft_at')->nullable()->after('is_draft');
            $table->dateTime('draft_to_created_at')->nullable()->after('draft_at');
        });

        Schema::table('log_tx_payment_plans', function (Blueprint $table) {
            $table->char('is_draft',1)->nullable()->after('beginning_balance');
            $table->dateTime('draft_at')->nullable()->after('is_draft');
            $table->dateTime('draft_to_created_at')->nullable()->after('draft_at');
        });

        DB::unprepared('DROP TRIGGER IF EXISTS after_tx_payment_plans_update');
        DB::unprepared('DROP TRIGGER IF EXISTS after_tx_payment_plans_delete');

        DB::unprepared('
            CREATE TRIGGER after_tx_payment_plans_update AFTER UPDATE ON tx_payment_plans FOR EACH ROW
                BEGIN
                    INSERT INTO log_tx_payment_plans(id_ori,payment_month,beginning_balance,is_draft,draft_at,draft_to_created_at,
                        active,created_at,updated_at,created_by,updated_by,action)
                    VALUES(old.id,old.payment_month,old.beginning_balance,old.is_draft,old.draft_at,old.draft_to_created_at,
                        old.active,old.created_at,old.updated_at,old.created_by,old.updated_by,\'U\');
                END
            ');

        DB::unprepared('
            CREATE TRIGGER after_tx_payment_plans_delete AFTER DELETE ON tx_payment_plans FOR EACH ROW
                BEGIN
                    INSERT INTO log_tx_payment_plans(id_ori,payment_month,beginning_balance,is_draft,draft_at,draft_to_created_at,
                        active,created_at,updated_at,created_by,updated_by,action)
                    VALUES(old.id,old.payment_month,old.beginning_balance,old.is_draft,old.draft_at,old.draft_to_created_at,
                        old.active,old.created_at,old.updated_at,old.created_by,old.updated_by,\'D\');
                END
            ');
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
