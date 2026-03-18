<?php

namespace App\Http\Controllers\adm;

use App\Http\Controllers\Controller;
use App\Models\Mst_courier;
use App\Models\Mst_courier_bank_information;
use App\Models\Mst_province;
use App\Models\Mst_city;
use App\Models\Mst_global;
use App\Models\Mst_district;
use App\Models\Mst_sub_district;
use App\Models\Mst_menu_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use App\Rules\PhoneNumber;

class CourierController extends Controller
{
    protected $title = 'Courier';
    protected $folder = 'courier';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_courier::where('active','=','Y')
        ->orderBy('name', 'ASC')
        ->get();
        $queryCount = Mst_courier::where('active','=','Y')
        ->count();
        $data = [
            'couriers' => $query,
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
        $country_id = 9999;
        $province = Mst_province::where([
            'country_id' => $country_id,
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
        ->orderBy('title_ind', 'ASC')
        ->get();
        $courierType = Mst_global::where([
            'data_cat' => 'courier-type',
            'active' => 'Y'
        ])
        ->orderBy('title_ind', 'ASC')
        ->get();
        $currency = Mst_global::where([
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->orderBy('title_ind', 'ASC')
        ->get();
        $data = [
            'province' => $province,
            'cities' => $city,
            'districts' => $districts,
            'subdistricts' => $subdistricts,
            'citiesNPWP' => $cityNPWP,
            'districtsNPWP' => $districtsNPWP,
            'subdistrictsNPWP' => $subdistrictsNPWP,
            'entityType' => $entityType,
            'courierType' => $courierType,
            'currency' => $currency,
            'title' => $this->title,
            'folder' => $this->folder,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0)
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
            'menu_id' => 23,
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
            'courierName' => 'required|max:255',
            'office_address' => 'required|max:1024',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'district_id' => 'required|numeric',
            'subdistrict_id' => 'required|numeric',
            'postcode' => 'max:6|nullable',
            'courier_email' => 'max:64|email:rfc|nullable',
            'phone1' => ['max:32', new PhoneNumber, 'nullable'],
            'phone2' => ['max:32', new PhoneNumber, 'nullable'],
            'pic1Name' => 'required|max:255',
            'picphone1' => ['max:32', new PhoneNumber, 'nullable'],
            'pic_email1' => 'max:64|email:rfc|nullable',
            'npwp_no' => 'max:24|nullable',
            'npwp_address' => 'max:1024|nullable|required_with:npwp_no',
            'npwp_province_id' => 'nullable|required_with:npwp_no',
            'npwp_city_id' => 'nullable|required_with:npwp_no',
            'npwp_district_id' => 'nullable|required_with:npwp_no',
            'npwp_subdistrict_id' => 'nullable|required_with:npwp_no',
        ];
        $errMsg = [
            'entityType_id.numeric' => 'please select a valid entity type.',
            'province_id.numeric' => 'please select a valid province.',
            'city_id.numeric' => 'please select a valid city.',
            'district_id.numeric' => 'please select a valid district.',
            'subdistrict_id.numeric' => 'please select a valid sub district.',
            'pic1Name.required' => 'the pic name field is required.',
            'picphone1.required' => 'the pic phone field is required.',
            'pic_email1.required' => 'the pic email field is required.',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if (isset($request['bank_name'.$i])) {
                    $validateShipmentInput = [
                        'bank_name'.$i => 'required|max:255',
                        'bank_address'.$i => 'required|max:1024',
                        'account_name'.$i => 'required|max:255',
                        'account_no'.$i => 'required|max:255',
                        'currency_bank_id'.$i => 'required|numeric',
                        'swift_code'.$i => 'max:255|nullable',
                        'bsb_code'.$i => 'max:255|nullable',
                    ];
                    $errShipmentMsg = [
                        'bank_name'.$i.'.required' => 'the bank name field is required.',
                        'bank_address'.$i.'.required' => 'the bank address field is required.',
                        'account_name'.$i.'.required' => 'the account name field is required.',
                        'account_no'.$i.'.required' => 'the account no field is required.',
                        'currency_bank_id'.$i.'.numeric' => 'please select a valid currency',
                        'swift_code'.$i.'.required' => 'the swift code field is required.',
                        'bsb_code'.$i.'.required' => 'the bsb code field is required.',
                    ];
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

        $ins = Mst_courier::create([
            'entity_type_id' => $request->entityType_id,
            'name' => $request->courierName,
            'office_address' => $request->office_address,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'district_id' => $request->district_id,
            'sub_district_id' => $request->subdistrict_id,
            'post_code' => $request->postcode,
            'courier_email' => $request->courier_email,
            'phone1' => $request->phone1,
            'phone2' => $request->phone2,
            'pic1_name' => $request->pic1Name,
            'pic1_phone' => $request->picphone1,
            'pic1_email' => $request->pic_email1,
            'npwp_no' => $request->npwp_no,
            'npwp_address' => $npwp_address,
            'npwp_province_id' => $npwp_province_id,
            'npwp_city_id' => $npwp_city_id,
            'npwp_district_id' => $npwp_district_id,
            'npwp_sub_district_id' => $npwp_sub_district_id,
            'active' => 'Y',
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        // get last ID
        $maxId = Mst_courier::max('id');

        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['bank_name'.$i]) {
                    $insBank = Mst_courier_bank_information::create([
                        'courier_id' => $maxId,
                        'bank_name' => $request['bank_name'.$i],
                        'bank_address' => $request['bank_address'.$i],
                        'account_name' => $request['account_name'.$i],
                        'account_no' => $request['account_no'.$i],
                        'currency_id' => $request['currency_bank_id'.$i],
                        'swift_code' => $request['swift_code'.$i],
                        'bsb_code' => $request['bsb_code'.$i],
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                }
            }
        }

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_courier  $mst_courier
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $query = Mst_courier::where('slug', '=', urldecode($slug))->first();
        if ($query) {
            $country_id = 9999;
            $province = Mst_province::where([
                'country_id' => $country_id,
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
            $cityNPWP = [];
            $cityNPWP = Mst_city::where([
                'province_id' => $query->npwp_province_id,
                'active' => 'Y'
            ])
            ->orderBy('city_name', 'ASC')
            ->get();
            $districts = [];
            $districts = Mst_district::where([
                'city_id' => $query->city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
            $districtsNPWP = [];
            $districtsNPWP = Mst_district::where([
                'city_id' => $query->npwp_city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
            $subdistricts = [];
            $subdistricts = Mst_sub_district::where([
                'district_id' => $query->district_id,
                'active' => 'Y'
            ])
            ->orderBy('sub_district_name', 'ASC')
            ->get();
            $subdistrictsNPWP = [];
            $subdistrictsNPWP = Mst_sub_district::where([
                'district_id' => $query->npwp_district_id,
                'active' => 'Y'
            ])
            ->orderBy('sub_district_name', 'ASC')
            ->get();
            $entityType = Mst_global::where([
                'data_cat' => 'entity-type',
                'active' => 'Y'
            ])
            ->orderBy('title_ind', 'ASC')
            ->get();
            $courierType = Mst_global::where([
                'data_cat' => 'courier-type',
                'active' => 'Y'
            ])
            ->orderBy('title_ind', 'ASC')
            ->get();
            $currency = Mst_global::where([
                'data_cat' => 'currency',
                'active' => 'Y'
            ])
            ->orderBy('title_ind', 'ASC')
            ->get();
            $queryBank = Mst_courier_bank_information::where([
                'courier_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $count = Mst_courier_bank_information::where([
                'courier_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();
            $data = [
                'courier' => $query,
                'province' => $province,
                'cities' => $city,
                'districts' => $districts,
                'subdistricts' => $subdistricts,
                'citiesNPWP' => $cityNPWP,
                'districtsNPWP' => $districtsNPWP,
                'subdistrictsNPWP' => $subdistrictsNPWP,
                'entityType' => $entityType,
                'courierType' => $courierType,
                'currency' => $currency,
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => old('totalRow') ? old('totalRow') : $count,
                'queryBank' => $queryBank
            ];
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
     * @param  \App\Models\Mst_courier  $mst_courier
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $query = Mst_courier::where('slug', '=', urldecode($slug))
        ->first();
        if ($query) {
            $country_id = 9999;
            $province = Mst_province::where([
                'country_id' => $country_id,
                'active' => 'Y'
            ])
            ->orderBy('province_name', 'ASC')
            ->get();

            if (old('province_id')) {
                $city = Mst_city::where([
                    'province_id' => old('province_id'),
                    'active' => 'Y'
                ])
                ->orderBy('city_name', 'ASC')
                ->get();
            } else {
                $city = Mst_city::where([
                    'province_id' => $query->province_id,
                    'active' => 'Y'
                ])
                ->orderBy('city_name', 'ASC')
                ->get();
            }

            $districts = [];
            if (old('city_id')) {
                $districts = Mst_district::where([
                    'city_id' => old('city_id'),
                    'active' => 'Y'
                ])
                ->orderBy('district_name', 'ASC')
                ->get();
            } else {
                $districts = Mst_district::where([
                    'city_id' => $query->city_id,
                    'active' => 'Y'
                ])
                ->orderBy('district_name', 'ASC')
                ->get();
            }

            $subdistricts = [];
            if (old('district_id')) {
                $subdistricts = Mst_sub_district::where([
                    'district_id' => old('district_id'),
                    'active' => 'Y'
                ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
            } else {
                $subdistricts = Mst_sub_district::where([
                    'district_id' => $query->district_id,
                    'active' => 'Y'
                ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
            }

            $cityNPWP = Mst_city::where([
                'province_id' => $query->npwp_province_id,
                'active' => 'Y'
            ])
            ->orderBy('city_name', 'ASC')
            ->get();
            $districtsNPWP = Mst_district::where([
                'city_id' => $query->npwp_city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
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
            ->orderBy('title_ind', 'ASC')
            ->get();
            $courierType = Mst_global::where([
                'data_cat' => 'courier-type',
                'active' => 'Y'
            ])
            ->orderBy('title_ind', 'ASC')
            ->get();
            $currency = Mst_global::where([
                'data_cat' => 'currency',
                'active' => 'Y'
            ])
            ->orderBy('title_ind', 'ASC')
            ->get();

            $queryBank = Mst_courier_bank_information::where([
                'courier_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $count = Mst_courier_bank_information::where([
                'courier_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();
            if (old('totalRow')) {
                $count = old('totalRow');
                $queryBank = [];
            }

            $data = [
                'courier' => $query,
                'province' => $province,
                'cities' => $city,
                'districts' => $districts,
                'subdistricts' => $subdistricts,
                'citiesNPWP' => $cityNPWP,
                'districtsNPWP' => $districtsNPWP,
                'subdistrictsNPWP' => $subdistrictsNPWP,
                'entityType' => $entityType,
                'courierType' => $courierType,
                'currency' => $currency,
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => old('totalRow') ? old('totalRow') : $count,
                'queryBank' => $queryBank
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
     * @param  \App\Models\Mst_courier  $mst_courier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 23,
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
            'courierName' => 'required|max:255',
            'office_address' => 'required|max:1024',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'district_id' => 'required|numeric',
            'subdistrict_id' => 'required|numeric',
            'postcode' => 'max:6|nullable',
            'courier_email' => 'max:64|email:rfc|nullable',
            'phone1' => ['max:32', new PhoneNumber, 'nullable'],
            'phone2' => ['max:32', new PhoneNumber, 'nullable'],
            'pic1Name' => 'required|max:255',
            'picphone1' => ['max:32', new PhoneNumber, 'nullable'],
            'pic_email1' => 'max:64|email:rfc|nullable',
            'npwp_no' => 'max:24|nullable',
            'npwp_address' => 'max:1024|nullable|required_with:npwp_no',
            'npwp_province_id' => 'nullable|required_with:npwp_no',
            'npwp_city_id' => 'nullable|required_with:npwp_no',
            'npwp_district_id' => 'nullable|required_with:npwp_no',
            'npwp_subdistrict_id' => 'nullable|required_with:npwp_no',
        ];
        $errMsg = [
            'entityType_id.numeric' => 'please select a valid entity type.',
            'province_id.numeric' => 'please select a valid province.',
            'city_id.numeric' => 'please select a valid city.',
            'district_id.numeric' => 'please select a valid district.',
            'subdistrict_id.numeric' => 'please select a valid sub district.',
            'pic1Name.required' => 'the pic name field is required.',
            'picphone1.required' => 'the pic phone field is required.',
            'pic_email1.required' => 'the pic email field is required.',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['bank_name'.$i]) {
                    $validateShipmentInput = [
                        'bank_name'.$i => 'required|max:255',
                        'bank_address'.$i => 'required|max:1024',
                        'account_name'.$i => 'required|max:255',
                        'account_no'.$i => 'required|max:255',
                        'currency_bank_id'.$i => 'required|numeric',
                        'swift_code'.$i => 'max:255|nullable',
                        'bsb_code'.$i => 'max:255|nullable',
                    ];
                    $errShipmentMsg = [
                        'bank_name'.$i.'.required' => 'the bank name field is required.',
                        'bank_address'.$i.'.required' => 'the bank address field is required.',
                        'account_name'.$i.'.required' => 'the account name field is required.',
                        'account_no'.$i.'.required' => 'the account no field is required.',
                        'currency_bank_id'.$i.'.numeric' => 'please select a valid currency',
                        'swift_code'.$i.'.required' => 'the swift code field is required.',
                        'bsb_code'.$i.'.required' => 'the bsb code field is required.',
                    ];
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

        $qCourier = Mst_courier::where('slug', '=', urldecode($slug))
        ->first();

        $ins = Mst_courier::where('slug', '=', urldecode($slug))
        ->update([
            'entity_type_id' => $request->entityType_id,
            'name' => $request->courierName,
            'slug' => SlugService::createSlug(Mst_courier::class, 'slug', $request->courierName),
            'office_address' => $request->office_address,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'district_id' => $request->district_id,
            'sub_district_id' => $request->subdistrict_id,
            'post_code' => $request->postcode,
            'courier_email' => $request->courier_email,
            'phone1' => $request->phone1,
            'phone2' => $request->phone2,
            'pic1_name' => $request->pic1Name,
            'pic1_phone' => $request->picphone1,
            'pic1_email' => $request->pic_email1,
            'npwp_no' => $request->npwp_no,
            'npwp_address' => $npwp_address,
            'npwp_province_id' => $npwp_province_id,
            'npwp_city_id' => $npwp_city_id,
            'npwp_district_id' => $npwp_district_id,
            'npwp_sub_district_id' => $npwp_sub_district_id,
            'active' => 'Y',
            'updated_by' => Auth::user()->id
        ]);

        // set not active
        $updBank = Mst_courier_bank_information::where([
            'courier_id' => $qCourier->id
        ])->update([
            'active' => 'N'
        ]);

        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['bank_name'.$i]) {
                    if ($request['bank_id_'.$i] > 0) {
                        $updBank = Mst_courier_bank_information::where('id', '=', $request['bank_id_'.$i])
                        ->update([
                            'courier_id' => $qCourier->id,
                            'bank_name' => $request['bank_name'.$i],
                            'bank_address' => $request['bank_address'.$i],
                            'account_name' => $request['account_name'.$i],
                            'account_no' => $request['account_no'.$i],
                            'currency_id' => $request['currency_bank_id'.$i],
                            'swift_code' => $request['swift_code'.$i],
                            'bsb_code' => $request['bsb_code'.$i],
                            'active' => 'Y',
                            'updated_by' => Auth::user()->id
                        ]);
                    } else {
                        $insBank = Mst_courier_bank_information::create([
                            'courier_id' => $qCourier->id,
                            'bank_name' => $request['bank_name'.$i],
                            'bank_address' => $request['bank_address'.$i],
                            'account_name' => $request['account_name'.$i],
                            'account_no' => $request['account_no'.$i],
                            'currency_id' => $request['currency_bank_id'.$i],
                            'swift_code' => $request['swift_code'.$i],
                            'bsb_code' => $request['bsb_code'.$i],
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
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
     * @param  \App\Models\Mst_courier  $mst_courier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_courier $mst_courier)
    {
        //
    }
}
