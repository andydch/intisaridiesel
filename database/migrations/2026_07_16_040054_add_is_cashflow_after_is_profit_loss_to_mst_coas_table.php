<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('mst_coas', function (Blueprint $table) {
            $table->char('is_cashflow', 1)->default('N')->after('is_profit_loss')->comment('penanda coa ini digunakan utk report cashflow');
        });

        Schema::table('log_mst_coas', function (Blueprint $table) {
            $table->char('is_cashflow', 1)->default('N')->after('is_profit_loss')->nullable()->comment('penanda coa ini digunakan utk report cashflow');
        });

        DB::unprepared("DROP TRIGGER IF EXISTS trg_mst_coas_update");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_mst_coas_delete");

        // update
        DB::unprepared("
            CREATE TRIGGER trg_mst_coas_update
            BEFORE UPDATE ON mst_coas
            FOR EACH ROW
            BEGIN
                INSERT INTO log_mst_coas
                    SET action = 'U',
                    old_id=old.id, 
                    `coa_level`=old.`coa_level`,
                    `coa_code`=old.`coa_code`,
                    `coa_code_complete`=old.`coa_code_complete`,
                    `coa_name`=old.`coa_name`,
                    `coa_parent`=old.`coa_parent`,
                    `branch_id`=old.`branch_id`,
                    `is_master_coa`=old.`is_master_coa`,
                    `is_balance_sheet`=old.`is_balance_sheet`,
                    `is_profit_loss`=old.`is_profit_loss`,
                    `is_cashflow`=old.`is_cashflow`,
                    `local`=old.`local`,
                    `beginning_balance_date`=old.`beginning_balance_date`,
                    `beginning_balance_amount`=old.`beginning_balance_amount`,
                    `is_draft`=old.`is_draft`,
                    `draft_at`=old.`draft_at`,
                    `draft_to_created_at`=old.`draft_to_created_at`,
                    `active`=old.`active`,
                    created_by=old.created_by, 
                    updated_by=old.updated_by, 
                    created_at=old.created_at, 
                    updated_at=old.updated_at;
            END
        ");

        // delete
        DB::unprepared("
            CREATE TRIGGER trg_mst_coas_delete
            BEFORE DELETE ON mst_coas
            FOR EACH ROW
            BEGIN
                INSERT INTO log_mst_coas
                    SET action = 'D',
                    old_id=old.id, 
                    `coa_level`=old.`coa_level`,
                    `coa_code`=old.`coa_code`,
                    `coa_code_complete`=old.`coa_code_complete`,
                    `coa_name`=old.`coa_name`,
                    `coa_parent`=old.`coa_parent`,
                    `branch_id`=old.`branch_id`,
                    `is_master_coa`=old.`is_master_coa`,
                    `is_balance_sheet`=old.`is_balance_sheet`,
                    `is_profit_loss`=old.`is_profit_loss`,
                    `is_cashflow`=old.`is_cashflow`,
                    `local`=old.`local`,
                    `beginning_balance_date`=old.`beginning_balance_date`,
                    `beginning_balance_amount`=old.`beginning_balance_amount`,
                    `is_draft`=old.`is_draft`,
                    `draft_at`=old.`draft_at`,
                    `draft_to_created_at`=old.`draft_to_created_at`,
                    `active`=old.`active`,
                    created_by=old.created_by, 
                    updated_by=old.updated_by, 
                    created_at=old.created_at, 
                    updated_at=old.updated_at;
            END
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_coas', function (Blueprint $table) {
            $table->dropColumn(['is_cashflow']);
        });

        Schema::table('log_mst_coas', function (Blueprint $table) {
            $table->dropColumn(['is_cashflow']);
        });

        DB::unprepared("DROP TRIGGER IF EXISTS trg_mst_coas_update");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_mst_coas_delete");

        // update
        DB::unprepared("
            CREATE TRIGGER trg_mst_coas_update
            BEFORE UPDATE ON mst_coas
            FOR EACH ROW
            BEGIN
                INSERT INTO log_mst_coas
                    SET action = 'U',
                    old_id=old.id, 
                    `coa_level`=old.`coa_level`,
                    `coa_code`=old.`coa_code`,
                    `coa_code_complete`=old.`coa_code_complete`,
                    `coa_name`=old.`coa_name`,
                    `coa_parent`=old.`coa_parent`,
                    `branch_id`=old.`branch_id`,
                    `is_master_coa`=old.`is_master_coa`,
                    `is_balance_sheet`=old.`is_balance_sheet`,
                    `is_profit_loss`=old.`is_profit_loss`,
                    `local`=old.`local`,
                    `beginning_balance_date`=old.`beginning_balance_date`,
                    `beginning_balance_amount`=old.`beginning_balance_amount`,
                    `is_draft`=old.`is_draft`,
                    `draft_at`=old.`draft_at`,
                    `draft_to_created_at`=old.`draft_to_created_at`,
                    `active`=old.`active`,
                    created_by=old.created_by, 
                    updated_by=old.updated_by, 
                    created_at=old.created_at, 
                    updated_at=old.updated_at;
            END
        ");

        // delete
        DB::unprepared("
            CREATE TRIGGER trg_mst_coas_delete
            BEFORE DELETE ON mst_coas
            FOR EACH ROW
            BEGIN
                INSERT INTO log_mst_coas
                    SET action = 'D',
                    old_id=old.id, 
                    `coa_level`=old.`coa_level`,
                    `coa_code`=old.`coa_code`,
                    `coa_code_complete`=old.`coa_code_complete`,
                    `coa_name`=old.`coa_name`,
                    `coa_parent`=old.`coa_parent`,
                    `branch_id`=old.`branch_id`,
                    `is_master_coa`=old.`is_master_coa`,
                    `is_balance_sheet`=old.`is_balance_sheet`,
                    `is_profit_loss`=old.`is_profit_loss`,
                    `local`=old.`local`,
                    `beginning_balance_date`=old.`beginning_balance_date`,
                    `beginning_balance_amount`=old.`beginning_balance_amount`,
                    `is_draft`=old.`is_draft`,
                    `draft_at`=old.`draft_at`,
                    `draft_to_created_at`=old.`draft_to_created_at`,
                    `active`=old.`active`,
                    created_by=old.created_by, 
                    updated_by=old.updated_by, 
                    created_at=old.created_at, 
                    updated_at=old.updated_at;
            END
        ");
    }
};
