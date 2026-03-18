<?php

namespace App\Imports\manual;

use App\Models\Mst_customer;
use App\Models\Mst_customer_shipment_address;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class ManualCustomerImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        foreach($collection as $coll){
            $entity_type_id = $coll[1];
            $customer_unique_code = $coll[3];
            $customer_name = $coll[4];
            $office_address = $coll[5];
            $province_id = $coll[6];
            $city_id = $coll[8];
            $district_id = $coll[10];
            $sub_district_id = $coll[12];
            $post_code = $coll[14];
            $customer_email = $coll[15];
            $branch_id = $coll[16];
            $phone1 = $coll[18];
            $phone2 = $coll[19];
            $pic1_name = $coll[20];
            $pic1_phone = $coll[21];
            $pic1_email = $coll[22];
            $pic2_name = $coll[23];
            $pic2_phone = $coll[24];
            $pic2_email = $coll[25];
            $npwp_no = $coll[26];
            $npwp_address = $coll[27];
            $npwp_province_id = $coll[28];
            $npwp_city_id = $coll[30];
            $npwp_district_id = $coll[32];
            $npwp_sub_district_id = $coll[34];
            $credit_limit = $coll[36];
            $limit_balance = $coll[37];
            $top = $coll[38];
            $salesman_id = $coll[39];
            $salesman_id2 = null;
            $customer_status = 'Y';
            $payment_status = 'Y';
            $shipment_customer_id = null;
            $shipment_address = $coll[44];
            $shipment_province_id = $coll[45];
            $shipment_city_id = $coll[47];
            $shipment_district_id = $coll[49];
            $shipment_sub_district_id = $coll[51];
            $shipment_phone = $coll[53];
            $shipment_post_code = $coll[54];

            if ($entity_type_id!='ENTITY CODE' && $entity_type_id!=''){

                $qCust = Mst_customer::where([
                    'customer_unique_code'=>$customer_unique_code,
                ])
                ->first();
                if (!$qCust){
                    $insCust = Mst_customer::create([
                        'entity_type_id'=>$entity_type_id,
                        'customer_unique_code'=>$customer_unique_code,
                        'name'=>$customer_name,
                        // 'slug',
                        'office_address'=>$office_address,
                        'province_id'=>$province_id,
                        'city_id'=>$city_id,
                        'district_id'=>$district_id,
                        'sub_district_id'=>$sub_district_id,
                        'post_code'=>($post_code!=''?$post_code:'000000'),
                        'cust_email'=>($customer_email!=''?$customer_email:'\'-'),
                        'branch_id'=>($branch_id!=''?$branch_id:0),
                        'phone1'=>($phone1!=''?$phone1:'\'-'),
                        'phone2'=>($phone2!=''?$phone2:'\'-'),
                        'pic1_name'=>($pic1_name!=''?$pic1_name:'\'-'),
                        'pic1_phone'=>($pic1_phone!=''?$pic1_phone:'\'-'),
                        'pic1_email'=>($pic1_email!=''?$pic1_email:'\'-'),
                        'pic2_name'=>($pic2_name!=''?$pic2_name:'\'-'),
                        'pic2_phone'=>($pic2_phone!=''?$pic2_phone:'\'-'),
                        'pic2_email'=>($pic2_email!=''?$pic2_email:'\'-'),
                        'npwp_no'=>($npwp_no!=''?$npwp_no:'\'-'),
                        'npwp_address'=>($npwp_address!=''?$npwp_address:'\'-'),
                        'npwp_province_id'=>($npwp_province_id!=''?$npwp_province_id:null),
                        'npwp_city_id'=>($npwp_city_id!=''?$npwp_city_id:null),
                        'npwp_district_id'=>($npwp_district_id!=''?$npwp_district_id:null),
                        'npwp_sub_district_id'=>($npwp_sub_district_id!=''?$npwp_sub_district_id:null),
                        'credit_limit'=>($credit_limit!=''?$credit_limit:0),
                        'limit_balance'=>($limit_balance!=''?$limit_balance:0),
                        'top'=>($top!=''?$top:0),
                        'salesman_id'=>($salesman_id!=''?$salesman_id:null),
                        'salesman_id2'=>($salesman_id2!=''?$salesman_id2:null),
                        'customer_status'=>$customer_status,
                        'payment_status'=>$payment_status,
                        'active'=>'Y',
                        'created_by'=>1,
                        'updated_by'=>1,
                    ]);

                    if ($shipment_address!=''){
                        $insCustShipment = Mst_customer_shipment_address::create([
                            'customer_id'=>$insCust->id,
                            'address'=>$shipment_address,
                            'province_id'=>($shipment_province_id!=''?$shipment_province_id:999),
                            'city_id'=>($shipment_city_id!=''?$shipment_city_id:9999),
                            'district_id'=>($shipment_district_id!=''?$shipment_district_id:9999),
                            'sub_district_id'=>($shipment_sub_district_id!=''?$shipment_sub_district_id:99999),
                            'phone'=>($shipment_phone!=''?$shipment_phone:'\'-'),
                            'post_code'=>($shipment_post_code!=''?$shipment_post_code:'\'-'),
                            'active'=>'Y',
                            'created_by'=>1,
                            'updated_by'=>1,
                        ]);
                    }

                }
            }
        }
    }
}
