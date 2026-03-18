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
        Schema::create('tx_payment_plans', function (Blueprint $table) {
            $table->id();
            $table->date('payment_month')->comment('bulan pembayaran');
            $table->decimal('beginning_balance',20,2)->comment('saldo awal');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('tx_payment_plan_per_rc_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_plan_id')->comment('FK ke tx_payment_plans');
            $table->date('plan_date')->comment('tanggal rencana bayar');
            $table->decimal('plan_pay',20,2)->comment('rencana yang mau dibayar');
            $table->unsignedBigInteger('receipt_order_id')->comment('FK ke tx_receipt_orders');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('payment_plan_id')->references('id')->on('tx_payment_plans');
            $table->foreign('receipt_order_id')->references('id')->on('tx_receipt_orders');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('log_tx_payment_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_ori');
            $table->date('payment_month')->comment('bulan pembayaran');
            $table->decimal('beginning_balance',20,2)->comment('saldo awal');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->char('action', 1);
            $table->timestamps();
        });

        Schema::create('log_tx_payment_plan_per_rc_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_ori');
            $table->unsignedBigInteger('payment_plan_id')->comment('FK ke tx_payment_plans');
            $table->date('plan_date')->comment('tanggal rencana bayar');
            $table->decimal('plan_pay',20,2)->comment('rencana yang mau dibayar');
            $table->unsignedBigInteger('receipt_order_id')->comment('FK ke tx_receipt_orders');
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->char('action', 1);
            $table->timestamps();
        });

        DB::unprepared('
        CREATE TRIGGER after_tx_payment_plans_update AFTER UPDATE ON tx_payment_plans FOR EACH ROW
            BEGIN
                INSERT INTO log_tx_payment_plans(id_ori,payment_month,beginning_balance,active,created_at,updated_at,created_by,updated_by,action)
                VALUES(old.id,old.payment_month,old.beginning_balance,old.active,old.created_at,old.updated_at,old.created_by,old.updated_by,\'U\');
            END
        ');

        DB::unprepared('
        CREATE TRIGGER after_tx_payment_plans_delete AFTER DELETE ON tx_payment_plans FOR EACH ROW
            BEGIN
                INSERT INTO log_tx_payment_plans(id_ori,payment_month,beginning_balance,active,created_at,updated_at,created_by,updated_by,action)
                VALUES(old.id,old.payment_month,old.beginning_balance,old.active,old.created_at,old.updated_at,old.created_by,old.updated_by,\'D\');
            END
        ');

        DB::unprepared('
        CREATE TRIGGER after_tx_payment_plan_per_ros_update AFTER UPDATE ON tx_payment_plan_per_rc_orders FOR EACH ROW
            BEGIN
                INSERT INTO log_tx_payment_plan_per_rc_orders(id_ori,payment_plan_id,plan_date,plan_pay,receipt_order_id,active,created_at,updated_at,created_by,updated_by,action)
                VALUES(old.id,old.payment_plan_id,old.plan_date,old.plan_pay,old.receipt_order_id,old.active,old.created_at,old.updated_at,old.created_by,old.updated_by,\'U\');
            END
        ');

        DB::unprepared('
        CREATE TRIGGER after_tx_payment_plan_per_ros_delete AFTER DELETE ON tx_payment_plan_per_rc_orders FOR EACH ROW
            BEGIN
                INSERT INTO log_tx_payment_plan_per_rc_orders(id_ori,payment_plan_id,plan_date,plan_pay,receipt_order_id,active,created_at,updated_at,created_by,updated_by,action)
                VALUES(old.id,old.payment_plan_id,old.plan_date,old.plan_pay,old.receipt_order_id,old.active,old.created_at,old.updated_at,old.created_by,old.updated_by,\'D\');
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
        DB::unprepared('DROP TRIGGER IF EXISTS after_tx_payment_plans_update');
        DB::unprepared('DROP TRIGGER IF EXISTS after_tx_payment_plans_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS after_tx_payment_plan_per_ros_update');
        DB::unprepared('DROP TRIGGER IF EXISTS after_tx_payment_plan_per_ros_delete');

        Schema::dropIfExists('tx_payment_plans');
        Schema::dropIfExists('tx_payment_plan_per_rc_orders');
        Schema::dropIfExists('log_tx_payment_plans');
        Schema::dropIfExists('log_tx_payment_plan_per_rc_orders');

    }
};
