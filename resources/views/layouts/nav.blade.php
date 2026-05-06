<!--sidebar wrapper -->
<style>
    .logo-icon {
        width: 130px;
    }
</style>
<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header" style="background-color: #fff;padding-right: 5px;">
        <div style="margin: auto;display:block;">
            <img src="{{ asset('assets/images/logo_UID.png') }}" class="logo-icon" alt="" />
        </div>
        {{-- <div>
            <h4 class="logo-text" style="color:#000;">USAHA<br/>INTISARI<br/>DIESEL</h4>
        </div> --}}
        {{-- <div>
            <h4 class="logo-text" style="color: #0c4da2;font-style:italic;font-weight:700;font-size:17px;line-height:0.5;">INTISARI
                DIESEL<br/><span style="font-size: 7px;font-weight:500;font-style:normal;">PENYALUR SUKU CADANG TRUK & BUS</span></h4>
        </div> --}}
        {{-- <div class="toggle-icon ms-auto" style="color:#000;"><i class='bx bx-arrow-to-left'></i></div> --}}
    </div>
    <!--navigation-->
    <ul class="metismenu" id="menu">
        <li>
            <a href="{{ url('dashboard') }}">
                <div class="parent-icon"><i class='bx bx-home-circle'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        @php
        // admin
        $queryMenu = \App\Models\Mst_menu_user::where([
            'user_id' => Auth::user()->id,
            'user_access_read' => 'Y',
            ])
            ->first();
        @endphp
        @if ($queryMenu || Auth::user()->id==1)
            <li class="menu-label">Master</li>

            @php
            // admin master part
            $query = \App\Models\Mst_menu_user::whereIn('menu_id', [41])
            ->where([
                'menu_id' => 41,
                'user_id' => Auth::user()->id,
                'user_access_read' => 'Y',
            ])
            ->first();
            @endphp
            @if ($query || Auth::user()->id==1)
                <li class="{{ strpos(url()->current(),"/stock-master")>0?'mm-active':'' }}">
                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master/::::::::::::::') }}" class="has-arrow">
                        <div class="parent-icon"><i class="lni lni-cog" style="color:#929876;"></i></div>
                        <div class="menu-title">Master Part</div>
                    </a>
                </li>
            @endif

            @php
            // admin master area
            $query = \App\Models\Mst_menu_user::whereIn('menu_id', [1, 2, 3, 4, 5])
                ->where([
                'user_id' => Auth::user()->id,
                'user_access_read' => 'Y',
            ])
            ->first();
            @endphp
            @if ($query || Auth::user()->id==1)
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="lni lni-map" style="color: #c6ca97;"></i></div>
                        <div class="menu-title">Master Area</div>
                    </a>
                    <ul>
                        @php
                        // admin country
                        $queryCountry = \App\Models\Mst_menu_user::where([
                            'menu_id' => 1,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryCountry || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/country/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/country') }}"><i class="bx bx-right-arrow-alt"></i>Country</a>
                        </li>
                        @endif
                        @php
                        // admin province
                        $queryProvince = \App\Models\Mst_menu_user::where([
                            'menu_id' => 2,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryProvince || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/province/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/province') }}"><i class="bx bx-right-arrow-alt"></i>Province</a>
                        </li>
                        @endif
                        @php
                        // admin city
                        $queryCity = \App\Models\Mst_menu_user::where([
                            'menu_id' => 3,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryCity || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/city/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/city') }}"><i class="bx bx-right-arrow-alt"></i>City</a>
                        </li>
                        @endif
                        @php
                        // admin district
                        $queryDistrict = \App\Models\Mst_menu_user::where([
                            'menu_id' => 4,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryDistrict || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/district/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/district') }}"><i class="bx bx-right-arrow-alt"></i>District</a>
                        </li>
                        @endif
                        @php
                        // admin sub district
                        $querySubDistrict = \App\Models\Mst_menu_user::where([
                            'menu_id' => 5,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($querySubDistrict || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/subdistrict/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/subdistrict') }}"><i class="bx bx-right-arrow-alt"></i>Sub District</a>
                        </li>
                        @endif
                    </ul>
                </li>
            @endif

            @php
            // admin master parameter
            $query = \App\Models\Mst_menu_user::whereIn('menu_id', [9,10,11,12,13,14,15,16,17,18,28,47,48,66,77,78])
            ->where([
                'user_id' => Auth::user()->id,
                'user_access_read' => 'Y',
                ])
            ->first();
            @endphp
            @if ($query || Auth::user()->id==1)
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-slider" style="color: #e3e06b;"></i></div>
                    <div class="menu-title">Master Parameter</div>
                </a>
                <ul>
                    {{-- @php
                    // admin global
                    $queryGlobal = \App\Models\Mst_menu_user::where([
                    'menu_id' => 6,
                    'user_id' => Auth::user()->id,
                    'user_access_read' => 'Y',
                    ])->first();
                    @endphp
                    @if ($queryGlobal || Auth::user()->id==1)
                    <li>
                        <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/mst-global') }}"><i
                                class="bx bx-right-arrow-alt"></i>Parameter</a>
                    </li>
                    @endif --}}

                    @php
                    // admin brand
                    $queryBrand = \App\Models\Mst_menu_user::where([
                        'menu_id' => 9,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryBrand || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/brand/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/brand') }}"><i class="bx bx-right-arrow-alt"></i>Brand</a>
                        </li>
                    @endif

                    @php
                    // admin brand type
                    $queryBrandType = \App\Models\Mst_menu_user::where([
                        'menu_id' => 28,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryBrandType || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/brand-type/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/brand-type') }}"><i class="bx bx-right-arrow-alt"></i>Brand Type</a>
                        </li>
                    @endif

                    @php
                    // admin currency
                    $queryCurrency = \App\Models\Mst_menu_user::where([
                        'menu_id' => 10,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryCurrency || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/currency/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/currency') }}"><i class="bx bx-right-arrow-alt"></i>Currency</a>
                        </li>
                    @endif

                    @php
                    // admin gender
                    $queryGender = \App\Models\Mst_menu_user::where([
                        'menu_id' => 11,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryGender || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/gender/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/gender') }}"><i class="bx bx-right-arrow-alt"></i>Gender</a>
                        </li>
                    @endif

                    @php
                    // admin entity-type
                    $queryGender = \App\Models\Mst_menu_user::where([
                        'menu_id' => 12,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryGender || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/entity-type/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/entity-type') }}"><i class="bx bx-right-arrow-alt"></i>Entity Type</a>
                        </li>
                    @endif

                    @php
                    // admin supplier-type
                    $querySupplier = \App\Models\Mst_menu_user::where([
                        'menu_id' => 13,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($querySupplier || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/supplier-type/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/supplier-type') }}"><i class="bx bx-right-arrow-alt"></i>Supplier Type</a>
                        </li>
                    @endif

                    @php
                    // admin part-type
                    $queryPart = \App\Models\Mst_menu_user::where([
                        'menu_id' => 14,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryPart || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/part-type/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/part-type') }}"><i class="bx bx-right-arrow-alt"></i>Part Type</a>
                        </li>
                    @endif

                    @php
                    // admin part-category
                    $queryPartCategory = \App\Models\Mst_menu_user::where([
                        'menu_id' => 15,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryPartCategory || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/part-category/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/part-category') }}"><i class="bx bx-right-arrow-alt"></i>Part Category</a>
                        </li>
                    @endif

                    @php
                    // admin weight-type
                    $queryWeightType = \App\Models\Mst_menu_user::where([
                        'menu_id' => 16,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryWeightType || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/weight-type/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/weight-type') }}"><i class="bx bx-right-arrow-alt"></i>Weight Type</a>
                        </li>
                    @endif

                    {{-- @php
                    // admin delivery-type
                    $queryDeliveryType = \App\Models\Mst_menu_user::where([
                    'menu_id' => 17,
                    'user_id' => Auth::user()->id,
                    'user_access_read' => 'Y',
                    ])->first();
                    @endphp
                    @if ($queryDeliveryType || Auth::user()->id==1)
                    <li>
                        <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/delivery-type') }}"><i class="bx bx-right-arrow-alt"></i>Delivery Type</a>
                    </li>
                    @endif --}}

                    @php
                    // admin quantity-type
                    $queryQuantityType = \App\Models\Mst_menu_user::where([
                        'menu_id' => 18,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryQuantityType || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/quantity-type/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/quantity-type') }}"><i class="bx bx-right-arrow-alt"></i>Quantity Type</a>
                        </li>
                    @endif

                    @php
                    // admin employee-section
                    $queryEmployeeSection = \App\Models\Mst_menu_user::where([
                        'menu_id' => 47,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryEmployeeSection || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/employee-section/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/employee-section') }}"><i class="bx bx-right-arrow-alt"></i>Employee Section</a>
                        </li>
                    @endif

                    {{-- @php
                    // admin courier-type
                    $queryCourierType = \App\Models\Mst_menu_user::where([
                        'menu_id' => 48,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryCourierType || Auth::user()->id==1)
                        <li>
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/courier-type') }}">
                                <i class="bx bx-right-arrow-alt"></i>Courier Type
                            </a>
                        </li>
                    @endif --}}

                    @php
                    // admin memo-limit
                    $queryMemoLimit = \App\Models\Mst_menu_user::where([
                        'menu_id' => 66,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryMemoLimit || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/memo-limit/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/memo-limit') }}"><i class="bx bx-right-arrow-alt"></i>Memo Limit</a>
                        </li>
                    @endif

                    @php
                    // branch target
                    $queryBranchTarget = \App\Models\Mst_menu_user::where([
                        'menu_id' => 77,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryBranchTarget || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/branch-target/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/branch-target') }}"><i class="bx bx-right-arrow-alt"></i>Branch Target</a>
                        </li>
                    @endif

                    @php
                    // salesman target
                    $querySalesmanTarget = \App\Models\Mst_menu_user::where([
                        'menu_id' => 78,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($querySalesmanTarget || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/salesman-target/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/salesman-target') }}"><i class="bx bx-right-arrow-alt"></i>Salesman Target</a>
                        </li>
                    @endif
                </ul>
            </li>
            @endif

            @php
            // admin master data
            $query = \App\Models\Mst_menu_user::whereIn('menu_id', [7,8,19,21,22,23])
                ->where([
                'user_id' => Auth::user()->id,
                'user_access_read' => 'Y',
            ])
            ->first();
            @endphp
            @if ($query || Auth::user()->id==1)
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="lni lni-headphone-alt" style="color: #d16246;"></i></div>
                    <div class="menu-title">Master Support</div>
                </a>
                <ul>
                    @php
                    // admin branch
                    $queryBranch = \App\Models\Mst_menu_user::where([
                        'menu_id' => 7,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryBranch || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/branch/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/branch') }}"><i class="bx bx-right-arrow-alt"></i>Branch</a>
                        </li>
                    @endif

                    {{-- @php
                    // admin salesman
                    $querySalesman = \App\Models\Mst_menu_user::where([
                    'menu_id' => 8,
                    'user_id' => Auth::user()->id,
                    'user_access_read' => 'Y',
                    ])->first();
                    @endphp
                    @if ($querySalesman || Auth::user()->id==1)
                    <li>
                        <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/salesman') }}"><i
                                class="bx bx-right-arrow-alt"></i>Salesman</a>
                    </li>
                    @endif --}}

                    @php
                    // admin customer
                    $queryCustomer = \App\Models\Mst_menu_user::where([
                        'menu_id' => 19,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryCustomer || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/customer/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/customer') }}"><i class="bx bx-right-arrow-alt"></i>Customer</a>
                        </li>
                    @endif

                    {{-- @php
                    // admin customer shipment controller
                    $queryCustomerShipment = \App\Models\Mst_menu_user::where([
                    'menu_id' => 20,
                    'user_id' => Auth::user()->id,
                    'user_access_read' => 'Y',
                    ])->first();
                    @endphp
                    @if ($queryCustomerShipment || Auth::user()->id==1)
                    <li>
                        <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/customer-shipment-address') }}"><i
                                class="bx bx-right-arrow-alt"></i>Customer Shipment Address</a>
                    </li>
                    @endif --}}

                    @php
                    // admin supplier controller
                    $querySupplier = \App\Models\Mst_menu_user::where([
                        'menu_id' => 21,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($querySupplier || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/supplier/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/supplier') }}"><i class="bx bx-right-arrow-alt"></i>Supplier</a>
                        </li>
                    @endif

                    {{-- @php
                    // admin part controller
                    $queryPart = \App\Models\Mst_menu_user::where([
                    'menu_id' => 22,
                    'user_id' => Auth::user()->id,
                    'user_access_read' => 'Y',
                    ])->first();
                    @endphp
                    @if ($queryPart || Auth::user()->id==1)
                    <li>
                        <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/part') }}"><i
                                class="bx bx-right-arrow-alt"></i>Part</a>
                    </li>
                    @endif --}}

                    @php
                    // admin courier controller
                    $queryCourier = \App\Models\Mst_menu_user::where([
                        'menu_id' => 23,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryCourier || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/courier/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/courier') }}"><i class="bx bx-right-arrow-alt"></i>Courier</a>
                        </li>
                    @endif
                </ul>
            </li>
            @endif

            @php
            // admin master finance & accounting
            $query = \App\Models\Mst_menu_user::whereIn('menu_id', [24,31,32,49,68,101])
            ->where([
                'user_id' => Auth::user()->id,
                'user_access_read' => 'Y',
            ])
            ->first();
            @endphp
            @if ($query || Auth::user()->id==1)
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-bar-chart-alt" style="color: #9d57ad;"></i></div>
                    <div class="menu-title">Master Finance & Accounting</div>
                </a>
                <ul>
                    @php
                    // admin company
                    $queryCompany = \App\Models\Mst_menu_user::where([
                        'menu_id' => 24,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryCompany || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/company/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/company') }}"><i class="bx bx-right-arrow-alt"></i>Company</a>
                        </li>
                    @endif

                    @php
                    // admin vat
                    $queryVAT = \App\Models\Mst_menu_user::where([
                        'menu_id' => 31,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryVAT || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/vat/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/vat') }}"><i class="bx bx-right-arrow-alt"></i>VAT</a>
                        </li>
                    @endif

                    @php
                    // admin coa
                    $queryCOA = \App\Models\Mst_menu_user::where([
                        'menu_id' => 32,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryCOA || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/coa/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/coa') }}"><i class="bx bx-right-arrow-alt"></i>COA</a>
                        </li>
                    @endif

                    @php
                    // admin payment reference
                    $queryPaymentReference = \App\Models\Mst_menu_user::where([
                        'menu_id' => 49,
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($queryPaymentReference || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/payment-ref/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/payment-ref') }}"><i class="bx bx-right-arrow-alt"></i>Payment Reference</a>
                        </li>
                    @endif

                    {{-- tax invoice --}}
                    @php
                        $queryTaxInvoice = \App\Models\Mst_menu_user::where([
                            'menu_id' => 68,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                    @endphp
                    @if ($queryTaxInvoice || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/tax-invoice/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/tax-invoice') }}"><i class="bx bx-right-arrow-alt"></i>Tax Invoice</a>
                        </li>
                    @endif

                    {{-- automatic journal --}}
                    @php
                        $queryAutomaticJournal = \App\Models\Mst_menu_user::where([
                            'menu_id' => 101,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                    @endphp
                    @if ($queryAutomaticJournal || Auth::user()->id==1)
                        <li class="{{ strpos(url()->current(),"/automatic-journal/")>0?'mm-active':'' }}">
                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/automatic-journal') }}"><i class="bx bx-right-arrow-alt"></i>Automatic Journal</a>
                        </li>
                    @endif
                </ul>
            </li>
            @endif
            {{-- // admin --}}

            @php
            // transaction
            $queryTx = \App\Models\Mst_menu_user::whereIn('menu_id',
            [
                25,26,27,29,30,33,34,35,36,37,38,39,40,42,43,44,50,51,52,53,54,55,56,
                57,58,59,60,62,63,64,67,69,70,71,72,73,74,75,76,102,103,113,116,117,121
            ])
            ->where(
            [
                'user_id' => Auth::user()->id,
                'user_access_read' => 'Y',
            ])
            ->first();
            @endphp
            @if ($queryTx || Auth::user()->id==1)
                <li class="menu-label">Transactions</li>
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="bx bx-basket" style="color: #9b334e;"></i> </div>
                        <div class="menu-title">Purchase</div>
                    </a>
                    <ul>
                        @php
                            // purchase inquiry
                            $queryPurchaseInquiry = \App\Models\Mst_menu_user::where([
                                'menu_id' => 67,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryPurchaseInquiry || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/purchase-inquiry/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-inquiry') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Inquiry</a>
                            </li>
                        @endif

                        @php
                        // quotation
                        $queryQuotation = \App\Models\Mst_menu_user::where([
                            'menu_id' => 35,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryQuotation || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/quotation/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/quotation') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Quotation</a>
                            </li>
                        @endif

                        @php
                        // memo
                        $queryMemo = \App\Models\Mst_menu_user::where([
                            'menu_id' => 25,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryMemo || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/memo/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/memo') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Memo</a>
                            </li>
                        @endif

                        @php
                        // order
                        $queryOrder = \App\Models\Mst_menu_user::where([
                            'menu_id' => 26,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryOrder || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/order/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/order') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Order</a>
                            </li>
                        @endif

                        @php
                        // receipt order
                        $queryReceiptOrder = \App\Models\Mst_menu_user::where([
                            'menu_id' => 29,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryReceiptOrder || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/receipt-order")>0 || strpos(url()->current(),"/receipt-order-index")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order') }}"><i class="bx bx-right-arrow-alt"></i>Receipt Order</a>
                            </li>
                        @endif

                        {{-- proses tagihan supplier --}}
                        @php
                            $queryTagihanSupplier = \App\Models\Mst_menu_user::where([
                                'menu_id' => 121,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryTagihanSupplier || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/tagihan-supplier/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/tagihan-supplier') }}"><i class="bx bx-right-arrow-alt"></i>Collection Tagihan Supplier</a>
                            </li>
                        @endif

                        @php
                        // purchase retur
                        $queryPurchaseRetur = \App\Models\Mst_menu_user::where([
                            'menu_id' => 37,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryPurchaseRetur || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/purchase-retur/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-retur') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Retur</a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="bx bx-line-chart" style="color: #6c3dbd;"></i></div>
                        <div class="menu-title">Sales</div>
                    </a>
                    <ul>
                        @php
                        // quotation
                        $queryQuotation = \App\Models\Mst_menu_user::where([
                            'menu_id' => 36,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryQuotation || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/sales-quotation/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/sales-quotation') }}"><i class="bx bx-right-arrow-alt"></i>Sales Quotation</a>
                            </li>
                        @endif

                        @php
                        // order
                        $queryOrder = \App\Models\Mst_menu_user::where([
                            'menu_id' => 33,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryOrder || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/sales-order/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/sales-order') }}"><i class="bx bx-right-arrow-alt"></i>Sales Order</a>
                            </li>
                        @endif

                        @php
                            // faktur, formerly delivery order
                            $queryDeliveryOrder = \App\Models\Mst_menu_user::where([
                                'menu_id' => 39,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryDeliveryOrder || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/faktur/")>0 || strpos(url()->current(),"/upl-faktur-pajak/")>0 || strpos(url()->current(),"/dl-faktur-pajak/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/faktur') }}"><i class="bx bx-right-arrow-alt"></i>Faktur</a>
                            </li>
                        @endif

                        {{-- invoice --}}
                        @php
                            $queryInvoice = \App\Models\Mst_menu_user::where([
                                'menu_id' => 52,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryInvoice || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/invoice/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/invoice') }}"><i class="bx bx-right-arrow-alt"></i>Billing Process</a>
                            </li>
                        @endif

                        @php
                            // nota retur
                            $queryNotaRetur = \App\Models\Mst_menu_user::where([
                                'menu_id' => 40,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryNotaRetur || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/nota-retur/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/nota-retur') }}"><i class="bx bx-right-arrow-alt"></i>Nota Retur</a>
                            </li>
                        @endif

                        @php
                            // sales progress
                            $querySalesProgress = \App\Models\Mst_menu_user::where([
                                'menu_id' => 113,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($querySalesProgress || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/sales-progress/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/sales-progress') }}"><i class="bx bx-right-arrow-alt"></i>Sales Progress</a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="lni lni-map-marker" style="color: #4bbffa;"></i></div>
                        <div class="menu-title">Local</div>
                    </a>
                    <ul>
                        @php
                            // surat jalan - non tax
                            $querySJ = \App\Models\Mst_menu_user::where([
                                'menu_id' => 70,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($querySJ || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/surat-jalan/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/surat-jalan') }}"><i class="bx bx-right-arrow-alt"></i>Surat Jalan</a>
                            </li>
                        @endif

                        @php
                            // nota penjualan
                            $queryDOnontax = \App\Models\Mst_menu_user::where([
                                'menu_id' => 69,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryDOnontax || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/delivery-order-local/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/delivery-order-local') }}"><i class="bx bx-right-arrow-alt"></i>Nota Penjualan</a>
                            </li>
                        @endif

                        @php
                            // kwitansi
                            $queryKwitansi = \App\Models\Mst_menu_user::where([
                                'menu_id' => 72,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryKwitansi || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/kwitansi/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/kwitansi') }}"><i class="bx bx-right-arrow-alt"></i>Proses Tagihan</a>
                            </li>
                        @endif

                        @php
                            // retur
                            $queryRetur = \App\Models\Mst_menu_user::where([
                                'menu_id' => 74,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryRetur || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/retur/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/retur') }}"><i class="bx bx-right-arrow-alt"></i>Retur</a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="bx bx-detail" style="color: #34a6dc;"></i></div>
                        <div class="menu-title">Stock Management</div>
                    </a>
                    <ul>
                        @php
                        // stock transfer
                        $queryStockTransfer = \App\Models\Mst_menu_user::where([
                            'menu_id' => 42,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryStockTransfer || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/stock-transfer/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer') }}"><i class="bx bx-right-arrow-alt"></i>Stock Transfer</a>
                            </li>
                        @endif

                        @php
                        // stock transfer
                        $queryStockTransferReceived = \App\Models\Mst_menu_user::where([
                            'menu_id' => 44,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryStockTransferReceived || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/stock-transfer-received/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer-received') }}"><i class="bx bx-right-arrow-alt"></i>Stock Transfer (Received)</a>
                            </li>
                        @endif

                        @php
                        // stock assembly
                        $queryStockAssembly = \App\Models\Mst_menu_user::where([
                            'menu_id' => 45,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryStockAssembly || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/stock-assembly/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-assembly') }}"><i class="bx bx-right-arrow-alt"></i>Stock Assembly</a>
                            </li>
                        @endif

                        @php
                        // stock disassembly
                        $queryStockDisAssembly = \App\Models\Mst_menu_user::where([
                            'menu_id' => 46,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryStockDisAssembly || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/stock-disassembly/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-disassembly') }}"><i class="bx bx-right-arrow-alt"></i>Stock Disassembly</a>
                            </li>
                        @endif

                        @php
                            // stock adjustment
                            $queryStockAdjustment = \App\Models\Mst_menu_user::where([
                                'menu_id' => 76,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryStockAdjustment || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/stock-adjustment/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-adjustment') }}"><i class="bx bx-right-arrow-alt"></i>Stock Adjustment</a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="bx bx-money" style="color: #1ddec9;"></i></div>
                        <div class="menu-title">Finance & Accounting</div>
                    </a>
                    <ul>
                        {{-- payment voucher --}}
                        @php
                            $queryPaymentVoucher = \App\Models\Mst_menu_user::where([
                                'menu_id' => 50,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryPaymentVoucher || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/payment-voucher/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/payment-voucher') }}"><i class="bx bx-right-arrow-alt"></i>Pembayaran Supplier</a>
                            </li>
                        @endif

                        {{-- payment receipt --}}
                        @php
                            $queryPaymentReceipt = \App\Models\Mst_menu_user::where([
                                'menu_id' => 54,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryPaymentReceipt|| Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/payment-receipt/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/payment-receipt') }}"><i class="bx bx-right-arrow-alt"></i>Penerimaan Customer</a>
                            </li>
                        @endif

                        {{-- general journal --}}
                        @php
                            $queryGeneralJournal = \App\Models\Mst_menu_user::where([
                                'menu_id' => 56,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryGeneralJournal || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/general-journal/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/general-journal') }}"><i class="bx bx-right-arrow-alt"></i>General Journal</a>
                            </li>
                        @endif

                        {{-- lokal journal --}}
                        @php
                            $queryLokalJournal = \App\Models\Mst_menu_user::where([
                                'menu_id' => 103,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryLokalJournal || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/lokal-journal/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/lokal-journal') }}"><i class="bx bx-right-arrow-alt"></i>Lokal Journal</a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="bx bx-coin-stack" style="color: #73dc83;"></i></div>
                        <div class="menu-title">Cash Flow</div>
                    </a>
                    <ul>
                        {{-- payment plan --}}
                        @php
                            $queryPaymentPlan = \App\Models\Mst_menu_user::where([
                                'menu_id' => 116,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryPaymentPlan || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/payment-plan/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/payment-plan') }}"><i class="bx bx-right-arrow-alt"></i>Rencana Pembayaran</a>
                            </li>
                        @endif

                        {{-- acceptance plan --}}
                        @php
                            $queryAcceptancePlan = \App\Models\Mst_menu_user::where([
                                'menu_id' => 117,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryAcceptancePlan || Auth::user()->id==1)
                            <li class="{{ strpos(url()->current(),"/acceptance-plan/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/acceptance-plan') }}"><i class="bx bx-right-arrow-alt"></i>Rencana Penerimaan</a>
                            </li>
                        @endif
                    </ul>
                </li>
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="bx bx-message-square-check" style="color: #16c099;"></i></div>
                        <div class="menu-title">Approval</div>
                    </a>
                    <ul>
                        @php
                            // approval order
                            $queryApprOrder = \App\Models\Mst_menu_user::where([
                                'menu_id' => 27,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryApprOrder || Auth::user()->id==1)
                            @php
                                $count = \App\Models\Tx_purchase_order::where('purchase_no','NOT LIKE','%Draft%')
                                ->where('approved_status','=',null)
                                ->where('active','=','Y')
                                ->count();
                            @endphp
                            <li class="{{ strpos(url()->current(),"/order-approval/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/order-approval') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Order @if ($count>0){{ '('.$count.')' }}@endif</a>
                            </li>
                        @endif

                        @php
                            // approval purchase retur
                            $queryApprPurchaseRetur = \App\Models\Mst_menu_user::where([
                                'menu_id' => 38,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryApprPurchaseRetur || Auth::user()->id==1)
                            @php
                                $count = \App\Models\Tx_purchase_retur::where('purchase_retur_no','NOT LIKE','%Draft%')
                                ->where('approved_by','=',null)
                                ->where('active','=','Y')
                                ->count();
                            @endphp
                            <li class="{{ strpos(url()->current(),"/purchase-retur-approval/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-retur-approval') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Retur @if ($count>0){{ '('.$count.')' }}@endif</a>
                            </li>
                        @endif

                        {{-- @php
                        // approval receipt order
                        $queryApprReceiptOrder = \App\Models\Mst_menu_user::where([
                            'menu_id' => 30,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryApprReceiptOrder || Auth::user()->id==1)
                        <li>
                            <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order-approval') }}"><i class="bx bx-right-arrow-alt"></i>Receipt Order</a>
                        </li>
                        @endif --}}

                        @php
                        // approval sales order
                        $queryApprSalesOrder = \App\Models\Mst_menu_user::where([
                            'menu_id' => 34,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryApprSalesOrder || Auth::user()->id==1)
                            @php
                                $count = \App\Models\Tx_sales_order::where('sales_order_no','NOT LIKE','%Draft%')
                                ->where('need_approval','=','Y')
                                ->where('active','=','Y')
                                ->count();
                            @endphp
                            <li class="{{ strpos(url()->current(),"/sales-order-approval/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/sales-order-approval') }}"><i class="bx bx-right-arrow-alt"></i>Sales Order @if ($count>0){{ '('.$count.')' }}@endif</a>
                            </li>
                        @endif

                        @php
                            // approval surat jalan
                            $queryApprSuratJalan = \App\Models\Mst_menu_user::where([
                                'menu_id' => 71,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryApprSuratJalan || Auth::user()->id==1)
                            @php
                                $count = \App\Models\Tx_surat_jalan::where('surat_jalan_no','NOT LIKE','%Draft%')
                                ->where('need_approval','=','Y')
                                ->where('active','=','Y')
                                ->count();
                            @endphp
                            <li class="{{ strpos(url()->current(),"/surat-jalan-approval/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/surat-jalan-approval') }}"><i class="bx bx-right-arrow-alt"></i>Surat Jalan @if ($count>0){{ '('.$count.')' }}@endif</a>
                            </li>
                        @endif

                        {{-- approval nota retur --}}
                        @php
                            $queryNotaRetur = \App\Models\Mst_menu_user::where([
                                'menu_id' => 55,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryNotaRetur || Auth::user()->id==1)
                            @php
                                $count = \App\Models\Tx_nota_retur::where('nota_retur_no','NOT LIKE','%Draft%')
                                ->where('approved_by','=',null)
                                ->where('active','=','Y')
                                ->count();
                            @endphp
                            <li class="{{ strpos(url()->current(),"/nota-retur-approval/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/nota-retur-approval') }}"><i class="bx bx-right-arrow-alt"></i>Nota Retur @if ($count>0){{ '('.$count.')' }}@endif</a>
                            </li>
                        @endif

                        {{-- approval nota retur - non tax --}}
                        @php
                            $queryNotaReturNonTax = \App\Models\Mst_menu_user::where([
                                'menu_id' => 75,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryNotaReturNonTax || Auth::user()->id==1)
                            @php
                                $count = \App\Models\Tx_nota_retur_non_tax::where('nota_retur_no','NOT LIKE','%Draft%')
                                ->where('approved_by','=',null)
                                ->where('active','=','Y')
                                ->count();
                            @endphp
                            <li class="{{ strpos(url()->current(),"/retur-approval/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/retur-approval') }}"><i class="bx bx-right-arrow-alt"></i>Retur @if ($count>0){{ '('.$count.')' }}@endif</a>
                            </li>
                        @endif

                        @php
                        // approval stock transfer
                        $queryApprPurchaseStockTransfer = \App\Models\Mst_menu_user::where([
                            'menu_id' => 43,
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                        @endphp
                        @if ($queryApprPurchaseStockTransfer || Auth::user()->id==1)
                            @php
                                $count = \App\Models\Tx_stock_transfer::where('stock_transfer_no','NOT LIKE','%Draft%')
                                ->where('approved_by','=',null)
                                ->where('canceled_by','=',null)
                                ->where('active','=','Y')
                                ->count();
                            @endphp
                            <li class="{{ strpos(url()->current(),"/stock-transfer-approval/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer-approval') }}"><i class="bx bx-right-arrow-alt"></i>Stock Transfer @if ($count>0){{ '('.$count.')' }}@endif</a>
                            </li>
                        @endif

                        {{-- approval payment voucher --}}
                        @php
                            $queryPaymentVoucher = \App\Models\Mst_menu_user::where([
                                'menu_id' => 51,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryPaymentVoucher || Auth::user()->id==1)
                            @php
                                $count = \App\Models\Tx_payment_voucher::where('payment_voucher_no','NOT LIKE','%Draft%')
                                ->where('approved_by','=',null)
                                ->where('canceled_by','=',null)
                                ->where('active','=','Y')
                                ->count();
                            @endphp
                            <li class="{{ strpos(url()->current(),"/payment-voucher-approval/")>0?'mm-active':'' }}">
                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/payment-voucher-approval') }}"><i class="bx bx-right-arrow-alt"></i>Pembayaran Supplier @if ($count>0){{ '('.$count.')' }}@endif</a>
                            </li>
                        @endif

                        {{-- approval general journal --}}
                        {{-- @php
                            $queryGeneralJournal = \App\Models\Mst_menu_user::where([
                                'menu_id' => 102,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryGeneralJournal || Auth::user()->id==1)
                            @php
                                $count = \App\Models\Tx_general_journal::where('general_journal_no','NOT LIKE','%Draft%')
                                ->where('is_wt_for_appr','=','Y')
                                ->where('active','=','Y')
                                ->count();
                            @endphp
                            <li><a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/general-journal-approval') }}"><i class="bx bx-right-arrow-alt"></i>General Journal @if ($count>0){{ '('.$count.')' }}@endif</a></li>
                        @endif --}}

                        {{-- approval invoice --}}
                        {{-- @php
                            $queryInvoice = \App\Models\Mst_menu_user::where([
                                'menu_id' => 53,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryInvoice || Auth::user()->id==1)
                            <li><a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/invoice-approval') }}"><i class="bx bx-right-arrow-alt"></i>Invoice</a></li>
                        @endif --}}

                        {{-- approval kwitansi --}}
                        {{-- @php
                            $queryKwitansi = \App\Models\Mst_menu_user::where([
                                'menu_id' => 73,
                                'user_id' => Auth::user()->id,
                                'user_access_read' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if ($queryKwitansi || Auth::user()->id==1)
                            <li><a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/kwitansi-approval') }}"><i class="bx bx-right-arrow-alt"></i>Kwitansi</a></li>
                        @endif --}}
                    </ul>
                </li>
            @endif
            {{-- transaksi --}}

            @php
            // report
            $query = \App\Models\Mst_menu_user::whereIn('menu_id', [57,58,59,60,61,62,63,64,65,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,
                100,104,105,106,107,108,109,110,111,112,114,115,118,119,122,123,125])
                ->where([
                    'user_id' => Auth::user()->id,
                    'user_access_read' => 'Y',
                ])
                ->first();
            @endphp
            @if ($query || Auth::user()->id==1)
                <li class="menu-label">Reports</li>
                <li>

                    @php
                    // inventory report
                    $query = \App\Models\Mst_menu_user::whereIn('menu_id', [57,58,59,60,61,62,63,64,65])
                    ->where([
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                    ])
                    ->first();
                    @endphp
                    @if ($query || Auth::user()->id==1)
                        <a href="javascript:;" class="has-arrow">
                            <div class="parent-icon"><i class="bx bx-line-chart" style="color: #237d86;"></i></div>
                            <div class="menu-title">Inventory Report</div>
                        </a>
                        <ul>
                            {{-- report master inventory --}}
                            @php
                                $queryReportInventory = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 57,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryReportInventory || Auth::user()->id==1)
                                <li class="{{ strpos(url()->current(),"/master-inventory/")>0?'mm-active':'' }}">
                                    <a href="{{ url(ENV('REPORT_FOLDER_NAME').'/master-inventory') }}"><i class="bx bx-right-arrow-alt"></i>Master Inventory</a>
                                </li>
                            @endif

                            {{-- report inventory over stock under stock --}}
                            @php
                                $queryInventoryOverStockUnderStock = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 60,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryInventoryOverStockUnderStock || Auth::user()->id==1)
                                <li class="{{ strpos(url()->current(),"/inventory-over-stock-under-stock/")>0?'mm-active':'' }}">
                                    <a href="{{ url(ENV('REPORT_FOLDER_NAME').'/inventory-over-stock-under-stock') }}"><i class="bx bx-right-arrow-alt"></i>Inventory Over Stock / Under Stock</a>
                                </li>
                            @endif

                            {{-- report summary stock per branch per merk --}}
                            @php
                                $querySummaryStockPerGudangPerMerk = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 61,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySummaryStockPerGudangPerMerk || Auth::user()->id==1)
                                <li class="{{ strpos(url()->current(),"/summary-stock-per-gudang-per-merk/")>0?'mm-active':'' }}">
                                    <a href="{{ url(ENV('REPORT_FOLDER_NAME').'/summary-stock-per-gudang-per-merk') }}"><i class="bx bx-right-arrow-alt"></i>Summary Stock Per Branch Per Merk</a>
                                </li>
                            @endif

                            {{-- report pergerakan barang --}}
                            @php
                                $queryMovementOfParts = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 63,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryMovementOfParts || Auth::user()->id==1)
                                <li class="{{ strpos(url()->current(),"/pergerakan-barang/")>0?'mm-active':'' }}">
                                    <a href="{{ url(ENV('REPORT_FOLDER_NAME').'/pergerakan-barang') }}"><i class="bx bx-right-arrow-alt"></i>Parts Movement</a>
                                </li>
                            @endif

                            {{-- report perubahan cost rata-rata --}}
                            @php
                                $queryChangeInAvgCost = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 64,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryChangeInAvgCost || Auth::user()->id==1)
                                <li class="{{ strpos(url()->current(),"/perubahan-cost-rata-rata/")>0?'mm-active':'' }}">
                                    <a href="{{ url(ENV('REPORT_FOLDER_NAME').'/perubahan-cost-rata-rata') }}"><i class="bx bx-right-arrow-alt"></i>AVG Cost Change</a>
                                </li>
                            @endif

                            {{-- report outstanding purchase order per P/N --}}
                            {{-- @php
                                $queryOutPOperPN = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 65,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryOutPOperPN || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/outstanding-purchase-order-per-pn') }}"><i class="bx bx-right-arrow-alt"></i>Outstanding Purchase Order Per P/N</a></li>
                            @endif --}}

                            {{-- report stock inventory accuration per branch --}}
                            {{-- @php
                                $queryStockInv = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 79,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryStockInv || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/stock-inventory-accuration-per-branch') }}"><i class="bx bx-right-arrow-alt"></i>Stock Inventory Accuration Per Branch</a></li>
                            @endif --}}

                            {{-- report inventory per merk per part no --}}
                            {{-- @php
                                $queryInventoryPerMerkPerPartNo = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 58,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryInventoryPerMerkPerPartNo || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/inventory-per-merk-per-part-no') }}"><i class="bx bx-right-arrow-alt"></i>Inventory Per Merk Per Part No</a></li>
                            @endif --}}

                            {{-- report inventory per gudang per part no --}}
                            {{-- @php
                                $queryInventoryPerGudangPerPartNo = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 59,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryInventoryPerGudangPerPartNo || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/inventory-per-gudang-per-part-no') }}"><i class="bx bx-right-arrow-alt"></i>Inventory Per Gudang Per Part No</a></li>
                            @endif --}}

                            {{-- report summary stock per gudang per merk --}}
                            {{-- @php
                                $querySummaryStockPerMerkPerGudang = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 62,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySummaryStockPerMerkPerGudang || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/summary-stock-per-merk-per-gudang') }}"><i class="bx bx-right-arrow-alt"></i>Summary Stock Per Merk Per Gudang</a></li>
                            @endif --}}
                        </ul>
                    @endif
                </li>
                <li>
                    @php
                    // Sales report
                    $query = \App\Models\Mst_menu_user::whereIn('menu_id', [80,81,82,83,84,85,86,87,88,89,90,91,92,111,112,119,125])
                        ->where([
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                        ])
                        ->first();
                    @endphp
                    @if ($query || Auth::user()->id==1)
                        <a href="javascript:;" class="has-arrow">
                            <div class="parent-icon"><i class="bx bx-receipt" style="color: #0d8b42;"></i></div>
                            <div class="menu-title">Sales Report</div>
                        </a>
                        <ul>
                            {{-- sales per branch per customer --}}
                            @php
                                $querySalesPerBranchPerCust = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 80,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySalesPerBranchPerCust || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/sales-per-branch-per-customer') }}"><i class="bx bx-right-arrow-alt"></i>Sales Per Branch Per Customer</a></li>
                            @endif

                            {{-- sales per faktur --}}
                            @php
                                $querySalesPerFakturPerSalesOrder = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 81,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySalesPerFakturPerSalesOrder || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/sales-per-faktur-per-sales-order') }}"><i class="bx bx-right-arrow-alt"></i>Sales Per Faktur</a></li>
                            @endif

                            {{-- penjualan per customer per tahun -pending- --}}
                            {{-- @php
                                $queryPenjualanPerCustPerTahun = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 82,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryPenjualanPerCustPerTahun || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/penjualan-per-customer-per-tahun') }}"><i class="bx bx-right-arrow-alt"></i>Penjualan Per Customer Per Tahun</a></li>
                            @endif --}}

                            {{-- penjualan per customer per parts no fk & np --}}
                            @php
                                $queryPenjualanPerCustPerPartsNo = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 83,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryPenjualanPerCustPerPartsNo || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/penjualan-per-customer-per-parts-no') }}"><i class="bx bx-right-arrow-alt"></i>Penjualan Per Customer Per Parts No (FK & NP)</a></li>
                            @endif

                            {{-- penjualan per customer per parts no so & sj --}}
                            @php
                                $queryPenjualanPerCustPerPartsNoSoSj = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 119,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryPenjualanPerCustPerPartsNoSoSj || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/penjualan-per-customer-per-parts-no-so-sj') }}"><i class="bx bx-right-arrow-alt"></i>Penjualan Per Customer Per Parts No (SO & SJ)</a></li>
                            @endif

                            {{-- summary sales per branch per salesman --}}
                            @php
                                $querySummarySalesPerBranchPerSalesman = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 84,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySummarySalesPerBranchPerSalesman || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/summary-sales-per-branch-per-salesman') }}"><i class="bx bx-right-arrow-alt"></i>Summary Sales Per Branch Per Salesman</a></li>
                            @endif

                            {{-- summary sales per branch per customer --}}
                            @php
                                $querySummarySalesPerBranchPerCustomer = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 125,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySummarySalesPerBranchPerCustomer || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/summary-sales-per-branch-per-customer') }}"><i class="bx bx-right-arrow-alt"></i>Summary Sales Per Branch Per Customer</a></li>
                            @endif

                            {{-- summary penjualan per branch per brand --}}
                            @php
                                $querySummarySalesPerBranchPerBrand = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 85,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySummarySalesPerBranchPerBrand || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/summary-penjualan-per-branch-per-brand') }}"><i class="bx bx-right-arrow-alt"></i>Summary Sales Per Branch Per Brand</a></li>
                            @endif

                            {{-- sales actual vs target per branch --}}
                            @php
                                $querySalesActualVsTargetPerBranch = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 86,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySalesActualVsTargetPerBranch || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/sales-actual-vs-target-per-branch') }}"><i class="bx bx-right-arrow-alt"></i>Sales Actual VS Target Per Branch</a></li>
                            @endif

                            {{-- sales target per branch --}}
                            {{-- @php
                                $querySalesTargetPerBranch = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 87,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySalesTargetPerBranch || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/sales-target-per-branch') }}"><i class="bx bx-right-arrow-alt"></i>Sales Target Per Branch</a></li>
                            @endif --}}

                            {{-- sales target customer per branch --}}
                            {{-- @php
                                $querySalesTargetCustomerPerBranch = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 88,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySalesTargetCustomerPerBranch || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/sales-target-customer-per-branch') }}"><i class="bx bx-right-arrow-alt"></i>Sales Target Customer Per Branch</a></li>
                            @endif --}}

                            {{-- sales per customer per year --}}
                            @php
                                $querySalesPerCustPerYear = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 89,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySalesPerCustPerYear || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/sales-per-cust-per-year') }}"><i class="bx bx-right-arrow-alt"></i>Sales Per Customer Per Year</a></li>
                            @endif

                            {{-- sales per customer detail faktur --}}
                            @php
                                $querySalesPerCustDetailFaktur = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 90,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySalesPerCustDetailFaktur || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/penjualan-per-customer-detail-faktur') }}"><i class="bx bx-right-arrow-alt"></i>Sales Per Customer Detail Faktur</a></li>
                            @endif

                            {{-- retur penjualan detail --}}
                            @php
                                $queryReturPenjualanDetail = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 91,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryReturPenjualanDetail || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/retur-penjualan-detail') }}"><i class="bx bx-right-arrow-alt"></i>Retur Penjualan Detail</a></li>
                            @endif

                            {{-- summary retur penjualan --}}
                            @php
                                $querySummaryReturPenjualan = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 92,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($querySummaryReturPenjualan || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/summary-retur-penjualan') }}"><i class="bx bx-right-arrow-alt"></i>Summary Retur Penjualan</a></li>
                            @endif

                            {{-- overdue receivables per branch --}}
                            @php
                                $queryOverdueReceivablesPerBranch = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 111,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryOverdueReceivablesPerBranch || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/overdue-receivables-per-branch') }}"><i class="bx bx-right-arrow-alt"></i>Overdue Receivables Per Branch</a></li>
                            @endif

                            {{-- analyze receivables summary per branch --}}
                            @php
                                $queryAnalyzeReceivablesSummaryPerBranch = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 112,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryAnalyzeReceivablesSummaryPerBranch || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/analyze-receivables-summary-per-branch') }}">
                                    <i class="bx bx-right-arrow-alt"></i>Summary Analisa Piutang Per Cabang</a></li>
                            @endif
                        </ul>
                    @endif
                </li>
                <li>
                    @php
                    // Purchase report
                    $query = \App\Models\Mst_menu_user::whereIn('menu_id', [93,94,95,96,97,98,99,100,104,115])
                        ->where([
                        'user_id' => Auth::user()->id,
                        'user_access_read' => 'Y',
                        ])
                        ->first();
                    @endphp
                    @if ($query || Auth::user()->id==1)
                        <a href="javascript:;" class="has-arrow">
                            <div class="parent-icon"><i class="bx bx-purchase-tag-alt" style="color: #5d8c42;"></i></div>
                            <div class="menu-title">Purchase Report</div>
                        </a>
                        <ul>
                            {{-- purchase retur --}}
                            @php
                                $queryPurchaseRetur = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 93,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryPurchaseRetur || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/purchase-retur-rpt') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Retur</a></li>
                            @endif

                            {{-- purchase per supplier per branch --}}
                            @php
                                $queryPurchasePerSupplierPerBranch = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 94,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryPurchasePerSupplierPerBranch || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/purchase-per-supplier-per-cabang') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Per Supplier Per Branch</a></li>
                            @endif

                            {{-- purchase per supplier per parts no --}}
                            @php
                                $queryPurchasePerSupplierPerPartsNo = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 95,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryPurchasePerSupplierPerPartsNo || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/purchase-per-supplier-per-parts-no') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Per Supplier Per Parts No</a></li>
                            @endif

                            {{-- purchase per supplier per year --}}
                            @php
                                $queryPurchasePerSupplierPerYear = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 104,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryPurchasePerSupplierPerYear || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/purchase-per-supplier-per-year') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Per Supplier Per Year</a></li>
                            @endif

                            {{-- purchase summary per supplier --}}
                            @php
                                $queryPurchaseSummaryPerSupplier = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 96,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryPurchaseSummaryPerSupplier || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/purchase-summary-per-supplier') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Summary Per Supplier</a></li>
                            @endif

                            {{-- purchase summary per branch per brand --}}
                            @php
                                $queryPurchaseSummaryPerBranchPerBrand = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 97,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryPurchaseSummaryPerBranchPerBrand || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/purchase-summary-per-branch-per-brand') }}"><i class="bx bx-right-arrow-alt"></i>Purchase Summary Per Branch Per Brand</a></li>
                            @endif

                            {{-- outstanding purchase order --}}
                            @php
                                $queryOutstandingPurchaseOrder = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 98,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryOutstandingPurchaseOrder || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/outstanding-purchase-order-ps') }}"><i class="bx bx-right-arrow-alt"></i>Oustanding Purchase Order</a></li>
                            @endif

                            {{-- debt overdue per branch --}}
                            @php
                                $queryDebtOverduePerBranch = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 99,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryDebtOverduePerBranch || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/debt-overdue-per-branch') }}"><i class="bx bx-right-arrow-alt"></i>Debt Overdue Per Branch</a></li>
                            @endif

                            {{-- analyze debt summary per branch --}}
                            @php
                                $queryDebtOverduePerBranch = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 100,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryDebtOverduePerBranch || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/analyze-debt-summary-per-branch') }}"><i class="bx bx-right-arrow-alt"></i>Summary Analisa Hutang Per Cabang</a></li>
                            @endif

                            {{-- supplier payment status 01 --}}
                            @php
                                $queryPurchaseSupplierPaymentStatus = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 115,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryPurchaseSupplierPaymentStatus || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/purchase-supplier-payment-status') }}"><i class="bx bx-right-arrow-alt"></i>Supplier Payment Status (Old)</a></li>
                            @endif

                        </ul>
                    @endif
                </li>
                <li>
                    @php
                        // Finance Report
                        $query = \App\Models\Mst_menu_user::whereIn('menu_id', [105,106,107,108,109,110,114,118,122,123])
                        ->where([
                            'user_id' => Auth::user()->id,
                            'user_access_read' => 'Y',
                        ])
                        ->first();
                    @endphp
                    @if ($query || Auth::user()->id==1)
                        <a href="javascript:;" class="has-arrow">
                            <div class="parent-icon"><i class="bx bx-abacus" style="color: #5d8c42;"></i></div>
                            <div class="menu-title">Finance Report</div>
                        </a>
                        <ul>
                            {{-- transaction per account --}}
                            @php
                                $queryTxPerAccount = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 106,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryTxPerAccount || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/rpt-finance-transaction-per-account') }}"><i class="bx bx-right-arrow-alt"></i>Transaction Per Account</a></li>
                            @endif
                            {{-- journal --}}
                            @php
                                $queryJournal = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 105,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryJournal || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/rpt-finance-journal') }}"><i class="bx bx-right-arrow-alt"></i>Journal</a></li>
                            @endif
                            {{-- transaction journal --}}
                            @php
                                $queryTxJournal = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 107,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryTxJournal || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/rpt-finance-transaction-journal') }}"><i class="bx bx-right-arrow-alt"></i>Transaction Journal</a></li>
                            @endif
                            {{-- general ledger --}}
                            @php
                                $queryGeneral = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 108,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryGeneral || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/rpt-finance-general-ledger') }}"><i class="bx bx-right-arrow-alt"></i>General Ledger</a></li>
                            @endif
                            {{-- operating expenses --}}
                            @php
                                $queryOperatingExpenses = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 109,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryOperatingExpenses || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/rpt-finance-operating-expenses') }}"><i class="bx bx-right-arrow-alt"></i>Operating Expenses</a></li>
                            @endif
                            {{-- income statement --}}
                            @php
                                $queryIncomeStatement = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 110,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryIncomeStatement || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/rpt-finance-income-statement') }}"><i class="bx bx-right-arrow-alt"></i>Income Statement</a></li>
                            @endif

                            {{-- balance sheet --}}
                            @php
                                $queryIncomeStatement = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 114,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryIncomeStatement || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/rpt-balance-sheet') }}"><i class="bx bx-right-arrow-alt"></i>Balance Sheet</a></li>
                            @endif

                            {{-- cash flow --}}
                            @php
                                $queryCashFlow = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 118,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryCashFlow || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/rpt-cash-flow') }}"><i class="bx bx-right-arrow-alt"></i>Cash Flow</a></li>
                            @endif

                            {{-- cash flow test --}}
                            @php
                                $queryCashFlow = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 118,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryCashFlow || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/rpt-cash-flow-dbg') }}"><i class="bx bx-right-arrow-alt"></i>Cash Flow (Test)</a></li>
                            @endif

                            {{-- kartu hutang --}}
                            @php
                                $queryKartuHutang = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 122,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryKartuHutang || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/rpt-finance-kartu-hutang') }}"><i class="bx bx-right-arrow-alt"></i>{{ ucwords(strtolower(env('KARTU_HUTANG'))) }}</a></li>
                            @endif

                            {{-- kartu piutang --}}
                            @php
                                $queryKartuPiutang = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 123,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryKartuPiutang || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/rpt-finance-kartu-piutang') }}"><i class="bx bx-right-arrow-alt"></i>{{ ucwords(strtolower(env('KARTU_PIUTANG'))) }}</a></li>
                            @endif

                            {{-- customer payment status --}}
                            @php
                                $queryCustPaymentStatus = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 124,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryCustPaymentStatus || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/customer-payment-status') }}">
                                    <i class="bx bx-right-arrow-alt"></i>Customer Payment Status</a></li>
                            @endif

                            {{-- supplier payment status 02 --}}
                            @php
                                $queryPurchaseSupplierPaymentStatus = \App\Models\Mst_menu_user::where([
                                    'menu_id' => 115,
                                    'user_id' => Auth::user()->id,
                                    'user_access_read' => 'Y',
                                ])
                                ->first();
                            @endphp
                            @if ($queryPurchaseSupplierPaymentStatus || Auth::user()->id==1)
                                <li><a href="{{ url(ENV('REPORT_FOLDER_NAME').'/purchase-supplier-payment-status-02') }}"><i class="bx bx-right-arrow-alt"></i>Supplier Payment Status</a></li>
                            @endif
                        </ul>
                    @endif
                </li>
            @endif
        @endif

        @if (Auth::user()->id==1 || Auth::user()->email=='sulian@intimotor.com' || 
            Auth::user()->email=='sujayadi.office@gmail.com' ||
            Auth::user()->email=='maeger@koidigital.co.id')
            <li class="menu-label">Superuser Only</li>
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-user-check" style="color: #85a341;"></i></div>
                    <div class="menu-title">User Administration</div>
                </a>
                {{-- <ul>
                    <li>
                        <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/menu') }}"><i class="bx bx-right-arrow-alt"></i>Menu Access</a>
                    </li>
                </ul>
                <ul>
                    <li>
                        <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/user-access') }}"><i class="bx bx-right-arrow-alt"></i>User</a>
                    </li>
                </ul> --}}
                <ul>
                    <li class="{{ strpos(url()->current(),"/user-management/")>0?'mm-active':'' }}">
                        <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/user-management') }}"><i class="bx bx-right-arrow-alt"></i>User Management</a>
                    </li>
                    <li class="{{ strpos(url()->current(),"/user-management/")>0?'mm-active':'' }}">
                        <a href="https://docs.google.com/spreadsheets/d/1VrkQxO7ttRY1iUukPauL2ajpziaa2i29C8IzCpEoN34/edit?usp=sharing" target="_new"><i class="bx bx-right-arrow-alt"></i>Problem Solver</a>
                    </li>
                </ul>
            </li>
        @endif
        @if ((Auth::user() ? 1 : 0)==1)
            <li>
                <a href="{{ url('sign-out') }}">
                    <div class="parent-icon"><i class='bx bx-log-out'></i></div>
                    <div class="menu-title">Sign Out</div>
                </a>
            </li>
        @endif
    </ul>
    <!--end navigation-->
</div>
<!--end sidebar wrapper -->
