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
            <form name="form_del" id="form-del" action="{{ url('/del_branch_target?next_uri='.urlencode($folder)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                <input type="hidden" name="all_ids" id="all_ids">
                @csrf
                <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/create') }}" class="btn btn-primary px-5" style="margin-bottom: 15px;">Add New</a>
                    {{-- <a id="btn-del-row" class="btn btn-light px-5" style="margin-bottom: 15px;">Delete</a> --}}
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
                            <table id="branch-target-datatable" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Target Year</th>
                                        <th style="width: 75%;">Sales Target ({{ $qCurrency->string_val }})</th>
                                        <th style="width: 10%;">Action</th>
                                        {{-- <th style="width: 5%;">Status</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($branch_targets as $b)
                                        <tr>
                                            <td style="text-align: center;">{{ $b->year }}</td>
                                            <td style="text-align: right;">
                                                <input type="hidden" name="branch_target_id{{ $i }}" id="branch_target_id{{ $i }}" value="{{ $b->id }}">
                                                <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $b->year }}">
                                                {{ number_format($b->sales_target,0,'.',',') }}
                                            </td>
                                            <td style="text-align: left;">
                                                <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$b->id) }}" style="text-decoration: underline;">View</a>
                                                @if ($b->active=='Y')
                                                    |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$b->id.'/edit') }}" style="text-decoration: underline;">Edit</a>
                                                @endif
                                            </td>
                                            {{-- <td style="text-align: center;">
                                                @if ($b->active=='Y')
                                                    <input type="checkbox" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                @else
                                                    {{ 'Deleted' }}
                                                    <input type="Hidden" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                @endif
                                            </td> --}}
                                        </tr>
                                        @php
                                            $i += 1;
                                        @endphp
                                    @endforeach
                                </tbody>
                                {{-- <tfoot>
                                    <tr>
                                        <th>Target Year</th>
                                        <th>Sales Target ({{ $qCurrency->string_val }})</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot> --}}
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!--end page wrapper -->
@endsection

@section('script')
    <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#branch-target-datatable').DataTable({
                'ordering': false,
            });
        });
    </script>
@endsection
