@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet"
    href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
<style>
    .select2-selection {
        height: 38px !important;
        font-size: 1rem;
    }
</style>
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include('tx.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            {{-- <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($p_inquiries->slug)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT') --}}
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <div class="border p-4 rounded">
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Purchase Inquiry No</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $p_inquiries->purchase_inquiry_no }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Purchase Inquiry Date</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($p_inquiries->purchase_inquiry_date), 'd/m/Y') }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Supplier</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $p_inquiries->supplier->name }}</label>
                                </div>
                                <div id="supplier_data" class="row mb-3">
                                    <label for="supplier_data" class="col-sm-3 col-form-label">Information</label>
                                    <div id="supplier_info" class="col-sm-9">
                                        @if(old('supplier_id') || @isset($supplierPics[0]))
                                            {!!
                                            (!is_null($supplierPics[0]->entity_type)?$supplierPics[0]->entity_type->title_ind.' ':'').$supplierPics[0]->name.
                                            '<br />Address: '.$supplierPics[0]->office_address.
                                            ($supplierPics[0]->subdistrict->sub_district_name=='Other'?'':
                                            ', '.ucwords(strtolower($supplierPics[0]->subdistrict->sub_district_name))).
                                            ($supplierPics[0]->district->district_name=='Other'?'':
                                            ', '.$supplierPics[0]->district->district_name).
                                            ($supplierPics[0]->city->city_name=='Other'?'':
                                            '<br />'.($supplierPics[0]->city->city_type=='Luar
                                            Negeri'?'':$supplierPics[0]->city->city_type).' '.
                                            $supplierPics[0]->city->city_name).
                                            ($supplierPics[0]->province->province_name=='Other'?'':
                                            '<br />'.$supplierPics[0]->province->province_name).
                                            '<br />'.$supplierPics[0]->country->country_name.
                                            ($supplierPics[0]->subdistrict->post_code=='000000'?'':
                                            ' '.$supplierPics[0]->subdistrict->post_code)
                                            !!}
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">PIC</label>
                                    <label for="" class="col-sm-9 col-form-label">
                                        @if ($p_inquiries->pic_idx==1)
                                            {{ $p_inquiries->supplier->pic1_name }}
                                        @else
                                            {{ $p_inquiries->supplier->pic2_name }}
                                        @endif
                                    </label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Header</label>
                                    <label for="" class="col-sm-9 col-form-label">{!! $p_inquiries->header !!}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Footer</label>
                                    <label for="" class="col-sm-9 col-form-label">{!! $p_inquiries->footer !!}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Remark</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $p_inquiries->remark }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-0 text-uppercase">Part Detail</h6>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @php
                                $totRow = $totalRow;
                            @endphp
                            <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 5%;text-align:center;">#</th>
                                        <th scope="col" style="width: 35%;">Part Name</th>
                                        <th scope="col" style="width: 10%;">Qty</th>
                                        <th scope="col" style="width: 10%;">Unit</th>
                                        <th scope="col" style="width: 40%;">Description</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($p_inquiryParts as $part)
                                        <tr id="row{{ $i }}">
                                            <th scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $i+1 }}.</label></th>
                                            <td>
                                                <label for="" class="col-form-label">{{ $part->part_name }}</label>
                                            </td>
                                            <td>
                                                <label for="" class="col-form-label">{{ $part->qty }}</label>
                                            </td>
                                            <td>
                                                <label for="" class="col-form-label">{{ $part->unit }}</label>
                                            </td>
                                            <td>
                                                <label for="" class="col-form-label">{{ $part->description }}</label>
                                            </td>
                                        </tr>
                                        @php
                                            $i += 1;
                                        @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="back-btn" class="btn btn-secondary px-5" value="Cancel">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {{-- </form> --}}
        </div>
        <!--end row-->
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#back-btn").click(function() {
            // history.back();
            location.href = '{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}';
        });
    });
</script>
@endsection
