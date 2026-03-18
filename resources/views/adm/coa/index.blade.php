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
        <form name="form_del" id="form-del" action="{{ url('/del_coa?next_uri='.urlencode($folder)) }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <input type="hidden" name="all_ids" id="all_ids">
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
                        <table id="coa-table" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    {{-- <th>Level</th> --}}
                                    <th style="width: 10%;">COA Code</th>
                                    <th>COA Name</th>
                                    <th style="width: 5%;">Action</th>
                                    <th style="width: 5%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($coas as $b)
                                    <tr>
                                        {{-- <td>{{ $b->coa_level }}</td> --}}
                                        <td>{{ ($b->is_draft=='Y'?'Draft':'').$b->coa_code }}</td>
                                        <td>
                                            {{ $b->coa_name }}
                                            <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $b->coa_name }}">
                                            <input type="hidden" name="coa_id{{ $i }}" id="coa_id{{ $i }}" value="{{ $b->id }}">
                                        </td>
                                        <td>
                                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$b->id) }}" style="text-decoration: underline;">View</a>
                                            @if ($b->active=='Y')
                                                |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$b->id.'/edit') }}" style="text-decoration: underline;">Edit</a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($b->active=='Y')
                                                <input type="checkbox" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                            @else
                                                {{ 'Deleted' }}
                                                <input type="Hidden" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                            @endif
                                        </td>
                                    </tr>
                                    {{-- Lvl 2 --}}
                                    @php
                                        $qLvl2 = \App\Models\Mst_coa::where([
                                            'coa_parent' => $b->id,
                                            // 'coa_level' => 2,
                                            'active' => 'Y',
                                        ])
                                        ->get();
                                    @endphp
                                    @foreach ($qLvl2 as $q2)
                                        @php
                                            $i += 1;
                                        @endphp
                                        <tr>
                                            {{-- <td>{{ $b->coa_level }}</td> --}}
                                            <td>{{ ($q2->is_draft=='Y'?'Draft':'').$q2->coa_code_complete }}</td>
                                            <td>
                                                {!! '&nbsp;&nbsp;&nbsp;'.$q2->coa_name !!}
                                                <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $q2->coa_name }}">
                                                <input type="hidden" name="coa_id{{ $i }}" id="coa_id{{ $i }}" value="{{ $q2->id }}">
                                            </td>
                                            <td>
                                                <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$q2->id) }}" style="text-decoration: underline;">View</a>
                                                @if ($q2->active=='Y')
                                                    |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$q2->id.'/edit') }}" style="text-decoration: underline;">Edit</a>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($q2->active=='Y')
                                                    <input type="checkbox" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                @else
                                                    {{ 'Deleted' }}
                                                    <input type="Hidden" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                @endif
                                            </td>
                                        </tr>
                                        {{-- Lvl 3 --}}
                                        @php
                                            $qLvl3 = \App\Models\Mst_coa::where([
                                                'coa_parent' => $q2->id,
                                                // 'coa_level' => 3,
                                                'active' => 'Y',
                                            ])
                                            ->get();
                                        @endphp
                                        @foreach ($qLvl3 as $q3)
                                            @php
                                                $i += 1;
                                            @endphp
                                            <tr>
                                                {{-- <td>{{ $b->coa_level }}</td> --}}
                                                <td>{{ ($q3->is_draft=='Y'?'Draft':'').$q3->coa_code_complete }}</td>
                                                <td>
                                                    {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$q3->coa_name !!}
                                                    <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $q3->coa_name }}">
                                                    <input type="hidden" name="coa_id{{ $i }}" id="coa_id{{ $i }}" value="{{ $q3->id }}">
                                                </td>
                                                <td>
                                                    <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$q3->id) }}" style="text-decoration: underline;">View</a>
                                                    @if ($q3->active=='Y')
                                                        |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$q3->id.'/edit') }}" style="text-decoration: underline;">Edit</a>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($q3->active=='Y')
                                                        <input type="checkbox" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                    @else
                                                        {{ 'Deleted' }}
                                                        <input type="Hidden" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                    @endif
                                                </td>
                                            </tr>
                                            {{-- Lvl 4 --}}
                                            @php
                                                $qLvl4 = \App\Models\Mst_coa::where([
                                                    'coa_parent' => $q3->id,
                                                    // 'coa_level' => 4,
                                                    'active' => 'Y',
                                                ])
                                                ->get();
                                            @endphp
                                            @foreach ($qLvl4 as $q4)
                                                @php
                                                    $i += 1;
                                                @endphp
                                                <tr>
                                                    {{-- <td>{{ $b->coa_level }}</td> --}}
                                                    <td>{{ ($q4->is_draft=='Y'?'Draft':'').$q4->coa_code_complete }}</td>
                                                    <td>
                                                        {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$q4->coa_name !!}
                                                        <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $q4->coa_name }}">
                                                        <input type="hidden" name="coa_id{{ $i }}" id="coa_id{{ $i }}" value="{{ $q4->id }}">
                                                    </td>
                                                    <td>
                                                        <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$q4->id) }}" style="text-decoration: underline;">View</a>
                                                        @if ($q4->active=='Y')
                                                            |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$q4->id.'/edit') }}" style="text-decoration: underline;">Edit</a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($q4->active=='Y')
                                                            <input type="checkbox" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                        @else
                                                            {{ 'Deleted' }}
                                                            <input type="Hidden" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                        @endif
                                                    </td>
                                                </tr>
                                                {{-- Lvl 5 --}}
                                                @php
                                                    $qLvl5 = \App\Models\Mst_coa::where([
                                                        'coa_parent' => $q4->id,
                                                        // 'coa_level' => 5,
                                                        'active' => 'Y',
                                                    ])
                                                    ->get();
                                                @endphp
                                                @foreach ($qLvl5 as $q5)
                                                    @php
                                                        $i += 1;
                                                    @endphp
                                                    <tr>
                                                        {{-- <td>{{ $b->coa_level }}</td> --}}
                                                        <td>{{ ($q5->is_draft=='Y'?'Draft':'').$q5->coa_code_complete }}</td>
                                                        <td>
                                                            {!! '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$q5->coa_name !!}
                                                            <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $q5->coa_name }}">
                                                            <input type="hidden" name="coa_id{{ $i }}" id="coa_id{{ $i }}" value="{{ $q5->id }}">
                                                        </td>
                                                        <td>
                                                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$q5->id) }}" style="text-decoration: underline;">View</a>
                                                            @if ($q5->active=='Y')
                                                                |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$q5->id.'/edit') }}" style="text-decoration: underline;">Edit</a>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($q5->active=='Y')
                                                                <input type="checkbox" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                            @else
                                                                {{ 'Deleted' }}
                                                                <input type="Hidden" name="delRow{{ $i }}" id="delRow{{ $i }}">
                                                            @endif
                                                        </td>
                                                    </tr>

                                                @endforeach
                                                {{-- Lvl 5 --}}

                                            @endforeach
                                            {{-- Lvl 4 --}}

                                        @endforeach
                                        {{-- Lvl 3 --}}

                                    @endforeach
                                    {{-- Lvl 2 --}}
                                    @php
                                        $i += 1;
                                    @endphp
                                @endforeach
                                {{-- @foreach ($coasParent0 as $b)
                                    <tr>
                                        <td>{{ ($b->is_draft=='Y'?'Draft':'').$b->coa_code }}</td>
                                        <td>
                                            {{ $b->coa_name }}
                                            <input type="hidden" name="title_caption{{ $i }}" id="title_caption{{ $i }}" value="{{ $b->coa_name }}">
                                            <input type="hidden" name="coa_id{{ $i }}" id="coa_id{{ $i }}" value="{{ $b->id }}">
                                        </td>
                                        <td>
                                            <a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$b->id) }}" style="text-decoration: underline;">View</a>
                                            @if ($b->active=='Y')
                                                |&nbsp;<a href="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$b->id.'/edit') }}" style="text-decoration: underline;">Edit</a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($b->active=='Y')
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
                                @endforeach --}}
                            </tbody>
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
        $('#coa-table').DataTable({
            "ordering": false,
        });

        $("#btn-del-row").click(function() {
            let rowNo = '';
            for (i = 0; i < {{ $i }}; i++) {
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
                    for (i = 0; i < {{ $i }}; i++) {
                        if ($("#delRow" + i).is(':checked')) {
                            aId += $("#coa_id" + i).val()+',';
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
