<?php

namespace App\Http\Controllers\dbg;

use DateTime;
use DateTimeZone;
use App\Models\Mst_branch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_receipt_order;

class RptAnalizeDebtSummController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $timezoneNow = new DateTimeZone('Asia/Jakarta');
        $date_local_now = new DateTime();
        $date_local_now->setTimeZone($timezoneNow);
        $date_local_lastmonth = new DateTime();
        $date_local_lastmonth->setTimeZone($timezoneNow);
        $date_local_last2month = new DateTime();
        $date_local_last2month->setTimeZone($timezoneNow);
        $date_local_last3month = new DateTime();
        $date_local_last3month->setTimeZone($timezoneNow);
        $date_local_lastMore3month = new DateTime();
        $date_local_lastMore3month->setTimeZone($timezoneNow);

        $this_month = $date_local_now;
        $last_month = $date_local_lastmonth;
        $last_2month = $date_local_last2month;
        $last_3month = $date_local_last3month;
        $last_more3month = $date_local_lastMore3month;
        date_add($this_month, date_interval_create_from_date_string("0 months"));
        // echo 'this month '.date_format($this_month,"Y-m").'<br/>';
        date_add($last_month, date_interval_create_from_date_string("-1 months"));
        // echo 'last month '.date_format($last_month,"Y-m").'<br/>';
        date_add($last_2month, date_interval_create_from_date_string("-2 months"));
        // echo 'last 2 months '.date_format($last_2month,"Y-m").'<br/>';
        date_add($last_3month, date_interval_create_from_date_string("-3 months"));
        // echo 'last 3 months '.date_format($last_3month,"Y-m").'<br/>';
        date_add($last_more3month, date_interval_create_from_date_string("-4 months"));
        // echo 'last 3 months '.date_format($last_3month,"Y-m").'<br/>';

        $qBranches = Mst_branch::where([
            'active'=>'Y',
        ])
        ->orderBy('name','ASC')
        ->get();
        foreach($qBranches as $branch){
            $qRO = Tx_receipt_order::leftJoin('mst_suppliers as m_sp','tx_receipt_orders.supplier_id','=','m_sp.id')
            ->select(
                'm_sp.id as supplier_id',
                'm_sp.name as supplier_name',
            )
            // ->selectRaw('SUM(tx_receipt_orders.invoice_amount) as tot_amount')
            ->whereRaw('tx_receipt_orders.receipt_no NOT LIKE \'%Draft%\'')
            ->where([
                'tx_receipt_orders.branch_id'=>$branch->id,
                'tx_receipt_orders.active'=>'Y',
            ])
            ->orderBy('m_sp.name','ASC')
            ->groupBy('m_sp.id')
            ->groupBy('m_sp.name')
            ->get();
            if ($qRO){
                if ($qRO->count()>0){
                    echo $branch->name.'<br/>';
                    foreach($qRO as $ro){
                        echo '&nbsp;&nbsp;&nbsp;'.$ro->supplier_name.'<br/>';

                        $qRO_thismonth = Tx_receipt_order::selectRaw('SUM(invoice_amount*(CASE WHEN exchange_rate>0 THEN exchange_rate ELSE 1 END)) AS tot_amount')
                        ->whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                        ->whereRaw('DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($this_month,"Y-m").'\'')
                        ->where([
                            'supplier_id'=>$ro->supplier_id,
                            'branch_id'=>$branch->id,
                            'active'=>'Y',
                        ])
                        ->first();
                        echo '&nbsp;&nbsp;&nbsp;'.'this month total amount: '.(!is_null($qRO_thismonth->tot_amount)?$qRO_thismonth->tot_amount:0).'<br/>';

                        $qRO_lastmonth = Tx_receipt_order::selectRaw('SUM(invoice_amount*(CASE WHEN exchange_rate>0 THEN exchange_rate ELSE 1 END)) AS tot_amount')
                        ->whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                        ->whereRaw('DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($last_month,"Y-m").'\'')
                        ->where([
                            'supplier_id'=>$ro->supplier_id,
                            'branch_id'=>$branch->id,
                            'active'=>'Y',
                        ])
                        ->first();
                        echo '&nbsp;&nbsp;&nbsp;'.'last month total amount: '.(!is_null($qRO_lastmonth->tot_amount)?$qRO_lastmonth->tot_amount:0).'<br/>';

                        $qRO_last2month = Tx_receipt_order::selectRaw('SUM(invoice_amount*(CASE WHEN exchange_rate>0 THEN exchange_rate ELSE 1 END)) AS tot_amount')
                        ->whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                        ->whereRaw('DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($last_2month,"Y-m").'\'')
                        ->where([
                            'supplier_id'=>$ro->supplier_id,
                            'branch_id'=>$branch->id,
                            'active'=>'Y',
                        ])
                        ->first();
                        echo '&nbsp;&nbsp;&nbsp;'.'last 2 months total amount: '.(!is_null($qRO_last2month->tot_amount)?$qRO_last2month->tot_amount:0).'<br/>';

                        $qRO_last3month = Tx_receipt_order::selectRaw('SUM(invoice_amount*(CASE WHEN exchange_rate>0 THEN exchange_rate ELSE 1 END)) AS tot_amount')
                        ->whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                        ->whereRaw('DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($last_3month,"Y-m").'\'')
                        ->where([
                            'supplier_id'=>$ro->supplier_id,
                            'branch_id'=>$branch->id,
                            'active'=>'Y',
                        ])
                        ->first();
                        echo '&nbsp;&nbsp;&nbsp;'.'last 3 months total amount: '.(!is_null($qRO_last3month->tot_amount)?$qRO_last3month->tot_amount:0).'<br/>';

                        $qRO_lastMore3month = Tx_receipt_order::selectRaw('SUM(invoice_amount*(CASE WHEN exchange_rate>0 THEN exchange_rate ELSE 1 END)) AS tot_amount')
                        ->whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                        ->whereRaw('(receipt_date BETWEEN \''.date_format($last_3month,"Y").'-01-01 0:0:0\' AND \''.date_format($last_3month,"Y-m").'-01 0:0:0\')')
                        // ->whereRaw('(receipt_date BETWEEN '2024-01-01 0:0:0' AND '2024-09-01 0:0:0')')
                        // ->whereRaw('DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($last_3month,"Y-m").'\'')
                        ->where([
                            'supplier_id'=>$ro->supplier_id,
                            'branch_id'=>$branch->id,
                            'active'=>'Y',
                        ])
                        ->first();
                        echo '&nbsp;&nbsp;&nbsp;'.'last more 3 months total amount: '.(!is_null($qRO_lastMore3month->tot_amount)?$qRO_lastMore3month->tot_amount:0).'<br/>';

                        $qRO_thisyear = Tx_receipt_order::selectRaw('SUM(invoice_amount*(CASE WHEN exchange_rate>0 THEN exchange_rate ELSE 1 END)) AS tot_amount')
                        ->whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                        ->whereRaw('DATE_FORMAT(receipt_date, "%Y")=\''.date_format($last_3month,"Y").'\'')
                        ->where([
                            'supplier_id'=>$ro->supplier_id,
                            'branch_id'=>$branch->id,
                            'active'=>'Y',
                        ])
                        ->first();
                        echo '&nbsp;&nbsp;&nbsp;'.'this year total amount: '.(!is_null($qRO_thisyear->tot_amount)?$qRO_thisyear->tot_amount:0).'<br/>';

                        // echo 'this month: '.'<br/>';
                        // echo 'this month '.date_format($this_month,"Y-m").'<br/>';
                        // echo 'last month '.date_format($last_month,"Y-m").'<br/>';
                        // echo 'last 2 months '.date_format($last_2month,"Y-m").'<br/>';
                        // echo 'last 3 months '.date_format($last_3month,"Y-m").'<br/>';
                        // echo 'last more 3 months '.date_format($last_more3month,"Y-m").'<br/>';
                        echo '<br/>';
                    }
                }
            }
        }
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
        //
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
