<?php

namespace App\Http\Controllers\tx;

use DateTime;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Tx_qty_part;
use Illuminate\Http\Request;
use App\Models\Mst_brand_type;
use App\Models\Tx_nota_retur_part;
use App\Models\Tx_sales_order_part;
use App\Models\Tx_surat_jalan_part;
use App\Http\Controllers\Controller;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_receipt_order_part;
use App\Models\Tx_delivery_order_part;
use App\Models\Tx_purchase_order_part;
use App\Models\Tx_purchase_retur_part;
use App\Models\Tx_stock_assembly_part;
use App\Models\Tx_stock_transfer_part;
use Illuminate\Database\Query\Builder;
use App\Models\Tx_sales_quotation_part;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Tx_stock_disassembly_part;
use App\Models\Tx_purchase_quotation_part;

class StockMasterServerSideController extends Controller
{
    protected $title = 'Master Part';
    protected $folder = 'stock-master';
    protected $uri_folder = 'stock-master-stock-card';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$param=null)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 1800);

        $date01 = new DateTime(date('Y-m-d H:i:s'));

        $queryBranch = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        $queryBrand = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y'
        ])
        ->orderBy('string_val','ASC')
        ->get();
        $queryBrandType = Mst_brand_type::where([
            'active' => 'Y'
        ])
        ->orderBy('brand_type','ASC')
        ->get();
        $queryPartType = Mst_global::where([
            'data_cat' => 'part-type',
            'active' => 'Y'
        ])
        ->orderBy('string_val','ASC')
        ->get();

        $paramTemp = str_replace("\\","/",urldecode($param));
        $parameter = explode('::',$paramTemp);
        if(count($parameter)<6){
            return redirect(route('stockmaster.index').'/'.urlencode('::::::::::::::'));
        }
        if ($request->ajax()) {
            $sql = Tx_qty_part::leftJoin('mst_parts','tx_qty_parts.part_id','=','mst_parts.id')
            ->leftJoin('mst_globals as mg_01','mst_parts.part_type_id','=','mg_01.id')
            ->leftJoin('mst_globals as mg_02','mst_parts.quantity_type_id','=','mg_02.id')
            ->leftJoin('mst_globals as mg_03','mst_parts.brand_id','=','mg_03.id')
            ->leftJoin('mst_branches as mb','tx_qty_parts.branch_id','=','mb.id')
            ->select(
                'mst_parts.id AS part_idx',
                'mst_parts.slug',
                'mst_parts.part_number',
                'mst_parts.part_name',
                'mst_parts.final_price',
                'mst_parts.price_list',
                'mst_parts.avg_cost',
                'mst_parts.final_cost',
                'mst_parts.active AS part_active',
                'mg_01.title_ind as part_type_name',
                'tx_qty_parts.qty',
                'tx_qty_parts.branch_id AS branch_id_tmp',
                'mb.name as branch_name',
                'mg_02.string_val as unit_name',
                'mg_03.title_ind as brand_name',
                'tx_qty_parts.id as rank',
            )
            ->selectRaw('IF(LENGTH(mst_parts.part_number)<11,CONCAT(MID(mst_parts.part_number, 1, 5),"-",MID(mst_parts.part_number, 6, 5)),'.
                'CONCAT(MID(mst_parts.part_number, 1, 5),"-",MID(mst_parts.part_number, 6, 5),"-",MID(mst_parts.part_number, 11, LENGTH(mst_parts.part_number)))) AS part_number_wd')
            ->selectRaw('mst_parts.part_name as part_name_wd')
            // purchase memo
            ->addSelect(['purchase_memo_qty' => Tx_purchase_memo_part::selectRaw('IFNULL(SUM(tx_purchase_memo_parts.qty),0)')    // total qty dari memo yg aktif
                ->leftJoin('tx_purchase_memos as tx_memo','tx_purchase_memo_parts.memo_id','=','tx_memo.id')
                ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                ->whereColumn('tx_purchase_memo_parts.part_id','mst_parts.id')
                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_memo.branch_id IS null) OR tx_memo.branch_id=tx_qty_parts.branch_id)')
                ->where('tx_purchase_memo_parts.active','=','Y')
                ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                ->where('tx_memo.active','=','Y')
            ])
            // purchase order
            ->addSelect(['purchase_order_qty' => Tx_purchase_order_part::selectRaw('IFNULL(SUM(tx_purchase_order_parts.qty),0)')  // total qty dari po yg aktif
                ->leftJoin('tx_purchase_orders as tx_order','tx_purchase_order_parts.order_id','=','tx_order.id')
                ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                ->whereColumn('tx_purchase_order_parts.part_id','mst_parts.id')
                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_order.branch_id IS null) OR tx_order.branch_id=tx_qty_parts.branch_id)')
                ->where('tx_purchase_order_parts.active','=','Y')
                ->where('tx_order.approved_by','<>',null)
                ->where('tx_order.active','=','Y')
            ])
            ->addSelect(['purchase_ro_qty_mo' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO yg konek ke MO
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->whereIn('tx_receipt_order_parts.po_mo_no', function($query){
                    $query->select('tx_memo.memo_no')
                    ->from('tx_purchase_memos as tx_memo')
                    ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                    ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_memo.branch_id IS null) OR tx_memo.branch_id=tx_qty_parts.branch_id)')
                    ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                    ->where('tx_memo.active','=','Y');
                })
            ])
            ->addSelect(['purchase_ro_qty_po' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO yg konek ke PO
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->whereIn('tx_receipt_order_parts.po_mo_no', function($query){
                    $query->select('tx_order.purchase_no')
                    ->from('tx_purchase_orders as tx_order')
                    ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                    ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_order.branch_id IS null) OR tx_order.branch_id=tx_qty_parts.branch_id)')
                    ->where('tx_order.approved_by','<>',null)
                    ->where('tx_order.active','=','Y');
                })
            ])
            ->addSelect(['purchase_ro_qty_no_partial_mo' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO dg is_partial_received=N
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                ->where('tx_receipt_order_parts.is_partial_received','=','N')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->whereIn('tx_receipt_order_parts.po_mo_no', function($query){
                    $query->select('tx_memo.memo_no')
                    ->from('tx_purchase_memos as tx_memo')
                    ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                    ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_memo.branch_id IS null) OR tx_memo.branch_id=tx_qty_parts.branch_id)')
                    ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                    ->where('tx_memo.active','=','Y');
                })
            ])
            ->addSelect(['purchase_ro_qty_no_partial_po' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO dg is_partial_received=N
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                ->where('tx_receipt_order_parts.is_partial_received','=','N')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->whereIn('tx_receipt_order_parts.po_mo_no', function($query){
                    $query->select('tx_order.purchase_no')
                    ->from('tx_purchase_orders as tx_order')
                    ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                    ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_order.branch_id IS null) OR tx_order.branch_id=tx_qty_parts.branch_id)')
                    ->where('tx_order.approved_by','<>',null)
                    ->where('tx_order.active','=','Y');
                })
            ])
            ->addSelect(['purchase_ro_branch_id' => Tx_receipt_order_part::select('usr.branch_id')  // branch
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->limit(1)
            ])
            ->addSelect(['purchase_ro_final_cost' => Tx_receipt_order_part::select('tx_receipt_order_parts.final_cost')  // final cost
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                ->where('tx_receipt_order_parts.final_cost','>',0)
                ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->orderBy('tx_ro.created_at','DESC')
                ->orderBy('tx_receipt_order_parts.created_at','DESC')
                ->limit(1)
            ])
            ->addSelect(['purchase_ro_qty_no_partial_final_cost' => Tx_receipt_order_part::select('tx_receipt_order_parts.final_cost')  // final cost
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty_parts.branch_id)')
                ->where('tx_receipt_order_parts.is_partial_received','=','N')
                ->where('tx_receipt_order_parts.final_cost','>',0)
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->orderBy('tx_ro.created_at','DESC')
                ->orderBy('tx_receipt_order_parts.created_at','DESC')
                ->limit(1)
            ])
            ->addSelect(['in_transit_qty' => Tx_stock_transfer_part::selectRaw('IFNULL(SUM(tx_stock_transfer_parts.qty),0)')
                ->leftJoin('tx_stock_transfers as tx_stock','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock.id')
                ->whereColumn('tx_stock_transfer_parts.part_id','mst_parts.id')
                ->whereColumn('tx_stock.branch_to_id','tx_qty_parts.branch_id')
                ->where('tx_stock_transfer_parts.active','=','Y')
                ->where('tx_stock.approved_by','<>',null)
                ->where('tx_stock.received_by','=',null)
                ->where('tx_stock.active','=','Y')
            ])
            ->addSelect(['last_final_price' => Tx_sales_order_part::selectRaw('IFNULL(tx_sales_order_parts.price,0) AS last_final_price')
                ->leftJoin('tx_sales_orders as txso','tx_sales_order_parts.order_id','=','txso.id')
                ->leftJoin('userdetails as usr','txso.created_by','=','usr.user_id')
                ->whereColumn('tx_sales_order_parts.part_id','mst_parts.id')
                // ---
                // gunakan kode cabang user ketika cabang SO kosong
                // jika cabang SO ada maka gunakan kode cabang SO
                ->whereRaw('((usr.branch_id=tx_qty_parts.branch_id AND txso.branch_id IS null) OR txso.branch_id=tx_qty_parts.branch_id)')
                // ---
                ->where('tx_sales_order_parts.active','=','Y')
                ->where('txso.active','=','Y')
                ->orderBy('txso.created_at','DESC')
                ->limit(1)
            ])
            ->addSelect(['brand_type_name' => Mst_brand_type::select('brand_type')
                ->where('mst_brand_types.brand_id','=','mg_03.id')
            ])
            ->addSelect(['brand_type_id' => Mst_brand_type::select('id')
                ->where('mst_brand_types.brand_id','=','mg_03.id')
            ])
            ->where([
                'mst_parts.active' => 'Y',
                'mb.active' => 'Y',
            ])
            ->when($parameter[0]<>'', function($q) use($parameter) {
                $q->where('mst_parts.part_number','LIKE', $parameter[0].'%');
            })
            ->when($parameter[1]<>'', function($q) use($parameter) {
                $q->where('mst_parts.part_name','LIKE', '%'.$parameter[1].'%');
            })
            ->when($parameter[2]<>'', function($q) use($parameter) {
                $q->where('mst_parts.brand_id','=', $parameter[2]);
            })
            ->when($parameter[3]<>'', function($q) use($parameter) {
                $q->where(function (Builder $query) {
                    $query->select('id')
                    ->from('mst_brand_types')
                    ->whereColumn('mst_brand_types.brand_id', 'mg_03.id')
                    ->limit(1);
                }, $parameter[3]);
            })
            ->when($parameter[4]<>'', function($q) use($parameter) {
                $q->where('mg_01.id','=', $parameter[4]);
            })
            ->when($parameter[5]<>'', function($q) use($parameter) {
                $q->where('mb.id','=', $parameter[5]);
            })
            ->when($parameter[7]=='Y', function($q) use($parameter) {
                $q->whereRaw('tx_qty_parts.qty>0');

            })
            ->orderBy('mst_parts.part_number','ASC');

            return DataTables::of($sql)
            ->addColumn('part_number_with_delimiter', function ($sql) {
                return $sql->part_number_wd;
            })
            ->addColumn('parts_name', function ($sql) {
                return $sql->part_name_wd;
            })
            ->addColumn('SOqty', function ($sql) {
                // sales order
                $qtySO = Tx_sales_order_part::leftJoin('tx_sales_orders AS txso','tx_sales_order_parts.order_id','=','txso.id')
                ->leftJoin('userdetails AS usr','tx_sales_order_parts.created_by','=','usr.user_id')
                ->whereNotIn('txso.id',function (Builder $query) {
                    $query->select('tx_do_parts.sales_order_id')
                    ->from('tx_delivery_order_parts as tx_do_parts')
                    ->leftJoin('tx_delivery_orders as tx_do', 'tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                    ->where([
                        'tx_do_parts.active' => 'Y',
                        'tx_do.active' => 'Y',
                    ]);
                })
                ->whereRaw('txso.sales_order_no NOT LIKE \'%Draft%\'')
                ->where([
                    'tx_sales_order_parts.part_id'=>$sql->part_idx,
                    'tx_sales_order_parts.active'=>'Y',
                    'txso.need_approval'=>'N',
                    'txso.active'=>'Y',
                ])
                ->when($sql->branch_id_tmp!=null, function($q) use($sql) {
                    $q->whereRaw('((usr.branch_id='.$sql->branch_id_tmp.' AND txso.branch_id IS null) OR txso.branch_id='.$sql->branch_id_tmp.')');
                })
                ->sum('tx_sales_order_parts.qty');

                // surat jalan
                $qtySJ = Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txsj','tx_surat_jalan_parts.surat_jalan_id','=','txsj.id')
                ->leftJoin('userdetails AS usr','tx_surat_jalan_parts.created_by','=','usr.user_id')
                ->whereNotIn('txsj.id',function (Builder $query) {
                    $query->select('tx_do_parts.sales_order_id')
                    ->from('tx_delivery_order_non_tax_parts as tx_do_parts')
                    ->leftJoin('tx_delivery_order_non_taxes as tx_do', 'tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                    ->where([
                        'tx_do_parts.active' => 'Y',
                        'tx_do.active' => 'Y',
                    ]);
                })
                ->whereRaw('txsj.surat_jalan_no NOT LIKE \'%Draft%\'')
                ->where([
                    'tx_surat_jalan_parts.part_id'=>$sql->part_idx,
                    'tx_surat_jalan_parts.active'=>'Y',
                    'txsj.need_approval'=>'N',
                    'txsj.active'=>'Y',
                ])
                ->when($sql->branch_id_tmp!=null, function($q) use($sql) {
                    $q->whereRaw('((usr.branch_id='.$sql->branch_id_tmp.' AND txsj.branch_id IS null) OR txsj.branch_id='.$sql->branch_id_tmp.')');
                })
                ->sum('tx_surat_jalan_parts.qty');

                if(($qtySJ+$qtySO)>0){
                    return '<a href="#" onclick="dispSalesOrderInfo('.$sql->part_idx.','.$sql->branch_id_tmp.');">'.($qtySO+$qtySJ).'</a>';
                }else{
                    return ($qtySJ+$qtySO);
                }
            })
            ->addColumn('OOqty', function ($sql) {
                // on order
                $oo = ((is_null($sql->purchase_memo_qty)?0:
                    $sql->purchase_memo_qty)+$sql->purchase_order_qty)-($sql->purchase_ro_qty_mo+$sql->purchase_ro_qty_po+$sql->purchase_ro_qty_no_partial_mo+$sql->purchase_ro_qty_no_partial_po);
                if($oo>0){
                    return '<a href="#" onclick="dispOnOrderInfo('.$sql->part_idx.','.$sql->branch_id_tmp.');">'.$oo.'</a>';
                }else{
                    return $oo;
                }
            })
            ->addColumn('ITqty', function ($sql) {
                if($sql->in_transit_qty>0){
                    return '<a href="#" onclick="dispInTransitInfo('.$sql->part_idx.','.$sql->branch_id_tmp.');">'.$sql->in_transit_qty.'</a>';
                }else{
                    return $sql->in_transit_qty;
                }
            })
            ->addColumn('final_cost_val', function ($sql) {
                if($sql->purchase_ro_final_cost>0){
                    return ($sql->purchase_ro_final_cost>0?$sql->purchase_ro_final_cost:0);
                }else{
                    return ($sql->purchase_ro_qty_no_partial_final_cost>0?$sql->purchase_ro_qty_no_partial_final_cost:0);
                }
            })
            ->addColumn('last_final_price_val', function ($sql) {
                return ($sql->last_final_price>0?$sql->last_final_price:0);
            })
            ->addColumn('action', function ($sql) {
                $txt = '<a style="text-decoration: underline;" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master-part/'.urlencode($sql->slug)).'?br_id='.$sql->branch_id_tmp.'">View</a> |
                    <a style="text-decoration: underline;" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master-part/'.urlencode($sql->slug).'/edit').'">Edit</a> |
                    <a style="text-decoration: underline;" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master-stock-card/'.$sql->part_idx).'">Stock Card</a>'.
                    '<input type="hidden" name="title_caption'.$sql->rank.'" id="title_caption'.$sql->rank.'" value="'.$sql->part_name.'">'.
                    '<input type="hidden" name="part_id'.$sql->rank.'" id="part_id'.$sql->rank.'" value="'.$sql->part_idx.'">';
                return $txt;
            })
            ->addColumn('del_checkbox', function ($sql) {
                $isTx = false;
                $tx01 = Tx_delivery_order_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx01){$isTx = true;}

                $tx02 = Tx_nota_retur_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx02 && !$isTx){$isTx = true;}

                $tx03 = Tx_purchase_memo_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx03 && !$isTx){$isTx = true;}

                $tx04 = Tx_purchase_order_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx04 && !$isTx){$isTx = true;}

                $tx05 = Tx_purchase_quotation_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx05 && !$isTx){$isTx = true;}

                $tx06 = Tx_purchase_retur_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx06 && !$isTx){$isTx = true;}

                $tx07 = Tx_receipt_order_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx07 && !$isTx){$isTx = true;}

                $tx08 = Tx_sales_order_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx08 && !$isTx){$isTx = true;}

                $tx09 = Tx_sales_quotation_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx09 && !$isTx){$isTx = true;}

                $tx10 = Tx_stock_assembly_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx10 && !$isTx){$isTx = true;}

                $tx11 = Tx_stock_disassembly_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx11 && !$isTx){$isTx = true;}

                $tx12 = Tx_stock_transfer_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx12 && !$isTx){$isTx = true;}

                $tx13 = Tx_surat_jalan_part::where([
                    'part_id' => $sql->part_idx,
                ])
                ->first();
                if($tx13 && !$isTx){$isTx = true;}
                
                if($sql->part_active=='Y' && !$isTx){
                    return '<input type="checkbox" name="delRow'.$sql->rank.'" id="delRow'.$sql->rank.'">';
                }else{
                    return '<input type="hidden" name="delRow'.$sql->rank.'" id="delRow'.$sql->rank.'">';
                }
            })
            ->rawColumns(['part_number_with_delimiter','parts_name','SOqty','OOqty','ITqty','last_final_price_val','price_list_val','action','del_checkbox'])
            ->toJson();
        }

        $date02 = new DateTime(date('Y-m-d H:i:s'));
        $interval = $date02->diff($date01);
        // dd('selisih waktu generate unique code '.$interval->format('%H:%i:%s'));

        $data = [
            'stocks' => [],
            'rowCount' => Tx_qty_part::count(),
            'title' => $this->title,
            'folder' => $this->folder,
            'uri_folder' => $this->uri_folder,
            'queryBranch' => $queryBranch,
            'queryBrand' => $queryBrand,
            // 'queryBrandType' => $queryBrandType,
            'queryPartType' => $queryPartType,
            'param' => $param,
            'parameter' => $parameter,
            'date01' => $date01,
        ];

        return view('tx.'.$this->folder.'.index-stock-master-serverside', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $part_no = '';
        if($request->part_no!=''){
            $part_no = $request->part_no;
        }

        $part_name = '';
        if($request->part_name!=''){
            $part_name = $request->part_name;
        }

        $brand_id = '';
        if($request->brand_id!=''){
            $brand_id = $request->brand_id;
        }

        $brandtype_id = '';
        if($request->brandtype_id!=''){
            $brandtype_id = $request->brandtype_id;
        }

        $partType_id = '';
        if($request->partType_id!=''){
            $partType_id = $request->partType_id;
        }

        $branch_id = '';
        if($request->branch_id!=''){
            $branch_id = $request->branch_id;
        }

        $qRstring = $part_no.'::'
            .$part_name.'::'
            .$brand_id.'::'
            .$brandtype_id.'::'
            .$partType_id.'::'
            .$branch_id.'::'
            .($request->showCost=='on'?'Y':'N').'::'
            .($request->showOhGreaterThanZero=='on'?'Y':'N');
        $qRstring = str_replace("/","\\",$qRstring);
        return redirect(route('stockmaster.index').'/'.urlencode($qRstring));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
