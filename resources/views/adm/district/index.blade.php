@extends('layouts.app')

@section('style')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
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
            @include('adm.district.breadcrumb')
            <!--end breadcrumb-->
            <h6 class="mb-0 text-uppercase">District</h6>
            <hr>
            <form action="{{ url('admin/district/find-district') }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                <div class="row col-12" style="margin-bottom: 15px;">
                    {{-- <div class="col-md-3">
                        <label for="country_id" class="form-label">Country</label>
                        <select class="form-select single-select @error('country_id') is-invalid @enderror" id="country_id"
                            name="country_id">
                            <option value="#">Choose Country</option>
                            @php
                                $countryId = 9999;
                            @endphp
                            @if (old('country_id'))
                                @php
                                    $countryId = old('country_id');
                                @endphp
                            @endif
                            @foreach ($country as $c)
                                <option @if ($countryId == $d->id) {{ 'selected' }} @endif
                                    value="{{ $d->id }}">{{ $d->country_name }}</option>
                            @endforeach
                        </select>
                        @error('country_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div> --}}
                    <div class="col-md-3">
                        <label for="province_id" class="form-label">Province</label>
                        <select id="province_id" name="province_id" class="form-select single-select @error('province_id') is-invalid @enderror">
                            <option value="#">Choose Province</option>
                            @php
                                $provinceId = old('province_id')?old('province_id'):0;
                            @endphp
                            @foreach ($province as $p)
                                <option @if ($provinceId == $p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->province_name }}</option>
                            @endforeach
                        </select>
                        @error('province_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="city_id" class="form-label">City</label>
                        <select id="city_id" name="city_id" class="form-select single-select @error('city_id') is-invalid @enderror">
                            <option value="#">Choose City</option>
                            @php
                                $cityId = old('city_id')?old('city_id'):0;
                            @endphp
                            @foreach ($city as $c)
                                <option @if ($cityId == $c->id) {{ 'selected' }} @endif value="{{ $c->id }}">{{ $c->city_type.' '.$c->city_name }}</option>
                            @endforeach
                        </select>
                        @error('city_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-3">
                        <label for="district" class="form-label">Or Find District</label>
                        <input type="text" class="form-control @error('district') is-invalid @enderror" id="district"
                            name="district" maxlength="128" value="@if (old('district')) {{ old('district') }}@else{{ $district_name }} @endif">
                        @error('district')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-3" style="margin-top: 15px;">
                        <button type="submit" class="btn btn-primary px-5">Display District</button>
                    </div>
                </div>
            </form>
            <hr />
            <form name="form_del" id="form-del" action="{{ url('/del_districtparam?next_uri='.urlencode($uri)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                <input type="hidden" name="all_ids" id="all_ids">
                @csrf
                <div class="col-12">
                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/district/create') }}" class="btn btn-primary px-5" style="margin-bottom: 15px;">Add New</a>
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
                            <table id="district-datatable" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">District Name</th>
                                        <th style="width: 30%;">City</th>
                                        <th style="width: 30%;">Province</th>
                                        {{-- <th>Country</th> --}}
                                        <th style="width: 5%;">Action</th>
                                        <th style="width: 5%;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($district as $d)
                                        <tr>
                                            <td>
                                                {{ $d->district_name }}&nbsp;(ID: {{ $d->id }})
                                                <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $d->district_name }}">
                                                <input type="hidden" name="district_id{{ $i }}" id="district_id{{ $i }}" value="{{ $d->id }}">
                                            </td>
                                            <td>{{ $d->city->city_type.' '.$d->city->city_name.' (ID: '.$d->city_id.')' }}
                                            </td>
                                            <td>{{ $d->city->province->province_name }}</td>
                                            {{-- <td>{{ $d->city->province->country->country_name }}</td> --}}
                                            {{-- <td>{{ $d->active }}</td>
                                            <td>{{ date_format(date_create($d->updated_at), 'd M Y H:i:s') }}</td> --}}
                                            <td>
                                                <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.$d->id) }}" style="text-decoration: underline;">View</a>
                                                @if ($d->active=='Y')
                                                    |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.$d->id.'/edit') }}" style="text-decoration: underline;">Edit</a>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($d->active=='Y')
                                                    <input type="checkbox" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                @else
                                                    {{ 'Deleted' }}
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
                                        <th>District Name</th>
                                        <th>City</th>
                                        <th>Province</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                    </tr>
                                </tfoot> --}}
                            </table>
                        </div>
                        {{-- <hr />
                        <div class="input-group" style="margin-top: 15px;">
                            <a download="" href="{{ url(ENV('ADMIN_FOLDER_NAME').'/district/district-export-xlsx') }}"
                                class="btn btn-light px-5" style="margin-bottom: 15px;">Export to Excel</a>
                        </div>
                        <form action="{{ url(ENV('ADMIN_FOLDER_NAME').'/district/district-import') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="input-group" style="margin-top: 15px;">
                                <input type="file" class="form-control @error('xlsx_file') is-invalid @enderror"
                                    id="xlsx_file" name="xlsx_file" aria-describedby="inputGroupFileAddon04"
                                    aria-label="Upload">
                                <button class="btn btn-light" type="submit" id="inputGroupFileAddon04">Import from
                                    Excel</button>
                                @error('xlsx_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </form> --}}
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!--end page wrapper -->
@endsection

@section('script')
    <script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#district-datatable').DataTable();

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
                                aId += $("#district_id" + i).val()+',';
                            }
                        }
                        if(aId!==''){
                            $("#all_ids").val(aId);
                            $("#form-del").submit();
                        }
                    }
                }
            });

            $('.single-select').select2({
                theme: 'bootstrap4',
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                allowClear: Boolean($(this).data('allow-clear')),
            });

            // $('#country_id').change(function() {
            //     $("#province_id").empty();
            //     $("#city_id").empty();
            //     var fd = new FormData();
            //     fd.append('country_id', $('#country_id option:selected').val());
            //     $.ajax({
            //         url: '{{ url('disp_province') }}',
            //         type: 'POST',
            //         enctype: 'application/x-www-form-urlencoded',
            //         data: fd,
            //         cache: false,
            //         contentType: false,
            //         processData: false,
            //         dataType: 'json',
            //         success: function(res) {
            //             let o = res[0].province;
            //             let totProvince = o.length;
            //             $("#province_id").append(
            //                 `<option value="#">Choose...</option>`
            //             );
            //             $("#city_id").append(
            //                 `<option value="#">Choose...</option>`
            //             );
            //             if (totProvince > 0) {
            //                 for (let i = 0; i < totProvince; i++) {
            //                     optionText = o[i].province_name;
            //                     optionValue = o[i].id;
            //                     $("#province_id").append(
            //                         `<option value="${optionValue}">${optionText}</option>`
            //                     );
            //                 }
            //             }
            //         },
            //     });
            // });

            $('#province_id').change(function() {
                $("#city_id").empty();
                var fd = new FormData();
                fd.append('province_id', $('#province_id option:selected').val());
                $.ajax({
                    url: '{{ url('disp_city') }}',
                    type: 'POST',
                    enctype: 'application/x-www-form-urlencoded',
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(res) {
                        let o = res[0].city;
                        let totCity = o.length;
                        $("#city_id").append(
                            `<option value="#">Choose...</option>`
                        );
                        if (totCity > 0) {
                            for (let i = 0; i < totCity; i++) {
                                optionText = o[i].city_type + ' ' + o[i].city_name;
                                optionValue = o[i].id;
                                $("#city_id").append(
                                    `<option value="${optionValue}">${optionText}</option>`
                                );
                            }
                        }
                    },
                });
            });
        });
    </script>
@endsection
