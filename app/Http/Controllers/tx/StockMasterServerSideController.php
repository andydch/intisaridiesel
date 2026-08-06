<?php

namespace App\Http\Controllers\tx;

use App\Http\Controllers\Controller;
use App\Models\Mst_branch;
use App\Models\Mst_brand_type;
use App\Models\Mst_global;
use App\Models\Tx_qty_part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

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
        $queryBranch = Mst_branch::select('id', 'name')
        ->where('active', '=','Y')
        ->orderBy('name','ASC')
        ->get();
        $queryBrand = Cache::remember('stock_master_brand', 3600, function () {
            return Mst_global::select('id', 'title_ind', 'string_val')
            ->where('data_cat', 'brand')
            ->where('active', 'Y')
            ->orderBy('string_val','ASC')
            ->get();
        });
        $queryBrandType = Mst_brand_type::select('id', 'brand_type')
        ->where('active', 'Y')
        ->orderBy('brand_type','ASC')
        ->get();
        $queryPartType = Cache::remember('stock_master_part_type', 3600, function () {
            return Mst_global::select('id', 'title_ind', 'string_val')
            ->where('data_cat', 'part-type')
            ->where('active', 'Y')
            ->orderBy('string_val','ASC')
            ->get();
        });

        $paramTemp = str_replace("\\", "/", urldecode($param));
        $parameter = explode('::', $paramTemp);
        if(count($parameter)<6){
            return redirect(route('stockmaster.index').'/'.urlencode('::::::::::::::'));
        }
        if ($request->ajax()) {
            $base = DB::table('tx_qty_parts')
            ->join('mst_parts AS mst_parts', function($join) {
                $join->on('tx_qty_parts.part_id', '=', 'mst_parts.id')
                ->where('mst_parts.active', 'Y');
            })
            ->leftJoin('mst_globals AS mg_01', function($join) {
                $join->on('mst_parts.part_type_id', '=', 'mg_01.id')
                ->where('mg_01.active', 'Y')
                ->where('mg_01.data_cat', 'part-type');
            })
            ->leftJoin('mst_globals AS mg_02', function($join) {
                $join->on('mst_parts.quantity_type_id', '=', 'mg_02.id')
                ->where('mg_02.active', 'Y')
                ->where('mg_02.data_cat', 'quantity-type');
            })
            ->leftJoin('mst_globals AS mg_03', function($join) {
                $join->on('mst_parts.brand_id', '=', 'mg_03.id')
                ->where('mg_03.active', 'Y')
                ->where('mg_03.data_cat', 'brand');
            })
            // ---- Filter parameter dipindah ke query dasar (base) agar
            //      query COUNT yang ringan ikut terfilter sama ----
            ->when($parameter[0]<>'', function($q) use($parameter) {
                $q->where('mst_parts.part_number', 'LIKE', '%'.$parameter[0].'%');
            })
            ->when($parameter[1]<>'', function($q) use($parameter) {
                $q->where('mst_parts.part_name', 'LIKE', '%'.$parameter[1].'%');
            })
            ->when($parameter[2]<>'', function($q) use($parameter) {
                $q->where('mst_parts.brand_id', '=', $parameter[2]);
            })
            ->when($parameter[3]<>'', function($q) use($parameter) {
                // Fix: filter brand type via tabel pivot mst_part_brand_types
                $q->whereExists(function ($q1) use ($parameter) {
                    $q1->selectRaw(1)
                    ->from('mst_part_brand_types')
                    ->whereColumn('mst_part_brand_types.part_id', 'mst_parts.id')
                    ->where('mst_part_brand_types.brand_type_id', '=', $parameter[3])
                    ->where('mst_part_brand_types.active', 'Y');
                });
            })
            ->when($parameter[4]<>'', function($q) use($parameter) {
                $q->where('mg_01.id', '=', $parameter[4]);
            })
            ->when($parameter[5]<>'', function($q) use($parameter) {
                $q->where('tx_qty_parts.branch_id', '=', $parameter[5]);
            })
            ->when($parameter[7]=='Y', function($q) use($parameter) {
                $q->whereRaw('tx_qty_parts.qty>0');
            })
            // ---- Kolom dasar dipindah ke $base (query INNER yang ringan).
            //      Subquery berat dipindah ke OUTER agar hanya dieksekusi
            //      untuk baris yang sudah di-paginate (≤length). ----
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
                'tx_qty_parts.qty',
                'tx_qty_parts.branch_id AS branch_id_tmp',
                'tx_qty_parts.id AS rank',
                'mg_01.title_ind AS part_type_name',
                'mg_02.title_ind AS unit_name',
                'mg_03.title_ind AS brand_name',
            )
            ->selectRaw('mst_parts.part_name AS part_name_wd')
            // Nama cabang (tanpa N+1 query)
            ->selectRaw("(SELECT b.name FROM mst_branches b WHERE b.id = tx_qty_parts.branch_id) AS branch_name_temp");

            // ====== 1. COUNT ringan (recordsTotal & recordsFiltered) ======
            $totalRecords = (clone $base)->count();

            $filteredRecords = $totalRecords;
            $searchValue = trim((string) $request->input('search.value', ''));
            if ($searchValue !== '') {
                $countFiltered = clone $base;
                $countFiltered->where(function ($q) use ($searchValue) {
                    // multi-term: keyword dipecah per kata, antar kata di-AND
                    foreach (preg_split('/\s+/', $searchValue) as $term) {
                        if ($term === '') {
                            continue;
                        }
                        $like = '%'.$term.'%';
                        $q->where(function ($q2) use ($like) {
                            $q2->where('mst_parts.part_number', 'LIKE', $like)
                            ->orWhere('mst_parts.part_name', 'LIKE', $like)
                            ->orWhere('mg_01.title_ind', 'LIKE', $like)
                            ->orWhere('mg_03.title_ind', 'LIKE', $like)
                            ->orWhereRaw('(SELECT name FROM mst_branches WHERE id = tx_qty_parts.branch_id) LIKE ?', [$like]);
                        });
                    }
                });
                $filteredRecords = $countFiltered->count();
            }

            // ====== 2. QUERY 1 (ringan, tanpa subquery berat):
            //      dapatkan id urut (ORDER BY + pagination) ======
            $orderByMap = [
                0 => 'mst_parts.part_number',
                1 => 'mst_parts.part_name',
                2 => 'mg_01.title_ind',
                3 => 'mg_03.title_ind',
                4 => 'branch_name_temp',
            ];
            $orderIndex = (int) $request->input('order.0.column', 0);
            $orderDir = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
            $orderCol = $orderByMap[$orderIndex] ?? 'mst_parts.part_number';

            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);

            $rankQuery = clone $base;
            if ($searchValue !== '') {
                $rankQuery->where(function ($q) use ($searchValue) {
                    foreach (preg_split('/\s+/', $searchValue) as $term) {
                        if ($term === '') {
                            continue;
                        }
                        $like = '%'.$term.'%';
                        $q->where(function ($q2) use ($like) {
                            $q2->where('mst_parts.part_number', 'LIKE', $like)
                            ->orWhere('mst_parts.part_name', 'LIKE', $like)
                            ->orWhere('mg_01.title_ind', 'LIKE', $like)
                            ->orWhere('mg_03.title_ind', 'LIKE', $like)
                            ->orWhereRaw('(SELECT name FROM mst_branches WHERE id = tx_qty_parts.branch_id) LIKE ?', [$like]);
                        });
                    }
                });
            }
            $rankQuery->orderBy($orderCol, $orderDir)
            ->select('tx_qty_parts.id AS rank');
            if ($length > 0) {
                $rankQuery->skip($start)->take($length);
            }
            $ranks = $rankQuery->pluck('rank');

            // Tidak ada data (mis. hasil search kosong)
            if ($ranks->isEmpty()) {
                return response()->json([
                    'draw' => (int) $request->input('draw'),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => [],
                ]);
            }

            // ====== 3. QUERY 2 (dasar, TANPA subquery di SELECT):
            //      ambil baris untuk id hasil QUERY 1 (≤length).
            //      Kompatibel MySQL 5.7: tidak ada correlated subquery
            //      di SELECT, tidak ada derived table. ======
            $data = (clone $base)
            ->whereIn('tx_qty_parts.id', $ranks->all())
            ->orderByRaw('FIELD(tx_qty_parts.id, '.implode(',', $ranks->all()).')')
            ->get();

            // ====== 4. Hitung nilai berat (has_tx, SO, OO, IT, harga) via
            //      query batch per part_id DI PHP — menghindari correlated
            //      subquery di SELECT yang bermasalah di MySQL 5.7 ======
            $data = $this->enrichStockRows($data);

            // ====== 5. Yajra: DataTables dari hasil enrich ======
            return DataTables::of($data)
            ->setTotalRecords($totalRecords)
            ->setFilteredRecords($filteredRecords)
            ->skipPaging()     // pagination sudah diterapkan di QUERY 1
            ->skipAutoFilter() // search sudah diterapkan di QUERY 1
            ->order(function ($q) {}) // order sudah diterapkan di QUERY 1
            ->addColumn('part_number_with_delimiter', function ($sql) {
                $partNumber = $sql->part_number;
                if(strlen($partNumber)<11){
                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                }else{
                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                }
                return $partNumber;
            })
            ->addColumn('parts_name', function ($sql) {
                return $sql->part_name_wd;
            })
            ->editColumn('branch_name_temp', function ($sql) {
                return $sql->branch_name_temp ?? '';
            })
            ->editColumn('SOqty', function ($sql) {
                $qty = (int)($sql->so_qty ?? 0) + (int)($sql->sj_qty ?? 0);
                if ($qty > 0) {
                    return '<a href="#" onclick="dispSalesOrderInfo('.$sql->part_idx.','.$sql->branch_id_tmp.');">'.$qty.'</a>';
                }
                return $qty;
            })
            ->editColumn('OOqty', function ($sql) {
                $oo = (int)($sql->oo_memo_qty ?? 0) + (int)($sql->oo_po_qty ?? 0) - (int)($sql->oo_ro_qty ?? 0);
                if ($oo > 0) {
                    return '<a href="#" onclick="dispOnOrderInfo('.$sql->part_idx.','.$sql->branch_id_tmp.');">'.$oo.'</a>';
                }
                return $oo;
            })
            ->editColumn('ITqty', function ($sql) {
                $qty = (int)($sql->it_qty ?? 0);
                if ($qty > 0) {
                    return '<a href="#" onclick="dispInTransitInfo('.$sql->part_idx.','.$sql->branch_id_tmp.');">'.$qty.'</a>';
                }
                return $qty;
            })
            ->editColumn('final_cost_val', function ($sql) {
                return (int)($sql->final_cost_val ?? 0);
            })
            ->editColumn('last_final_price_val', function ($sql) {
                return (int)($sql->last_final_price_val ?? 0);
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
                if ($sql->has_tx=='N'){
                    return '<input type="checkbox" name="delRow'.$sql->rank.'" id="delRow'.$sql->rank.'">';
                }else{
                    return '<input type="hidden" name="delRow'.$sql->rank.'" id="delRow'.$sql->rank.'">';
                }
            })
            ->rawColumns(['part_number_with_delimiter', 'parts_name', 'branch_name_temp', 'SOqty', 'OOqty', 'ITqty', 'last_final_price_val', 'price_list_val', 'action', 'del_checkbox'])
            ->toJson();
        }

        $data = [
            'stocks' => [],
            'rowCount' => Tx_qty_part::count(),
            'title' => $this->title,
            'folder' => $this->folder,
            'uri_folder' => $this->uri_folder,
            'queryBranch' => $queryBranch,
            'queryBrand' => $queryBrand,
            'queryPartType' => $queryPartType,
            'param' => $param,
            'parameter' => $parameter,
        ];

        return view('tx.'.$this->folder.'.index-stock-master-serverside', $data);
    }

    /**
     * Hitung kolom "berat" (has_tx, SOqty, OOqty, ITqty, final_cost,
     * harga terakhir) untuk baris stock yang sudah di-paginate.
     *
     * Menggunakan query batch per part_id (dikerjakan di PHP) — TANPA
     * correlated subquery di SELECT, sehingga kompatibel MySQL 5.7
     * (menghindari error "Unknown column ... in 'where clause'").
     *
     * @param  \Illuminate\Support\Collection  $rows  baris hasil QUERY 2
     * @return \Illuminate\Support\Collection
     */
    private function enrichStockRows($rows)
    {
        if ($rows->isEmpty()) {
            return $rows;
        }

        $partIds = $rows->pluck('part_idx')->unique()->values()->all();
        $idList = implode(',', $partIds);

        // helper: memetakan hasil agregat part_id:branch_id => record
        $toMap = function ($result) {
            $m = [];
            foreach ($result as $r) {
                $m[$r->part_id.':'.$r->branch_id] = $r;
            }

            return $m;
        };

        // ===== has_tx: part pernah dipakai transaksi =====
        $hasTxSql = 'SELECT part_id FROM tx_delivery_order_parts WHERE part_id IN ('.$idList.') AND active=\'Y\''
            .' UNION SELECT part_id FROM tx_nota_retur_parts WHERE part_id IN ('.$idList.') AND active=\'Y\''
            .' UNION SELECT part_id FROM tx_purchase_memo_parts WHERE part_id IN ('.$idList.') AND active=\'Y\''
            .' UNION SELECT part_id FROM tx_purchase_order_parts WHERE part_id IN ('.$idList.') AND active=\'Y\''
            .' UNION SELECT part_id FROM tx_purchase_quotation_parts WHERE part_id IN ('.$idList.') AND active=\'Y\''
            .' UNION SELECT part_id FROM tx_purchase_retur_parts WHERE part_id IN ('.$idList.') AND active=\'Y\''
            .' UNION SELECT part_id FROM tx_receipt_order_parts WHERE part_id IN ('.$idList.') AND active=\'Y\''
            .' UNION SELECT part_id FROM tx_sales_order_parts WHERE part_id IN ('.$idList.') AND active=\'Y\''
            .' UNION SELECT part_id FROM tx_sales_quotation_parts WHERE part_id IN ('.$idList.') AND active=\'Y\''
            .' UNION SELECT part_id FROM tx_stock_assembly_parts WHERE part_id IN ('.$idList.') AND active=\'Y\''
            .' UNION SELECT part_id FROM tx_stock_disassembly_parts WHERE part_id IN ('.$idList.') AND active=\'Y\''
            .' UNION SELECT part_id FROM tx_stock_transfer_parts WHERE part_id IN ('.$idList.') AND active=\'Y\''
            .' UNION SELECT part_id FROM tx_surat_jalan_parts WHERE part_id IN ('.$idList.') AND active=\'Y\'';
        $hasTx = collect(DB::select($hasTxSql))->pluck('part_id')->flip();

        // ===== SOqty - Sales Order =====
        $soRows = DB::table('tx_sales_order_parts AS txsop')
            ->join('tx_sales_orders AS txso', function ($j) {
                $j->on('txsop.order_id', '=', 'txso.id')
                  ->where('txso.active', 'Y')->where('txso.is_draft', 'N')->where('txso.need_approval', 'N');
            })
            ->whereIn('txsop.part_id', $partIds)
            ->where('txsop.active', 'Y')
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)
                  ->from('tx_delivery_order_parts as tx_do_parts')
                  ->join('tx_delivery_orders as tx_do', function ($j) {
                      $j->on('tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                        ->where('tx_do.is_draft', 'N')->where('tx_do.active', 'Y');
                  })
                  ->whereColumn('tx_do_parts.sales_order_id', 'txso.id')
                  ->where('tx_do_parts.active', 'Y');
            })
            ->select('txsop.part_id', 'txso.branch_id', DB::raw('SUM(txsop.qty) AS total'))
            ->groupBy('txsop.part_id', 'txso.branch_id')
            ->get();
        $soMap = $toMap($soRows);

        // ===== SOqty - Surat Jalan =====
        $sjRows = DB::table('tx_surat_jalan_parts AS txsjp')
            ->join('tx_surat_jalans AS txsj', function ($j) {
                $j->on('txsjp.surat_jalan_id', '=', 'txsj.id')
                  ->where('txsj.active', 'Y')->where('txsj.need_approval', 'N')->where('txsj.is_draft', 'N');
            })
            ->whereIn('txsjp.part_id', $partIds)
            ->where('txsjp.active', 'Y')
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)
                  ->from('tx_delivery_order_non_tax_parts as tx_do_parts')
                  ->join('tx_delivery_order_non_taxes as tx_do', function ($j) {
                      $j->on('tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                        ->where('tx_do.is_draft', 'N')->where('tx_do.active', 'Y');
                  })
                  ->whereColumn('tx_do_parts.sales_order_id', 'txsj.id')
                  ->where('tx_do_parts.active', 'Y');
            })
            ->select('txsjp.part_id', 'txsj.branch_id', DB::raw('SUM(txsjp.qty) AS total'))
            ->groupBy('txsjp.part_id', 'txsj.branch_id')
            ->get();
        $sjMap = $toMap($sjRows);

        // ===== OOqty - Purchase Memo =====
        $memoRows = DB::table('tx_purchase_memo_parts AS m')
            ->join('tx_purchase_memos AS h', function ($j) {
                $j->on('m.memo_id', '=', 'h.id')->where('h.is_draft', 'N')->where('h.active', 'Y');
            })
            ->whereIn('m.part_id', $partIds)->where('m.active', 'Y')
            ->select('m.part_id', 'h.branch_id', DB::raw('SUM(m.qty) AS total'))
            ->groupBy('m.part_id', 'h.branch_id')
            ->get();
        $memoMap = $toMap($memoRows);

        // ===== OOqty - Purchase Order =====
        $poRows = DB::table('tx_purchase_order_parts AS m')
            ->join('tx_purchase_orders AS h', function ($j) {
                $j->on('m.order_id', '=', 'h.id')
                  ->where('h.is_draft', 'N')->where('h.active', 'Y')->whereNotNull('h.approved_by');
            })
            ->whereIn('m.part_id', $partIds)->where('m.active', 'Y')
            ->select('m.part_id', 'h.branch_id', DB::raw('SUM(m.qty) AS total'))
            ->groupBy('m.part_id', 'h.branch_id')
            ->get();
        $poMap = $toMap($poRows);

        // ===== OOqty - Receipt Order =====
        $roRows = DB::table('tx_receipt_order_parts AS m')
            ->join('tx_receipt_orders AS h', function ($j) {
                $j->on('m.receipt_order_id', '=', 'h.id')->where('h.is_draft', 'N')->where('h.active', 'Y');
            })
            ->whereIn('m.part_id', $partIds)->where('m.active', 'Y')
            ->select('m.part_id', 'h.branch_id', DB::raw('SUM(m.qty) AS total'))
            ->groupBy('m.part_id', 'h.branch_id')
            ->get();
        $roMap = $toMap($roRows);

        // ===== ITqty - In Transit (Stock Transfer) =====
        $itRows = DB::table('tx_stock_transfer_parts AS m')
            ->join('tx_stock_transfers AS h', function ($j) {
                $j->on('m.stock_transfer_id', '=', 'h.id')
                  ->whereNotNull('h.approved_by')->whereNull('h.received_by')->where('h.active', 'Y');
            })
            ->whereIn('m.part_id', $partIds)->where('m.active', 'Y')
            ->select('m.part_id', 'h.branch_to_id AS branch_id', DB::raw('SUM(m.qty) AS total'))
            ->groupBy('m.part_id', 'h.branch_to_id')
            ->get();
        $itMap = $toMap($itRows);

        // ===== Final Cost (final_cost terakhir per part+branch) =====
        $fcRows = DB::table('tx_receipt_order_parts AS a')
            ->join('tx_receipt_orders AS h', function ($j) {
                $j->on('a.receipt_order_id', '=', 'h.id')->where('h.is_draft', 'N')->where('h.active', 'Y');
            })
            ->whereIn('a.part_id', $partIds)->where('a.final_cost', '>', 0)->where('a.active', 'Y')
            ->select('a.part_id', 'h.branch_id', 'a.final_cost', 'h.created_at')
            ->orderBy('h.created_at', 'DESC')
            ->get();
        $fcMap = [];
        foreach ($fcRows as $r) {
            $k = $r->part_id.':'.$r->branch_id;
            if (! isset($fcMap[$k])) {
                $fcMap[$k] = (int) $r->final_cost; // pertama = terbaru (sudah DESC)
            }
        }

        // ===== Last Final Price (SO UNION SJ, terbaru per part+branch) =====
        $soPrice = DB::table('tx_sales_order_parts AS p')
            ->join('tx_sales_orders AS h', function ($j) {
                $j->on('p.order_id', '=', 'h.id')
                  ->where('h.active', 'Y')->where('h.is_draft', 'N')->where('h.need_approval', 'N');
            })
            ->whereIn('p.part_id', $partIds)->where('p.active', 'Y')
            ->select('p.part_id', 'h.branch_id', 'p.price', 'p.created_at');
        $priceRows = DB::table('tx_surat_jalan_parts AS p')
            ->join('tx_surat_jalans AS h', function ($j) {
                $j->on('p.surat_jalan_id', '=', 'h.id')
                  ->where('h.active', 'Y')->where('h.need_approval', 'N')->where('h.is_draft', 'N');
            })
            ->whereIn('p.part_id', $partIds)->where('p.active', 'Y')
            ->select('p.part_id', 'h.branch_id', 'p.price', 'p.created_at')
            ->unionAll($soPrice)
            ->orderBy('created_at', 'DESC')
            ->get();
        $lfpMap = [];
        foreach ($priceRows as $r) {
            $k = $r->part_id.':'.$r->branch_id;
            if (! isset($lfpMap[$k])) {
                $lfpMap[$k] = (int) $r->price; // pertama = terbaru (sudah DESC)
            }
        }

        // ===== Attach nilai ke setiap baris =====
        foreach ($rows as $row) {
            $k = $row->part_idx.':'.$row->branch_id_tmp;
            $row->has_tx = isset($hasTx[$row->part_idx]) ? 'Y' : 'N';
            $row->so_qty = (int) ($soMap[$k]->total ?? 0);
            $row->sj_qty = (int) ($sjMap[$k]->total ?? 0);
            $row->oo_memo_qty = (int) ($memoMap[$k]->total ?? 0);
            $row->oo_po_qty = (int) ($poMap[$k]->total ?? 0);
            $row->oo_ro_qty = (int) ($roMap[$k]->total ?? 0);
            $row->it_qty = (int) ($itMap[$k]->total ?? 0);
            $row->final_cost_val = (int) ($fcMap[$k] ?? 0);
            $row->last_final_price_val = (int) ($lfpMap[$k] ?? 0);
        }

        return $rows;
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
