<?php

namespace App\Imports\manual;

use App\Models\Mst_supplier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ManualSupplierImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        foreach($collection as $coll){
            $supplier_type_id = ($coll[6]==9999?11:10);
            $entity_type_id = $coll[1];
            $name = $coll[4];
            $supplier_code = $coll[3];
            $office_address = $coll[5];
            $country_id = $coll[6];
            $province_id = $coll[8];
            $city_id = $coll[10];
            $district_id = $coll[12];
            $sub_district_id = $coll[14];
            $post_code = $coll[16];
            $supplier_email = ($coll[17]!=null?$coll[17]:'\'-');
            $phone1 = $coll[18];
            $phone2 = $coll[19];
            $pic1_name = ($coll[20]!=null?$coll[20]:'\'-');
            $pic1_phone = $coll[21];
            $pic1_email = $coll[22];
            $pic2_name = $coll[23];
            $pic2_phone = $coll[24];
            $pic2_email = $coll[25];
            $npwp_no = $coll[26];
            $npwp_address = null;
            $npwp_province_id = null;
            $npwp_city_id = null;
            $npwp_district_id = null;
            $npwp_sub_district_id = null;
            $top = $coll[29];
            $credit_limit = $coll[27];
            $limit_balance = $coll[28];

            if ($entity_type_id!='ENTITY CODE' && $entity_type_id!=''){
                $qSupplier = Mst_supplier::where([
                    'supplier_code'=>$supplier_code,
                ])
                ->first();
                if (!$qSupplier){
                    $ins = Mst_supplier::create([
                        'supplier_type_id'=>$supplier_type_id,
                        'entity_type_id'=>$entity_type_id,
                        'name'=>$name,
                        'supplier_code'=>$supplier_code,
                        'office_address'=>$office_address,
                        'country_id'=>$country_id,
                        'province_id'=>$province_id,
                        'city_id'=>$city_id,
                        'district_id'=>$district_id,
                        'sub_district_id'=>$sub_district_id,
                        'post_code'=>$post_code,
                        'supplier_email'=>$supplier_email,
                        'phone1'=>$phone1,
                        'phone2'=>$phone2,
                        'pic1_name'=>$pic1_name,
                        'pic1_phone'=>$pic1_phone,
                        'pic1_email'=>$pic1_email,
                        'pic2_name'=>$pic2_name,
                        'pic2_phone'=>$pic2_phone,
                        'pic2_email'=>$pic2_email,
                        'npwp_no'=>$npwp_no,
                        'npwp_address'=>$npwp_address,
                        'npwp_province_id'=>$npwp_province_id,
                        'npwp_city_id'=>$npwp_city_id,
                        'npwp_district_id'=>$npwp_district_id,
                        'npwp_sub_district_id'=>$npwp_sub_district_id,
                        'top'=>$top,
                        'credit_limit'=>$credit_limit,
                        'limit_balance'=>$limit_balance,
                        'active'=>'Y',
                        'created_by'=>1,
                        'updated_by'=>1,
                    ]);
                }
            }
        }
    }
}
