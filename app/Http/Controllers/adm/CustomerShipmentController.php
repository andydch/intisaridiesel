<?php

namespace App\Http\Controllers\adm;

use App\Models\Mst_city;
use App\Rules\PhoneNumber;
use App\Models\Mst_customer;
use App\Models\Mst_district;
use App\Models\Mst_province;
use App\Models\Mst_sub_district;
use App\Models\Mst_customer_shipment_address;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerShipmentController extends Controller
{
    protected $title = 'Customer Shipment Address';
    protected $folder = 'customer-shipment-address';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Mst_customer_shipment_address::leftJoin('mst_customers', 'mst_customer_shipment_address.customer_id', '=', 'mst_customers.id')
            ->select('mst_customer_shipment_address.*')
            ->orderBy('mst_customers.name', 'ASC')
            ->get();
        $data = [
            'customers' => $query,
            'title' => $this->title,
            'folder' => $this->folder
        ];

        return view('adm.' . $this->folder . '.index', $data);
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
        if (old('province_id')) {
            $city = Mst_city::where([
                'province_id' => old('province_id'),
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
        }

        $subdistricts = [];
        if (old('district_id')) {
            $subdistricts = Mst_sub_district::where([
                'district_id' => old('district_id'),
                'active' => 'Y'
            ])
                ->orderBy('sub_district_name', 'ASC')
                ->get();
        }

        $customer = Mst_customer::where([
            'active' => 'Y'
        ])
            ->orderBy('name', 'ASC')
            ->get();

        $data = [
            'province' => $province,
            'cities' => $city,
            'districts' => $districts,
            'subdistricts' => $subdistricts,
            'customers' => $customer,
            'title' => $this->title,
            'folder' => $this->folder
        ];
        return view('adm.' . $this->folder . '.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'customer_id' => 'required|numeric',
            'address' => 'required|max:1024',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'district_id' => 'required|numeric',
            'subdistrict_id' => 'required|numeric',
            'phone' => ['required', 'max:32', new PhoneNumber],
        ], [
            'customer_id.numeric' => 'Please select a valid customer',
            'province_id.numeric' => 'Please select a valid province',
            'city_id.numeric' => 'Please select a valid city',
            'district_id.numeric' => 'Please select a valid district',
            'subdistrict_id.numeric' => 'Please select a valid sub district',
        ])->validate();

        $active = 'N';
        if ($request->active == 'on') {
            $active = 'Y';
        }

        $ins = Mst_customer_shipment_address::create([
            'customer_id' => $request->customer_id,
            'address' => $request->address,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'district_id' => $request->district_id,
            'sub_district_id' => $request->subdistrict_id,
            'phone' => $request->phone,
            'active' => $active,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/' . $this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_customer_shipment_address  $mst_customer_shipment_address
     * @return \Illuminate\Http\Response
     */
    public function show(Mst_customer_shipment_address $mst_customer_shipment_address)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_customer_shipment_address  $mst_customer_shipment_address
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $query = Mst_customer_shipment_address::where('id', '=', $id)
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

            $customer = Mst_customer::where([
                'active' => 'Y'
            ])
                ->orderBy('name', 'ASC')
                ->get();

            $data = [
                'province' => $province,
                'cities' => $city,
                'districts' => $districts,
                'subdistricts' => $subdistricts,
                'customers' => $customer,
                'customer_shipping' => $query,
                'title' => $this->title,
                'folder' => $this->folder
            ];
            return view('adm.' . $this->folder . '.edit', $data);
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
     * @param  \App\Models\Mst_customer_shipment_address  $mst_customer_shipment_address
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Validator::make($request->all(), [
            'customer_id' => 'required|numeric',
            'address' => 'required|max:1024',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'district_id' => 'required|numeric',
            'subdistrict_id' => 'required|numeric',
            'phone' => ['required', 'max:32', new PhoneNumber],
        ], [
            'customer_id.numeric' => 'Please select a valid customer',
            'province_id.numeric' => 'Please select a valid province',
            'city_id.numeric' => 'Please select a valid city',
            'district_id.numeric' => 'Please select a valid district',
            'subdistrict_id.numeric' => 'Please select a valid sub district',
        ])->validate();

        $active = 'N';
        if ($request->active == 'on') {
            $active = 'Y';
        }

        $ins = Mst_customer_shipment_address::where('id', '=', $id)
            ->update([
                'customer_id' => $request->customer_id,
                'address' => $request->address,
                'province_id' => $request->province_id,
                'city_id' => $request->city_id,
                'district_id' => $request->district_id,
                'sub_district_id' => $request->subdistrict_id,
                'phone' => $request->phone,
                'active' => $active,
                'updated_by' => Auth::user()->id
            ]);

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/' . $this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_customer_shipment_address  $mst_customer_shipment_address
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_customer_shipment_address $mst_customer_shipment_address)
    {
        //
    }
}
