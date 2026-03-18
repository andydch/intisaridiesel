<?php

namespace App\Exports\coretax;

use App\Models\Tx_delivery_order;
use App\Exports\coretax\FakturListExport;
use App\Exports\coretax\FakturPartListExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FakturToCoretaxExport implements WithMultipleSheets
{
    protected $start_date;
    protected $end_date;
    protected $isAllCust;
    protected $someCust;

    public function __construct($start_date,$end_date,$isAllCust,$someCust)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->isAllCust = $isAllCust;
        $this->someCust = $someCust;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $start_date = explode("/", $this->start_date);
        $end_date = explode("/", $this->end_date);

        $fakturs = Tx_delivery_order::leftJoin('mst_customers as m_cust','tx_delivery_orders.customer_id','=','m_cust.id')
        ->leftJoin('mst_globals as ett_type','tx_delivery_orders.customer_entity_type_id','=','ett_type.id')
        ->leftJoin('mst_provinces as m_pr','m_cust.npwp_province_id','=','m_pr.id')
        ->leftJoin('mst_cities as m_ct','m_cust.npwp_city_id','=','m_ct.id')
        ->leftJoin('mst_districts as m_ds','m_cust.npwp_district_id','=','m_ds.id')
        ->leftJoin('mst_sub_districts as m_sds','m_cust.npwp_sub_district_id','=','m_sds.id')
        ->leftJoin('mst_countries as m_co','m_ct.country_id','=','m_co.id')
        ->select(
            'tx_delivery_orders.id as faktur_id',
            'tx_delivery_orders.delivery_order_no',
            'tx_delivery_orders.delivery_order_date',
            'tx_delivery_orders.customer_name',
            'm_cust.npwp_no as npwp_no_cust',
            'm_cust.office_address',
            'm_cust.npwp_address',
            'm_pr.province_name',
            'm_ct.city_name',
            'm_ct.country_id',
            'm_ds.district_name',
            'm_sds.sub_district_name',
            'm_co.country_name',
            'm_cust.post_code',
            'm_cust.cust_email',
            'ett_type.string_val as ett_name',
        )
        ->whereRaw('tx_delivery_orders.delivery_order_no NOT LIKE \'%Draft%\'')
        ->whereRaw('tx_delivery_orders.delivery_order_date>=\''.$start_date[2].'-'.$start_date[1].'-'.$start_date[0].'\'
            AND tx_delivery_orders.delivery_order_date<=\''.$end_date[2].'-'.$end_date[1].'-'.$end_date[0].'\'')
        ->when($this->isAllCust!='on', function($q){
            $q->whereIn('tx_delivery_orders.customer_id', explode(",",$this->someCust));
        })
        ->whereRaw('tx_delivery_orders.faktur_dl_date IS NULL')
        ->where([
            'tx_delivery_orders.active'=>'Y',
        ])
        ->orderBy('tx_delivery_orders.delivery_order_date','ASC')
        ->orderBy('tx_delivery_orders.created_at','ASC')
        ->get();

        return [
            new FakturListExport($fakturs),
            new FakturPartListExport($fakturs),
        ];
    }
}
