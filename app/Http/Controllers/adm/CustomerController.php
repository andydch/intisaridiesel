<?php

namespace App\Http\Controllers\adm;

use App\Models\User;
use App\Models\Mst_city;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Rules\PhoneNumber;
use App\Models\Mst_customer;
use App\Models\Mst_district;
use App\Models\Mst_province;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Rules\CustCodeUnique;
use App\Models\Mst_sub_district;
use App\Helpers\GlobalFuncHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Mst_customer_shipment_address;
use App\Models\Mst_menu_user;

class CustomerController extends Controller
{
    protected $title = 'Customer';
    protected $folder = 'customer';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_customer::where('active','=','Y')
        ->orderBy('customer_unique_code', 'ASC')
        ->orderBy('name', 'ASC')
        ->orderBy('created_at', 'DESC')
        ->get();
        $queryCount = Mst_customer::where('active','=','Y')
        ->count();
        $data = [
            'customers' => $query,
            'title' => $this->title,
            'folder' => $this->folder,
            'rowCount' => $queryCount
        ];

        return view('adm.'.$this->folder.'.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $province = Mst_province::where([
            'country_id' => 9999,
            'active' => 'Y'
        ])
        ->orderBy('province_name', 'ASC')
        ->get();

        $city = [];
        $cityNPWP = [];
        $districts = [];
        $districtsNPWP = [];
        $subdistricts = [];
        $subdistrictsNPWP = [];
        if (old('province_id')) {
            $city = Mst_city::where([
                'province_id' => old('province_id'),
                'active' => 'Y'
            ])
            ->orderBy('city_name', 'ASC')
            ->get();
        }
        if (old('city_id')) {
            $districts = Mst_district::where([
                'city_id' => old('city_id'),
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
        }
        if (old('district_id')) {
            $subdistricts = Mst_sub_district::where([
                'district_id' => old('district_id'),
                'active' => 'Y'
            ])
            ->orderBy('sub_district_name', 'ASC')
            ->get();
        }

        if (old('same_as_officeaddress') == 'on') {
            if (old('province_id')) {
                $cityNPWP = Mst_city::where([
                    'province_id' => old('province_id'),
                    'active' => 'Y'
                ])
                ->orderBy('city_name', 'ASC')
                ->get();
            }
            if (old('city_id')) {
                $districtsNPWP = Mst_district::where([
                    'city_id' => old('city_id'),
                    'active' => 'Y'
                ])
                ->orderBy('district_name', 'ASC')
                ->get();
            }
            if (old('district_id')) {
                $subdistrictsNPWP = Mst_sub_district::where([
                    'district_id' => old('district_id'),
                    'active' => 'Y'
                ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
            }
        }else{
            if (old('npwp_province_id')) {
                $cityNPWP = Mst_city::where([
                    'province_id' => old('npwp_province_id'),
                    'active' => 'Y'
                ])
                ->orderBy('city_name', 'ASC')
                ->get();
            }
            if (old('npwp_city_id')) {
                $districtsNPWP = Mst_district::where([
                    'city_id' => old('npwp_city_id'),
                    'active' => 'Y'
                ])
                ->orderBy('district_name', 'ASC')
                ->get();
            }
            if (old('npwp_district_id')) {
                $subdistrictsNPWP = Mst_sub_district::where([
                    'district_id' => old('npwp_district_id'),
                    'active' => 'Y'
                ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
            }
        }

        $entityType = Mst_global::where([
            'data_cat' => 'entity-type',
            'active' => 'Y'
        ])
        ->orderBy('order_no', 'ASC')
        ->get();

        $branches = [];
        $salesman = [];
        $userInfo = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if($userInfo){
            if($userInfo->is_director=='Y' || Auth::user()->id==1){
                // direktur, superuser
                $branches = Mst_branch::where([
                    'active' => 'Y'
                ])
                ->orderBy('name','ASC')
                ->get();
            }else{
                // kepala cabang, karyawan biasa
                $branches = Mst_branch::where([
                    'id' => $userInfo->branch_id,
                    'active' => 'Y',
                ])
                ->orderBy('name','ASC')
                ->get();
            }
        }
        if (old('branch_id')) {
            if($userInfo){
                if($userInfo->is_director=='Y' || $userInfo->is_branch_head=='Y' || $userInfo->user_id==Auth::user()->id){
                    // direktur, kepala cabang, superuser
                    $salesman = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                    ->select(
                        'userdetails.user_id',
                        'users.name AS salesman_name'
                    )
                    ->where('userdetails.branch_id','=',old('branch_id'))
                    ->where('userdetails.is_salesman','=','Y')
                    ->where('userdetails.active','=','Y')
                    ->get();
                }else{
                    // karyawan biasa
                    $salesman = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                    ->select(
                        'userdetails.user_id',
                        'users.name AS salesman_name'
                    )
                    ->where('userdetails.user_id','=',Auth::user()->id)
                    ->where('userdetails.branch_id','=',old('branch_id'))
                    ->where('userdetails.is_salesman','=','Y')
                    ->where('userdetails.active','=','Y')
                    ->get();
                }
            }
        }

        $data = [
            'province' => $province,
            'cities' => $city,
            'citiesNPWP' => $cityNPWP,
            'districts' => $districts,
            'districtsNPWP' => $districtsNPWP,
            'subdistricts' => $subdistricts,
            'subdistrictsNPWP' => $subdistrictsNPWP,
            'entityType' => $entityType,
            'salesman' => $salesman,
            'title' => $this->title,
            'folder' => $this->folder,
            'totalRow' => 0,
            'branches' => $branches,
        ];

        return view('adm.'.$this->folder.'.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 19,
            'user_id' => Auth::user()->id,
            'user_access_read' => 'Y',
        ])
        ->first();
        if (!$qCheckPriv){
            return redirect()
            ->back()
            ->withInput()
            ->with('status-error', ENV('ERR_MSG_02')?ENV('ERR_MSG_02'):'You are not allowed to access this page!');
        }

        $validateInput = [
            'entityType_id' => 'required|numeric',
            'customerName' => 'required|max:255',
            'cust_unique_code' => 'required|not_regex:/[^A-Z0-9]/i|size:5|unique:App\Models\Mst_customer,customer_unique_code',
            'office_address' => 'required|max:1024',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'district_id' => 'required|numeric',
            'subdistrict_id' => 'required|numeric',
            'branch_id' => 'required|numeric',
            'postcode' => 'required|max:6',
            'customer_email' => 'max:64|email:rfc|nullable',
            'phone1' => ['max:32', new PhoneNumber, 'nullable'],
            'phone2' => ['max:32', new PhoneNumber, 'nullable'],
            'pic1Name' => 'required|max:255',
            'picphone1' => ['max:32', new PhoneNumber, 'nullable'],
            'pic_email1' => 'max:64|email:rfc|nullable',
            'pic2Name' => 'max:255',
            'pic2phone' => ['max:32', new PhoneNumber],
            'pic_email2' => 'max:64|email:rfc|nullable',
            'npwp_no' => 'max:24',
            'npwp_address' => 'max:1024',
            // 'npwp_province_id' => 'required',
            // 'npwp_city_id' => 'required',
            // 'npwp_district_id' => 'required',
            // 'npwp_subdistrict_id' => 'required',
            'credit_limit' => ['required', new NumericCustom('Credit Limit')],
            // 'credit_limit' => 'required|numeric',
            // 'credit_limit' => 'required|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            // 'limit_balance' => 'required|numeric',
            // 'limit_balance' => 'required|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            'beginning_balance' => ['nullable', new NumericCustom('Beginning Balance')],
            'top_in_day' => 'required|numeric',
            'salesman_id' => 'required|numeric',
            'salesman_id2' => 'different:salesman_id|nullable',
        ];
        $errMsg = [
            'entityType_id.numeric' => 'Please select a valid entity type',
            'cust_unique_code.not_regex' => 'The customer unique code is invalid.',
            'cust_unique_code.unique' => 'The customer unique code has already been taken.',
            'cust_unique_code.size' => 'The customer unique code must be 5 characters.',
            'province_id.numeric' => 'Please select a valid province',
            'city_id.numeric' => 'Please select a valid city',
            'district_id.numeric' => 'Please select a valid district',
            'subdistrict_id.numeric' => 'Please select a valid sub district',
            'branch_id.numeric' => 'Please select a valid related branch',
            'salesman_id.numeric' => 'Please select a valid salesman #1',
            'salesman_id2.different' => 'The salesman id #2 and salesman id #1 must be different.',
            'credit_limit.regex' => 'The credit limit format is invalid. ' .
                'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
            // 'limit_balance.regex' => 'The credit limit format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789'
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['same_as_officeaddress_shipment'.$i] == 'on') {
                    $validateShipmentInput = [];
                    $errShipmentMsg = [];
                } else {
                    $validateShipmentInput = [
                        'address_addr'.$i => 'required|max:1024',
                        'province_id_addr'.$i => 'required|numeric',
                        'city_id_addr'.$i => 'required|numeric',
                        'district_id_addr'.$i => 'required|numeric',
                        'sub_district_id_addr'.$i => 'required|numeric',
                        'phone_addr'.$i => ['max:32', new PhoneNumber, 'nullable'],
                        'post_code_shipment'.$i => 'max:6|nullable',
                    ];
                    $errShipmentMsg = [
                        'province_id_addr'.$i.'.numeric' => 'Please select a valid province',
                        'city_id_addr'.$i.'.numeric' => 'Please select a valid city',
                        'district_id_addr'.$i.'.numeric' => 'Please select a valid district',
                        'sub_district_id_addr'.$i.'.numeric' => 'Please select a valid sub district',
                        'phone_addr'.$i.'.required' => 'The phone field is required',
                        'address_addr'.$i.'.required' => 'The phone field is required',
                        'post_code_shipment'.$i.'.required' => 'The postcode field is required',
                    ];
                }
                $validateInput = array_merge($validateInput, $validateShipmentInput);
                $errMsg = array_merge($errMsg, $errShipmentMsg);
            }
        }
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )->validate();

        $npwp_address = is_null($request->npwp_address) ? null : $request->npwp_address;
        $npwp_province_id = is_numeric($request->npwp_province_id) ? $request->npwp_province_id : null;
        $npwp_city_id = is_numeric($request->npwp_city_id) ? $request->npwp_city_id : null;
        $npwp_district_id = is_numeric($request->npwp_district_id) ? $request->npwp_district_id : null;
        $npwp_sub_district_id = is_numeric($request->npwp_subdistrict_id) ? $request->npwp_subdistrict_id : null;
        if ($request->same_as_officeaddress == 'on') {
            $npwp_address = $request->office_address;
            $npwp_province_id = $request->province_id;
            $npwp_city_id = $request->city_id;
            $npwp_district_id = $request->district_id;
            $npwp_sub_district_id = $request->subdistrict_id;
        }

        $salesmanBranch = Userdetail::where('user_id','=',$request->salesman_id)
        ->first();

        $ins = Mst_customer::create([
            'entity_type_id' => $request->entityType_id,
            'customer_unique_code' => $request->cust_unique_code,
            'name' => $request->customerName,
            'office_address' => $request->office_address,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'district_id' => $request->district_id,
            'sub_district_id' => $request->subdistrict_id,
            'post_code' => $request->postcode,
            'cust_email' => $request->customer_email,
            'branch_id' => $request->branch_id,
            // 'branch_id' => $salesmanBranch->branch_id,
            'phone1' => $request->phone1,
            'phone2' => $request->phone2,
            'pic1_name' => $request->pic1Name,
            'pic1_phone' => $request->picphone1,
            'pic1_email' => $request->pic_email1,
            'pic2_name' => $request->pic2Name,
            'pic2_phone' => $request->picphone2,
            'pic2_email' => $request->pic_email2,
            'npwp_no' => (is_null($request->npwp_no) ? '-' : $request->npwp_no),
            'npwp_address' => $npwp_address,
            'npwp_province_id' => $npwp_province_id,
            'npwp_city_id' => $npwp_city_id,
            'npwp_district_id' => $npwp_district_id,
            'npwp_sub_district_id' => $npwp_sub_district_id,
            'credit_limit' => GlobalFuncHelper::moneyValidate($request->credit_limit),
            // 'limit_balance' => GlobalFuncHelper::moneyValidate($request->limit_balance),
            'top' => $request->top_in_day,
            'salesman_id' => $request->salesman_id,
            'salesman_id2' => is_numeric($request->salesman_id2) ? $request->salesman_id2 : 0,
            'customer_status' => $request->cust_status,
            'payment_status' => $request->payment_status,
            'beginning_balance' => ($request->beginning_balance==''?0:GlobalFuncHelper::moneyValidate($request->beginning_balance)),
            'active' => 'Y',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        // get last ID
        $maxId = Mst_customer::max('id');

        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                $address = $request['address_addr'.$i];
                $province_id = $request['province_id_addr'.$i];
                $city_id = $request['city_id_addr'.$i];
                $district_id = $request['district_id_addr'.$i];
                $sub_district_id = $request['sub_district_id_addr'.$i];
                $post_code = $request['post_code_shipment'.$i];
                $phone_addr = $request['phone_addr'.$i];
                if ($request['same_as_officeaddress_shipment'.$i] == 'on') {
                    $address = $request->office_address;
                    $province_id = $request->province_id;
                    $city_id = $request->city_id;
                    $district_id = $request->district_id;
                    $sub_district_id = $request->subdistrict_id;
                    $post_code = $request->postcode;
                    $phone_addr = $request->phone1;
                }

                $insCustShipment = Mst_customer_shipment_address::create([
                    'customer_id' => $maxId,
                    'address' => $address,
                    'province_id' => $province_id,
                    'city_id' => $city_id,
                    'district_id' => $district_id,
                    'sub_district_id' => $sub_district_id,
                    'post_code' => $post_code,
                    'phone' => $phone_addr,
                    'active' => 'Y',
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id
                ]);
            }
        }

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_customer  $Mst_customer
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $query = Mst_customer::where('slug', '=', urldecode($slug))
        ->first();
        if ($query) {
            $province = Mst_province::where([
                'country_id' => 9999,
                'active' => 'Y'
            ])
            ->orderBy('province_name', 'ASC')
            ->get();
            $city = Mst_city::where([
                'province_id' => $query->province_id,
                'active' => 'Y'
            ])
            ->orderBy('city_name', 'ASC')
            ->get();
            if (old('province_id')) {
                $city = Mst_city::where([
                    'province_id' => old('province_id'),
                    'active' => 'Y'
                ])
                ->orderBy('city_name', 'ASC')
                ->get();
            }
            $cityNPWP = Mst_city::where([
                'province_id' => $query->npwp_province_id,
                'active' => 'Y'
            ])
            ->orderBy('city_name', 'ASC')
            ->get();
            if (old('npwp_province_id')) {
                $cityNPWP = Mst_city::where([
                    'province_id' => old('npwp_province_id'),
                    'active' => 'Y'
                ])
                ->orderBy('city_name', 'ASC')
                ->get();
            }

            $districts = Mst_district::where([
                'city_id' => $query->city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
            if (old('city_id')) {
                $districts = Mst_district::where([
                    'city_id' => old('city_id'),
                    'active' => 'Y'
                ])
                ->orderBy('district_name', 'ASC')
                ->get();
            }
            $districtsNPWP = Mst_district::where([
                'city_id' => $query->npwp_city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
            if (old('npwp_city_id')) {
                $districtsNPWP = Mst_district::where([
                    'city_id' => old('npwp_city_id'),
                    'active' => 'Y'
                ])
                ->orderBy('district_name', 'ASC')
                ->get();
            }

            $subdistricts = Mst_sub_district::where([
                'district_id' => $query->district_id,
                'active' => 'Y'
            ])
            ->orderBy('sub_district_name', 'ASC')
            ->get();
            if (old('district_id')) {
                $subdistricts = Mst_sub_district::where([
                    'district_id' => old('district_id'),
                    'active' => 'Y'
                ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
            }
            $subdistrictsNPWP = Mst_sub_district::where([
                'district_id' => $query->npwp_district_id,
                'active' => 'Y'
            ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
            if (old('npwp_district_id')) {
                $subdistrictsNPWP = Mst_sub_district::where([
                    'district_id' => old('npwp_district_id'),
                    'active' => 'Y'
                ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
            }

            $entityType = Mst_global::where([
                'data_cat' => 'entity-type',
                'active' => 'Y'
            ])
            ->orderBy('order_no', 'ASC')
            ->get();

            $salesman = User::leftJoin('userdetails','users.id','=','userdetails.user_id')
            ->where('userdetails.is_salesman','=','Y')
            ->orderBy('users.name','ASC')
            ->get();

            $queryShipment = Mst_customer_shipment_address::where([
                'customer_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $count = Mst_customer_shipment_address::where([
                'customer_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();
            if (old('totalRow')) {
                $count = old('totalRow');
                $queryShipment = [];
            }

            $branches = Mst_branch::where([
                'active' => 'Y'
            ])
            ->orderBy('name','ASC')
            ->get();

            $data = [
                'province' => $province,
                'cities' => $city,
                'citiesNPWP' => $cityNPWP,
                'districts' => $districts,
                'districtsNPWP' => $districtsNPWP,
                'subdistricts' => $subdistricts,
                'subdistrictsNPWP' => $subdistrictsNPWP,
                'entityType' => $entityType,
                'salesman' => $salesman,
                'cust' => $query,
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => old('totalRow') ? old('totalRow') : $count,
                'queryShipment' => $queryShipment,
                'branches' => $branches,
            ];

            $i = 0;
            foreach ($queryShipment as $qs) {
                $cityShipment = Mst_city::where([
                    'province_id' => $qs->province_id,
                    'active' => 'Y'
                ])
                    ->orderBy('city_name', 'ASC')
                    ->get();
                $districtShipment = Mst_district::where([
                    'city_id' => $qs->city_id,
                    'active' => 'Y'
                ])
                    ->orderBy('district_name', 'ASC')
                    ->get();
                $subdistrictShipment = Mst_sub_district::where([
                    'district_id' => $qs->district_id,
                    'active' => 'Y'
                ])
                    ->orderBy('sub_district_name', 'ASC')
                    ->get();
                $data = array_merge($data, [
                    'cityShipment'.$i => $cityShipment,
                    'districtShipment'.$i => $districtShipment,
                    'subdistrictShipment'.$i => $subdistrictShipment,
                ]);
                $i += 1;
            }

            return view('adm.'.$this->folder.'.show', $data);
        } else {
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_customer  $Mst_customer
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $query = Mst_customer::where('slug', '=', urldecode($slug))
        ->first();
        if ($query) {
            $province = Mst_province::where([
                'country_id' => 9999,
                'active' => 'Y'
            ])
            ->orderBy('province_name', 'ASC')
            ->get();
            $city = Mst_city::where([
                'province_id' => $query->province_id,
                'active' => 'Y'
            ])
            ->orderBy('city_name', 'ASC')
            ->get();
            if (old('province_id')) {
                $city = Mst_city::where([
                    'province_id' => old('province_id'),
                    'active' => 'Y'
                ])
                ->orderBy('city_name', 'ASC')
                ->get();
            }
            $cityNPWP = Mst_city::where([
                'province_id' => $query->npwp_province_id,
                'active' => 'Y'
            ])
            ->orderBy('city_name', 'ASC')
            ->get();

            $districts = Mst_district::where([
                'city_id' => $query->city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
            if (old('city_id')) {
                $districts = Mst_district::where([
                    'city_id' => old('city_id'),
                    'active' => 'Y'
                ])
                ->orderBy('district_name', 'ASC')
                ->get();
            }
            $districtsNPWP = Mst_district::where([
                'city_id' => $query->npwp_city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();

            $subdistricts = Mst_sub_district::where([
                'district_id' => $query->district_id,
                'active' => 'Y'
            ])
            ->orderBy('sub_district_name', 'ASC')
            ->get();
            if (old('district_id')) {
                $subdistricts = Mst_sub_district::where([
                    'district_id' => old('district_id'),
                    'active' => 'Y'
                ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
            }
            $subdistrictsNPWP = Mst_sub_district::where([
                'district_id' => $query->npwp_district_id,
                'active' => 'Y'
            ])
            ->orderBy('sub_district_name', 'ASC')
            ->get();

            if (old('same_as_officeaddress') == 'on') {
                if (old('province_id')) {
                    $cityNPWP = Mst_city::where([
                        'province_id' => old('province_id'),
                        'active' => 'Y'
                    ])
                    ->orderBy('city_name', 'ASC')
                    ->get();
                }
                if (old('city_id')) {
                    $districtsNPWP = Mst_district::where([
                        'city_id' => old('city_id'),
                        'active' => 'Y'
                    ])
                    ->orderBy('district_name', 'ASC')
                    ->get();
                }
                if (old('district_id')) {
                    $subdistrictsNPWP = Mst_sub_district::where([
                        'district_id' => old('district_id'),
                        'active' => 'Y'
                    ])
                    ->orderBy('sub_district_name', 'ASC')
                    ->get();
                }
            }else{
                if (old('npwp_province_id')) {
                    $cityNPWP = Mst_city::where([
                        'province_id' => old('npwp_province_id'),
                        'active' => 'Y'
                    ])
                    ->orderBy('city_name', 'ASC')
                    ->get();
                }
                if (old('npwp_city_id')) {
                    $districtsNPWP = Mst_district::where([
                        'city_id' => old('npwp_city_id'),
                        'active' => 'Y'
                    ])
                    ->orderBy('district_name', 'ASC')
                    ->get();
                }
                if (old('npwp_district_id')) {
                    $subdistrictsNPWP = Mst_sub_district::where([
                        'district_id' => old('npwp_district_id'),
                        'active' => 'Y'
                    ])
                    ->orderBy('sub_district_name', 'ASC')
                    ->get();
                }
            }

            $entityType = Mst_global::where([
                'data_cat' => 'entity-type',
                'active' => 'Y'
            ])
            ->orderBy('order_no', 'ASC')
            ->get();

            $branches = [];
            $salesman = [];
            $userInfo = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();
            if($userInfo){
                if($userInfo->is_director=='Y' || Auth::user()->id==1){
                    // direktur, superuser
                    $branches = Mst_branch::where([
                        'active' => 'Y'
                    ])
                    ->orderBy('name','ASC')
                    ->get();
                }else{
                    // kepala cabang, karyawan biasa
                    $branches = Mst_branch::where([
                        'id' => $userInfo->branch_id,
                        'active' => 'Y',
                    ])
                    ->orderBy('name','ASC')
                    ->get();
                }
            }
            if (old('branch_id')) {
                if($userInfo){
                    if($userInfo->is_director=='Y' || $userInfo->is_branch_head=='Y' || $userInfo->user_id==Auth::user()->id){
                        // direktur, kepala cabang, superuser
                        $salesman = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                        ->select(
                            'userdetails.user_id',
                            'users.name AS salesman_name'
                        )
                        ->where('userdetails.branch_id','=',old('branch_id'))
                        ->where('userdetails.is_salesman','=','Y')
                        ->where('userdetails.active','=','Y')
                        ->get();
                    }else{
                        // karyawan biasa
                        $salesman = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                        ->select(
                            'userdetails.user_id',
                            'users.name AS salesman_name'
                        )
                        ->where('userdetails.user_id','=',Auth::user()->id)
                        ->where('userdetails.branch_id','=',old('branch_id'))
                        ->where('userdetails.is_salesman','=','Y')
                        ->where('userdetails.active','=','Y')
                        ->get();
                    }
                }
            }else{
                if($userInfo->is_director=='Y' || $userInfo->is_branch_head=='Y' || $userInfo->user_id==Auth::user()->id){
                    // direktur, kepala cabang, superuser
                    $salesman = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                    ->select(
                        'userdetails.user_id',
                        'users.name AS salesman_name'
                    )
                    ->where('userdetails.branch_id','=',$query->branch_id)
                    ->where('userdetails.is_salesman','=','Y')
                    ->where('userdetails.active','=','Y')
                    ->get();
                }else{
                    // karyawan biasa
                    $salesman = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                    ->select(
                        'userdetails.user_id',
                        'users.name AS salesman_name'
                    )
                    ->where('userdetails.user_id','=',Auth::user()->id)
                    ->where('userdetails.branch_id','=',$query->branch_id)
                    ->where('userdetails.is_salesman','=','Y')
                    ->where('userdetails.active','=','Y')
                    ->get();
                }
            }

            $queryShipment = Mst_customer_shipment_address::where([
                'customer_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $count = Mst_customer_shipment_address::where([
                'customer_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();
            if (old('totalRow')) {
                $count = old('totalRow');
                $queryShipment = [];
            }

            $data = [
                'province' => $province,
                'cities' => $city,
                'citiesNPWP' => $cityNPWP,
                'districts' => $districts,
                'districtsNPWP' => $districtsNPWP,
                'subdistricts' => $subdistricts,
                'subdistrictsNPWP' => $subdistrictsNPWP,
                'entityType' => $entityType,
                'salesman' => $salesman,
                'cust' => $query,
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => old('totalRow') ? old('totalRow') : $count,
                'queryShipment' => $queryShipment,
                'branches' => $branches,
            ];

            return view('adm.'.$this->folder.'.edit', $data);
        } else {
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_customer  $Mst_customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 19,
            'user_id' => Auth::user()->id,
            'user_access_read' => 'Y',
        ])
        ->first();
        if (!$qCheckPriv){
            return redirect()
            ->back()
            ->withInput()
            ->with('status-error', ENV('ERR_MSG_02')?ENV('ERR_MSG_02'):'You are not allowed to access this page!');
        }
        
        $validateInput = [
            'entityType_id' => 'required|numeric',
            'customerName' => 'required|max:255',
            'cust_unique_code' => ['required','not_regex:/[^A-Z0-9]/i','size:5',new CustCodeUnique($request->cust_unique_code)],
            'office_address' => 'required|max:1024',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'district_id' => 'required|numeric',
            'subdistrict_id' => 'required|numeric',
            'postcode' => 'required|max:6',
            'customer_email' => 'max:64|email:rfc|nullable',
            'branch_id' => 'required|numeric',
            'phone1' => ['max:32', new PhoneNumber, 'nullable'],
            'phone2' => ['max:32', new PhoneNumber, 'nullable'],
            'pic1Name' => 'required|max:255',
            'picphone1' => ['max:32', new PhoneNumber, 'nullable'],
            'pic_email1' => 'max:64|email:rfc|nullable',
            'pic2Name' => 'max:255',
            'pic2phone' => ['max:32', new PhoneNumber],
            'pic_email2' => 'max:64|email:rfc|nullable',
            'npwp_no' => 'max:24',
            'npwp_address' => 'max:1024',
            // 'npwp_province_id' => 'required',
            // 'npwp_city_id' => 'required',
            // 'npwp_district_id' => 'required',
            // 'npwp_subdistrict_id' => 'required',
            'credit_limit' => ['required', new NumericCustom('Credit Limit')],
            // 'credit_limit' => 'required|numeric',
            // 'credit_limit' => 'required|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            // 'limit_balance' => 'required|numeric',
            // 'limit_balance' => 'required|regex:/^[0-9]{1,3}(.[0-9]{3})*\,[0-9]+$/',
            'beginning_balance' => ['nullable', new NumericCustom('Beginning Balance')],
            'top_in_day' => 'required|numeric',
            'salesman_id' => 'required|numeric',
            'salesman_id2' => 'different:salesman_id|nullable',
        ];
        $errMsg = [
            'entityType_id.numeric' => 'Please select a valid entity type',
            'cust_unique_code.not_regex' => 'The customer unique code is invalid.',
            'cust_unique_code.size' => 'The customer unique code must be 5 characters.',
            'province_id.numeric' => 'Please select a valid province',
            'city_id.numeric' => 'Please select a valid city',
            'district_id.numeric' => 'Please select a valid district',
            'subdistrict_id.numeric' => 'Please select a valid sub district',
            'branch_id.numeric' => 'Please select a valid related branch',
            'salesman_id.numeric' => 'Please select a valid salesman',
            'salesman_id2.different' => 'The salesman id #2 and salesman id #1 must be different.',
            'credit_limit.regex' => 'The credit limit format is invalid. ' .
                'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789',
            // 'limit_balance.regex' => 'The credit limit format is invalid. ' .
            //     'Must use comma as thousand separator and point as decimal separator. Example: 123.456,789'
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if(isset($request['shipmentId'.$i])){
                    if ($request['same_as_officeaddress_shipment'.$i] == 'on') {
                        $validateShipmentInput = [];
                        $errShipmentMsg = [];
                    } else {
                        $validateShipmentInput = [
                            'address_addr'.$i => 'required|max:1024',
                            'province_id_addr'.$i => 'required|numeric',
                            'city_id_addr'.$i => 'required|numeric',
                            'district_id_addr'.$i => 'required|numeric',
                            'sub_district_id_addr'.$i => 'required|numeric',
                            'post_code_shipment'.$i => 'max:6|nullable',
                            'phone_addr'.$i => ['max:32', new PhoneNumber, 'nullable'],
                        ];
                        $errShipmentMsg = [
                            'province_id_addr'.$i.'.numeric' => 'Please select a valid province',
                            'city_id_addr'.$i.'.numeric' => 'Please select a valid city',
                            'district_id_addr'.$i.'.numeric' => 'Please select a valid district',
                            'sub_district_id_addr'.$i.'.numeric' => 'Please select a valid sub district',
                            'phone_addr'.$i.'.required' => 'The phone field is required',
                            'post_code_shipment'.$i.'.required' => 'The postcode field is required',
                            'address_addr'.$i.'.required' => 'The phone field is required',
                        ];
                    }
                    $validateInput = array_merge($validateInput, $validateShipmentInput);
                    $errMsg = array_merge($errMsg, $errShipmentMsg);
                }
            }
        }
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )->validate();

        $salesmanBranch = Userdetail::where('user_id','=',$request->salesman_id)
        ->first();

        $npwp_address = is_null($request->npwp_address) ? null : $request->npwp_address;
        $npwp_province_id = is_numeric($request->npwp_province_id) ? $request->npwp_province_id : null;
        $npwp_city_id = is_numeric($request->npwp_city_id) ? $request->npwp_city_id : null;
        $npwp_district_id = is_numeric($request->npwp_district_id) ? $request->npwp_district_id : null;
        $npwp_sub_district_id = is_numeric($request->npwp_subdistrict_id) ? $request->npwp_subdistrict_id : null;
        if ($request->same_as_officeaddress == 'on') {
            $npwp_address = $request->office_address;
            $npwp_province_id = $request->province_id;
            $npwp_city_id = $request->city_id;
            $npwp_district_id = $request->district_id;
            $npwp_sub_district_id = $request->subdistrict_id;
        }

        $qCust = Mst_customer::where('slug', '=', urldecode($slug))
        ->first();

        $upd = Mst_customer::where('slug', '=', urldecode($slug))
        ->update([
            'entity_type_id' => $request->entityType_id,
            'name' => $request->customerName,
            'customer_unique_code' => $request->cust_unique_code,
            'office_address' => $request->office_address,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'district_id' => $request->district_id,
            'sub_district_id' => $request->subdistrict_id,
            'post_code' => $request->postcode,
            'cust_email' => $request->customer_email,
            'branch_id' => $request->branch_id,
            // 'branch_id' => $salesmanBranch->branch_id,
            'phone1' => $request->phone1,
            'phone2' => $request->phone2,
            'pic1_name' => $request->pic1Name,
            'pic1_phone' => $request->picphone1,
            'pic1_email' => $request->pic_email1,
            'pic2_name' => $request->pic2Name,
            'pic2_phone' => $request->picphone2,
            'pic2_email' => $request->pic_email2,
            'npwp_no' => (is_null($request->npwp_no) ? '-' : $request->npwp_no),
            'npwp_address' => $npwp_address,
            'npwp_province_id' => !is_null($npwp_province_id) ? $npwp_province_id : 9999,
            'npwp_city_id' => $npwp_city_id,
            'npwp_district_id' => $npwp_district_id,
            'npwp_sub_district_id' => $npwp_sub_district_id,
            'credit_limit' => GlobalFuncHelper::moneyValidate($request->credit_limit),
            // 'limit_balance' => GlobalFuncHelper::moneyValidate($request->limit_balance),
            'top' => $request->top_in_day,
            'salesman_id' => $request->salesman_id,
            'salesman_id2' => is_numeric($request->salesman_id2) ? $request->salesman_id2 : 0,
            'customer_status' => $request->cust_status,
            'payment_status' => $request->payment_status,
            'beginning_balance' => ($request->beginning_balance==''?0:GlobalFuncHelper::moneyValidate($request->beginning_balance)),
            'active' => 'Y',
            'updated_by' => Auth::user()->id
        ]);

        // set not active
        $updShipment = Mst_customer_shipment_address::where([
            'customer_id' => $qCust->id
        ])->update([
            'active' => 'N'
        ]);

        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if(isset($request['shipmentId'.$i])){
                    $address = $request['address_addr'.$i];
                    $province_id = $request['province_id_addr'.$i];
                    $city_id = $request['city_id_addr'.$i];
                    $district_id = $request['district_id_addr'.$i];
                    $sub_district_id = $request['sub_district_id_addr'.$i];
                    $post_code = $request['post_code_shipment'.$i];
                    $phone_addr = $request['phone_addr'.$i];
                    if ($request['same_as_officeaddress_shipment'.$i] == 'on') {
                        $address = $request->office_address;
                        $province_id = $request->province_id;
                        $city_id = $request->city_id;
                        $district_id = $request->district_id;
                        $sub_district_id = $request->subdistrict_id;
                        $post_code = $request->postcode;
                        $phone_addr = $request->phone1;
                    }

                    if ($request['shipmentId'.$i]=='0') {
                        $insCustShipment = Mst_customer_shipment_address::create([
                            'customer_id' => $qCust->id,
                            'address' => $address,
                            'province_id' => $province_id,
                            'city_id' => $city_id,
                            'district_id' => $district_id,
                            'sub_district_id' => $sub_district_id,
                            'post_code' => $post_code,
                            'phone' => $phone_addr,
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);
                    } else {
                        $updCustShipment = Mst_customer_shipment_address::where('id', '=', $request['shipmentId'.$i])
                        ->update([
                            'customer_id' => $qCust->id,
                            'address' => $address,
                            'province_id' => $province_id,
                            'city_id' => $city_id,
                            'district_id' => $district_id,
                            'sub_district_id' => $sub_district_id,
                            'post_code' => $post_code,
                            'phone' => $phone_addr,
                            'active' => 'Y',
                            'updated_by' => Auth::user()->id
                        ]);
                    }
                }
            }
        }

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_customer  $Mst_customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_customer $Mst_customer)
    {
        //
    }
}
