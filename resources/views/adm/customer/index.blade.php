@extends('layouts.app')

@section('style')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
@endsection

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.'.$folder.'.breadcrumb')
            <!--end breadcrumb-->
            <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
            <hr />
            {{-- <form name="form_del" id="form-del" action="{{ url('/del_customer?next_uri='.urlencode($folder)) }}" method="POST" enctype="application/x-www-form-urlencoded"> --}}
                <input type="hidden" name="all_ids" id="all_ids">
                @csrf
                <div class="col-12">
                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/create') }}" class="btn btn-primary px-5" style="margin-bottom: 15px;">Add New</a>
                    <a id="btn-del-row" class="btn btn-danger px-5" style="margin-bottom: 15px;">Delete</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif
                        @if (session('status-error'))
                            <div class="alert alert-danger">
                                {{ session('status-error') }}
                            </div>
                        @endif
                        <div class="table-responsive">
                            <table id="district" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">Code</th>
                                        <th style="width: 30%;">Customer</th>
                                        <th style="width: 30%;">Office Address</th>
                                        <th style="width: 15%;">PIC</th>
                                        <th style="width: 5%;">Branch</th>
                                        <th style="width: 5%;">Salesman</th>
                                        {{-- <th style="width: 10%;">Email</th>
                                        <th style="width: 5%;">Phone</th>
                                        <th style="width: 5%;">Customer Code</th> --}}
                                        {{-- <th>Active</th> --}}
                                        {{-- <th>Last Updated At</th> --}}
                                        <th style="width: 5%;">Action</th>
                                        <th style="width: 5%;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($customers as $c)
                                        <tr>
                                            <td>{{ $c->customer_unique_code }}</td>
                                            <td>
                                                <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $c->name }}">
                                                <input type="hidden" name="customer_id{{ $i }}" id="customer_id{{ $i }}" value="{{ $c->id }}">
                                                {{ $c->name }}
                                            </td>
                                            <td>
                                                {!! $c->office_address.', '.ucwords(strtolower($c->subdistrict->sub_district_name)).', '.
                                                $c->district->district_name.'<br/>'.$c->city->city_type.' '.$c->city->city_name.'<br/>'.
                                                $c->province->province_name.' '.($c->subdistrict->post_code != '000000' ? $c->subdistrict->post_code : '') !!}
                                            </td>
                                            <td>{!! $c->pic1_name.'<br/>'.$c->pic1_phone.'<br/>'.$c->pic1_email !!}</td>
                                            <td>{{ !is_null($c->branch)?$c->branch->initial:'' }}</td>
                                            <td>{{ $c->salesman01?$c->salesman01->initial:'-' }}</td>
                                            {{-- <td>{{ $c->cust_email }}</td>
                                            <td>{{ $c->phone1 }}</td>
                                            <td>{{ $c->customer_unique_code }}</td> --}}
                                            {{-- <td>{{ $c->active }}</td> --}}
                                            {{-- <td>{{ date_format(date_create($c->updated_at), 'd M Y H:i:s') }}</td> --}}
                                            <td>
                                                @php
                                                    $direksi = false;
                                                    $kacab = false;
                                                    $qUser = \App\Models\Userdetail::where('user_id','=',Auth::user()->id)
                                                    ->first();
                                                    if($qUser){
                                                        if($qUser->is_director=='Y'){$direksi = true;}
                                                        if($qUser->is_branch_head=='Y'){$kacab = true;}
                                                    }
                                                @endphp
                                                <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.urlencode($c->slug)) }}" style="text-decoration: underline;">View</a>
                                                |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.urlencode($c->slug).'/edit') }}" style="text-decoration: underline;">Edit</a>
                                                {{-- @if ($c->active=='Y' && ($c->created_by==Auth::user()->id || Auth::user()->id==1 || $direksi || $kacab))
                                                @endif --}}
                                            </td>
                                            <td>
                                                @php
                                                    $isNotFree = false;
                                                    $q = \App\Models\Tx_delivery_order::where('customer_id','=',$c->id)
                                                    ->first();
                                                    if($q){$isNotFree = true;}

                                                    $q = \App\Models\Tx_invoice::where('customer_id','=',$c->id)
                                                    ->first();
                                                    if($q){$isNotFree = true;}

                                                    $q = \App\Models\Tx_nota_retur::where('customer_id','=',$c->id)
                                                    ->first();
                                                    if($q){$isNotFree = true;}

                                                    $q = \App\Models\Tx_payment_receipt::where('customer_id','=',$c->id)
                                                    ->first();
                                                    if($q){$isNotFree = true;}

                                                    $q = \App\Models\Tx_sales_order::where('customer_id','=',$c->id)
                                                    ->first();
                                                    if($q){$isNotFree = true;}

                                                    $q = \App\Models\Tx_sales_quotation::where('customer_id','=',$c->id)
                                                    ->first();
                                                    if($q){$isNotFree = true;}
                                                @endphp
                                                @if ($c->active=='Y' && !$isNotFree && ($c->created_by==Auth::user()->id || Auth::user()->id==1 || $direksi || $kacab))
                                                    <input type="checkbox" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                @else
                                                    {{-- {{ 'Deleted' }} --}}
                                                    <input type="Hidden" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                @endif
                                            </td>
                                        </tr>
                                        @php
                                            $i += 1;
                                        @endphp
                                    @endforeach
                                </tbody>
                                {{-- <tfoot>
                                    <tr>
                                        <th>Code</th>
                                        <th>Customer</th>
                                        <th>Office Address</th>
                                        <th>PIC</th>
                                        <th>Branch</th>
                                        <th>Salesman</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                    </tr>
                                </tfoot> --}}
                            </table>
                        </div>
                        <hr />
                        @if (Auth::user()->id==1)
                            <div class="input-group" style="margin-top: 15px;">
                                <a download=""
                                    href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/customer-export-xlsx') }}"
                                    class="btn btn-primary px-5" style="margin-bottom: 15px;">Export to Excel</a>
                            </div>
                            <form action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$folder.'-import') }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="input-group" style="margin-top: 15px;">
                                    <input type="file" class="form-control @error('xlsx_file') is-invalid @enderror"
                                        id="xlsx_file" name="xlsx_file" aria-describedby="inputGroupFileAddon04"
                                        aria-label="Upload">
                                    <button class="btn btn-primary" type="submit" id="inputGroupFileAddon04">Import from
                                        Excel</button>
                                    @error('xlsx_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            {{-- </form> --}}
        </div>
    </div>
    <!--end page wrapper -->
@endsection

@section('script')
    <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#district').DataTable({
                'ordering':false,
            });

            $("#btn-del-row").click(function() {
                let rowNo = '';
                for (i = 0; i < {{ $rowCount }}; i++) {
                    if ($("#delRow" + i).is(':checked')) {
                        rowNo += '- '+$("#title_caption" + i).val()+'\n';
                    }
                }
                if(rowNo!=''){
                    let msg = 'The following {{ $title }} will be deleted.\n'+rowNo+'\nProcess cannot be undone. Continue?';
                    if(!confirm(msg)){
                        event.preventDefault();
                    }else{
                        let aId = '';
                        for (i = 0; i < {{ $rowCount }}; i++) {
                            if ($("#delRow" + i).is(':checked')) {
                                aId += $("#customer_id" + i).val()+',';
                            }
                        }
                        if(aId!==''){
                            $("#all_ids").val(aId);
                            $("#form-del").submit();
                        }
                    }
                }
            });
        });
    </script>
@endsection
