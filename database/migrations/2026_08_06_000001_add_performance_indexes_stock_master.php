<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index pendukung kinerja halaman Stock Master (Server Side DataTables).
 *
 * Latar belakang:
 * - Query utama memakai ~13 correlated subquery per baris (has_tx, SOqty,
 *   OOqty, ITqty, final_cost_val, last_final_price_val, dll).
 * - Setiap subquery memfilter `WHERE part_id = ? AND active = 'Y'`.
 * - Tanpa index (part_id, active), MySQL melakukan FULL TABLE SCAN per subquery
 *   => halaman & pencarian Yajra sangat lambat (hingga ~5 detik saat search).
 *
 * Catatan: kolom yang di-index sudah pasti ada (dipakai oleh query yang berjalan).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ===== Tabel detail transaksi: index lookup untuk subquery per part =====
        // (part_id, active) => subquery `WHERE part_id = ? AND active = 'Y'`
        Schema::table('tx_sales_order_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
            $table->index('order_id');
        });
        Schema::table('tx_surat_jalan_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
            $table->index('surat_jalan_id');
        });
        Schema::table('tx_purchase_memo_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
            $table->index('memo_id');
        });
        Schema::table('tx_purchase_order_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
            $table->index('order_id');
        });
        Schema::table('tx_purchase_quotation_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
        });
        Schema::table('tx_purchase_retur_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
        });
        Schema::table('tx_receipt_order_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
            $table->index('receipt_order_id');
        });
        Schema::table('tx_sales_quotation_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
        });
        Schema::table('tx_stock_assembly_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
        });
        Schema::table('tx_stock_disassembly_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
        });
        Schema::table('tx_stock_transfer_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
            $table->index('stock_transfer_id');
        });
        Schema::table('tx_nota_retur_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
        });
        Schema::table('tx_delivery_order_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
            $table->index('sales_order_id'); // dipakai di NOT EXISTS (SOqty)
        });
        Schema::table('tx_delivery_order_non_tax_parts', function (Blueprint $table) {
            $table->index(['part_id', 'active']);
            $table->index('sales_order_id'); // dipakai di NOT EXISTS (SOqty/Surat Jalan)
        });

        // ===== Tabel header: filter `branch_id` / `branch_to_id` di subquery =====
        Schema::table('tx_sales_orders', function (Blueprint $table) {
            $table->index('branch_id');
        });
        Schema::table('tx_surat_jalans', function (Blueprint $table) {
            $table->index('branch_id');
        });
        Schema::table('tx_purchase_memos', function (Blueprint $table) {
            $table->index('branch_id');
        });
        Schema::table('tx_purchase_orders', function (Blueprint $table) {
            $table->index('branch_id');
        });
        Schema::table('tx_receipt_orders', function (Blueprint $table) {
            $table->index('branch_id');
        });
        Schema::table('tx_stock_transfers', function (Blueprint $table) {
            $table->index('branch_to_id');
        });

        // ===== Tabel dasar =====
        Schema::table('tx_qty_parts', function (Blueprint $table) {
            $table->index(['part_id', 'branch_id']);
        });
        Schema::table('mst_parts', function (Blueprint $table) {
            $table->index(['active', 'part_number']);
        });
        Schema::table('mst_globals', function (Blueprint $table) {
            $table->index(['data_cat', 'active']);
        });
        Schema::table('mst_branches', function (Blueprint $table) {
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tx_sales_order_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
            $table->dropIndex(['order_id']);
        });
        Schema::table('tx_surat_jalan_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
            $table->dropIndex(['surat_jalan_id']);
        });
        Schema::table('tx_purchase_memo_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
            $table->dropIndex(['memo_id']);
        });
        Schema::table('tx_purchase_order_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
            $table->dropIndex(['order_id']);
        });
        Schema::table('tx_purchase_quotation_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
        });
        Schema::table('tx_purchase_retur_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
        });
        Schema::table('tx_receipt_order_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
            $table->dropIndex(['receipt_order_id']);
        });
        Schema::table('tx_sales_quotation_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
        });
        Schema::table('tx_stock_assembly_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
        });
        Schema::table('tx_stock_disassembly_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
        });
        Schema::table('tx_stock_transfer_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
            $table->dropIndex(['stock_transfer_id']);
        });
        Schema::table('tx_nota_retur_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
        });
        Schema::table('tx_delivery_order_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
            $table->dropIndex(['sales_order_id']);
        });
        Schema::table('tx_delivery_order_non_tax_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'active']);
            $table->dropIndex(['sales_order_id']);
        });

        Schema::table('tx_sales_orders', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
        });
        Schema::table('tx_surat_jalans', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
        });
        Schema::table('tx_purchase_memos', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
        });
        Schema::table('tx_purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
        });
        Schema::table('tx_receipt_orders', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
        });
        Schema::table('tx_stock_transfers', function (Blueprint $table) {
            $table->dropIndex(['branch_to_id']);
        });

        Schema::table('tx_qty_parts', function (Blueprint $table) {
            $table->dropIndex(['part_id', 'branch_id']);
        });
        Schema::table('mst_parts', function (Blueprint $table) {
            $table->dropIndex(['active', 'part_number']);
        });
        Schema::table('mst_globals', function (Blueprint $table) {
            $table->dropIndex(['data_cat', 'active']);
        });
        Schema::table('mst_branches', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};
