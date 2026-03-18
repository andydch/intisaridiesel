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
        Schema::create('mst_parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_number', 255);
            $table->string('part_name', 255);
            $table->string('slug', 260);
            $table->unsignedBigInteger('part_type_id')->comment('FK ke master global - part type');
            $table->unsignedBigInteger('part_category_id')->comment('FK ke master global - part category');
            $table->unsignedBigInteger('brand_id')->comment('FK ke master global - brand');
            // $table->unsignedBigInteger('supplier_id')->comment('FK ke master supplier');
            $table->integer('weight')->default(0)->nullable();
            $table->unsignedBigInteger('weight_id')->comment('FK ke master global - brand');
            $table->unsignedBigInteger('quantity_type_id')->comment('FK ke master global - quantity type');
            $table->integer('safety_stock')->default(0)->nullable();
            $table->decimal('price_list', 20, 2)->default(0)->nullable();
            $table->decimal('avg_cost', 20, 2)->default(0)->nullable();
            $table->decimal('initial_cost', 20, 2)->default(0)->nullable();
            $table->decimal('final_cost', 20, 2)->default(0)->nullable();
            $table->integer('total_sales_qty')->default(0)->nullable();
            $table->integer('total_sales')->default(0)->nullable();
            $table->char('active', 1)->default('Y');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->foreign('part_type_id')->references('id')->on('mst_globals');
            $table->foreign('part_category_id')->references('id')->on('mst_globals');
            $table->foreign('brand_id')->references('id')->on('mst_globals');
            // $table->foreign('supplier_id')->references('id')->on('mst_suppliers');
            $table->foreign('weight_id')->references('id')->on('mst_globals');
            $table->foreign('quantity_type_id')->references('id')->on('mst_globals');
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
        Schema::dropIfExists('mst_parts');
    }
};
