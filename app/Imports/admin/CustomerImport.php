<?php

namespace App\Imports\admin;

use App\Models\Mst_customer;
use App\Models\Mst_customer_shipment_address;
use App\Models\Mst_branch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class CustomerImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $entity_type_id = $row[2];
            $customer_unique_code = $row[3];
            $name = $row[4];
            $office_address = $row[5];
            $province_id = $row[7];
            $city_id = !is_null($row[9])?$row[9]:9999;
            $district_id = !is_null($row[11])&&$row[11]!='#N/A'?$row[11]:9999;
            $sub_district_id = !is_null($row[13])&&$row[13]!='#N/A'?$row[13]:9999;
            $post_code = !is_null($row[14])?$row[14]:'00000';
            $cust_email = !is_null($row[15])?$row[15]:'email@email.com';
            $branch_initial = $row[16];
            $phone1 = !is_null($row[17])?$row[17]:'00000';
            $phone2 = !is_null($row[18])?$row[18]:'00000';
            $pic1_name = !is_null($row[19])?$row[19]:'.';
            $pic1_phone = !is_null($row[20])?$row[20]:null;
            $pic1_email = !is_null($row[21])?$row[21]:null;
            $pic2_name = !is_null($row[22])?$row[22]:null;
            $pic2_phone = !is_null($row[23])?$row[23]:null;
            $pic2_email = !is_null($row[24])?$row[24]:null;
            $npwp_no = !is_null($row[25])?$row[25]:null;
            $npwp_address = !is_null($row[26])?$row[26]:null;
            $npwp_province_id = !is_null($row[28])&&$row[28]!='#N/A'?$row[28]:null;
            $npwp_city_id = !is_null($row[30])&&$row[30]!='#N/A'?$row[30]:null;
            $npwp_district_id = !is_null($row[32])&&$row[32]!='#N/A'?$row[32]:null;
            $npwp_sub_district_id = !is_null($row[34])&&$row[34]!='#N/A'?$row[34]:null;
            $credit_limit = is_numeric($row[35])?$row[35]:0;
            $limit_balance = is_numeric($row[36])?$row[36]:0;
            $top = is_numeric($row[37])?$row[37]:0;
            $salesman_id = $row[39];
            $salesman_id2 = null;
            $customer_status = $row[41]=='ACTIVE'?'Y':'N';
            $payment_status = $row[42]=='LANCAR'?'Y':'N';

            $shipment_address = !is_null($row[43])?$row[43]:'<no address>';
            $shipment_province_id = !is_null($row[45])&&$row[45]!='#N/A'?$row[45]:9999;
            $shipment_city_id = !is_null($row[47])&&$row[47]!='#N/A'?$row[47]:9999;
            $shipment_district_id = !is_null($row[49])&&$row[49]!='#N/A'?$row[49]:9999;
            $shipment_sub_district_id = !is_null($row[51])&&$row[51]!='#N/A'?$row[51]:9999;
            $shipment_phone = !is_null($row[52])?$row[52]:'00000';
            $hipment_post_code = !is_null($row[53])?$row[53]:'00000';

            $qBranch = Mst_branch::where([
                'initial'=>$branch_initial,
            ])
            ->first();
            if ($customer_unique_code!='CUST CODE' && $qBranch) {

                $custId = 0;
                $qCusts = Mst_customer::where([
                    'customer_unique_code'=>$customer_unique_code,
                ])
                ->first();
                if ($qCusts) {
                    $custId = $qCusts->id;
                    $updCust = Mst_customer::where([
                        'id'=>$qCusts->id,
                    ])
                    ->update([
                        'entity_type_id' => $entity_type_id,
                        'customer_unique_code' => $customer_unique_code,
                        'name' => $name,
                        'slug' => SlugService::createSlug(Mst_customer::class, 'slug', $name),
                        'office_address' => $office_address,
                        'province_id' => $province_id,
                        'city_id' => $city_id,
                        'district_id' => $district_id,
                        'sub_district_id' => $sub_district_id,
                        'post_code' => $post_code,
                        'cust_email' => $cust_email,
                        'branch_id' => $qBranch->id,
                        'phone1' => $phone1,
                        'phone2' => $phone2,
                        'pic1_name' => $pic1_name,
                        'pic1_phone' => $pic1_phone,
                        'pic1_email' => $pic1_email,
                        'pic2_name' => $pic2_name,
                        'pic2_phone' => $pic2_phone,
                        'pic2_email' => $pic2_email,
                        'npwp_no' => $npwp_no,
                        'npwp_address' => $npwp_address,
                        'npwp_province_id' => $npwp_province_id,
                        'npwp_city_id' => $npwp_city_id,
                        'npwp_district_id' => $npwp_district_id,
                        'npwp_sub_district_id' => $npwp_sub_district_id,
                        'credit_limit' => $credit_limit,
                        'limit_balance' => $limit_balance,
                        'top' => $top,
                        'salesman_id' => $salesman_id,
                        'salesman_id2' => $salesman_id2,
                        'customer_status' => $customer_status,
                        'payment_status' => $payment_status,
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                }else{
                    $insCust = Mst_customer::create([
                        'entity_type_id' => $entity_type_id,
                        'customer_unique_code' => $customer_unique_code,
                        'name' => $name,
                        'slug' => SlugService::createSlug(Mst_customer::class, 'slug', $name),
                        'office_address' => $office_address,
                        'province_id' => $province_id,
                        'city_id' => $city_id,
                        'district_id' => $district_id,
                        'sub_district_id' => $sub_district_id,
                        'post_code' => $post_code,
                        'cust_email' => $cust_email,
                        'branch_id' => $qBranch->id,
                        'phone1' => $phone1,
                        'phone2' => $phone2,
                        'pic1_name' => $pic1_name,
                        'pic1_phone' => $pic1_phone,
                        'pic1_email' => $pic1_email,
                        'pic2_name' => $pic2_name,
                        'pic2_phone' => $pic2_phone,
                        'pic2_email' => $pic2_email,
                        'npwp_no' => $npwp_no,
                        'npwp_address' => $npwp_address,
                        'npwp_province_id' => $npwp_province_id,
                        'npwp_city_id' => $npwp_city_id,
                        'npwp_district_id' => $npwp_district_id,
                        'npwp_sub_district_id' => $npwp_sub_district_id,
                        'credit_limit' => $credit_limit,
                        'limit_balance' => $limit_balance,
                        'top' => $top,
                        'salesman_id' => $salesman_id,
                        'salesman_id2' => $salesman_id2,
                        'customer_status' => $customer_status,
                        'payment_status' => $payment_status,
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                    $custId = $insCust->id;
                }

                // $delShipment = Mst_customer_shipment_address::where([
                //     'customer_id'=>$custId,
                // ])
                // ->delete();

                $insCustShipment = Mst_customer_shipment_address::create([
                    'customer_id'=>$custId,
                    'address'=>$shipment_address,
                    'province_id'=>$shipment_province_id,
                    'city_id'=>$shipment_city_id,
                    'district_id'=>$shipment_district_id,
                    'sub_district_id'=>$shipment_sub_district_id,
                    'phone'=>$shipment_phone,
                    'post_code'=>$hipment_post_code,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id
                ]);
            }
        }
    }
}
