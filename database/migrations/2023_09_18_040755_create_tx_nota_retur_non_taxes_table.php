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
        Schema::create('tx_nota_retur_non_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('nota_retur_no',15);
            $table->date('nota_retur_date');
            $table->unsignedBigInteger('delivery_order_id')->comment('FK ke tx_delivery_order_non_taxes');
            $table->unsignedBigInteger('customer_id')->comment('FK ke mst_customers');
            $table->unsignedBigInteger('customer_entity_type_id')->nullable()->comment('FK ke mst_globals');
            $table->string('customer_name',255);
            $table->unsignedBigInteger('branch_id')->comment('FK ke mst_branches');
            $table->text('remark')->nullable();
            $table->integer('total_qty')->default(0);
            $table->decimal('total_before_vat',20,2)->default(0);
            $table->decimal('total_after_vat',20,2)->default(0);
            $table->char('is_draft',1)->nullable();
            $table->dateTime('draft_at')->nullable();
            $table->dateTime('draft_to_created_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->comment('FK ke users');
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('canceled_by')->nullable()->comment('FK ke users');
            $table->dateTime('canceled_at')->nullable();
            $table->char('active',1)->default('Y');
            $table->unsignedBigInteger('created_by')->comment('FK ke users');
            $table->unsignedBigInteger('updated_by')->comment('FK ke users');
            $table->timestamps();

            $table->foreign('delivery_order_id')->references('id')->on('tx_delivery_order_non_taxes');
            $table->foreign('customer_id')->references('id')->on('mst_customers');
            $table->foreign('customer_entity_type_id')->references('id')->on('mst_globals');
            $table->foreign('branch_id')->references('id')->on('mst_branches');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('canceled_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_nota_retur_non_taxes');
    }
};
