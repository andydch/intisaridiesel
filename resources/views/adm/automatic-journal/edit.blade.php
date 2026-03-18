@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
<style>
    th, td {
        padding: 5px;
        vertical-align: middle;
    }
</style>
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include('adm.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$qAutomaticJournal->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    {{-- @if($errors->any())
                    Error:
                    {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                    @endif --}}
                    <div class="card">
                        <div class="card-body">
                            {{-- @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif --}}
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
                            <div class="row mb-3">
                                <label for="journalName" class="col-sm-3 col-form-label">Journal Name</label>
                                <div class="col-sm-9">{{ $qAutomaticJournal->journal_name }}</div>
                                <input type="hidden" name="aut_jn_id" value="{{ $qAutomaticJournal->id }}">
                            </div>
                            @php
                                $methodId = old('methodId')?old('methodId'):(isset($method_id)?$method_id:0);
                            @endphp
                            @if ($qAutomaticJournal->id==8 || $qAutomaticJournal->id==13)
                                <div class="row mb-3">
                                    <label for="methodId" class="col-sm-3 col-form-label">Method</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('methodId') is-invalid @enderror" id="methodId" name="methodId">
                                            <option value="#">Choose...</option>
                                            @php
                                                $method_id = explode("|",env('METHOD_BAYAR_SUPPLIER_ID'));
                                                $method_name = explode("|",env('METHOD_BAYAR_SUPPLIER_NAME'));
                                            @endphp
                                            @for ($mI=0;$mI<count($method_name);$mI++)
                                                <option @if($methodId==$method_id[$mI]){{ 'selected' }}@endif value="{{ $method_id[$mI] }}">{{ $method_name[$mI] }}</option>
                                            @endfor
                                        </select>
                                        @error('methodId')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            @if ($qAutomaticJournal->id==7 || $qAutomaticJournal->id==14)
                                <div class="row mb-3">
                                    <label for="methodId" class="col-sm-3 col-form-label">Method</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('methodId') is-invalid @enderror" id="methodId" name="methodId">
                                            <option value="#">Choose...</option>
                                            @php
                                                $method_id = explode("|",env('METHOD_TERIMA_CUST_ID'));
                                                $method_name = explode("|",env('METHOD_TERIMA_CUST_NAME'));
                                            @endphp
                                            @for ($mI=0;$mI<count($method_name);$mI++)
                                                <option @if($methodId==$method_id[$mI]){{ 'selected' }}@endif value="{{ $method_id[$mI] }}">{{ $method_name[$mI] }}</option>
                                            @endfor
                                        </select>
                                        @error('methodId')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            <div class="row mb-3">
                                <label for="branchId" class="col-sm-3 col-form-label">Branch @if($qAutomaticJournal->id==12){{ 'Transfer Out' }}@endif</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('branchId') is-invalid @enderror" id="branchId" name="branchId">
                                        <option value="0">Choose...</option>
                                        @php
                                            $branchId = old('branchId')?old('branchId'):(isset($branch_id)?$branch_id:0);
                                        @endphp
                                        @foreach ($qBranch as $qB)
                                            <option @if($branchId==$qB->id) {{ 'selected' }} @endif value="{{ $qB->id }}">{{ $qB->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branchId')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @if ($qAutomaticJournal->id==12)
                                <div class="row mb-3">
                                    <label for="branch_in_id" class="col-sm-3 col-form-label">Branch Transfer In</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('branch_in_id') is-invalid @enderror" id="branch_in_id" name="branch_in_id">
                                            <option value="0">Choose...</option>
                                            @php
                                                $branch_in_id_r = old('branch_in_id')?old('branch_in_id'):$branch_in_id;
                                            @endphp
                                            @foreach ($qBranch as $qB)
                                                <option @if($branch_in_id_r==$qB->id) {{ 'selected' }} @endif value="{{ $qB->id }}">{{ $qB->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('branch_in_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            <div class="row mb-3">
                                <table class="col-sm-12 table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>COA Code</th>
                                            <th>Desc</th>
                                            <th>Debet/Credit</th>
                                            @if ($methodId==2)
                                                <th>Del</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody id="new-coa-row">
                                        @switch($qAutomaticJournal->id)
                                            @case(1)
                                                {{-- faktur --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="5">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="COGS">
                                                        @php
                                                            $qFakturCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'P',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'cogs\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qFakturCoas as $qFkC)
                                                                <option @if($coaId==$qFkC->id){{ 'selected' }}@endif value="{{ $qFkC->id }}">
                                                                    {{ $qFkC->coa_code_complete.' - '.$qFkC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>COGS</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_2" value="2">
                                                        <input type="hidden" name="debet_or_credit_2" value="Credit">
                                                        <input type="hidden" name="desc_2" value="Inventory">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                            $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qFakturCoas as $qFkC)
                                                                <option @if($coaId==$qFkC->id){{ 'selected' }}@endif value="{{ $qFkC->id }}">
                                                                    {{ $qFkC->coa_code_complete.' - '.$qFkC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_3" value="3">
                                                        <input type="hidden" name="debet_or_credit_3" value="Debet">
                                                        <input type="hidden" name="desc_3" value="Piutang">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'piutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_3')?old('coa_id_3'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_3" value="@if(old('coa_dtl_id_3')){{ old('coa_dtl_id_3') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_3') is-invalid @enderror" id="coa_id_3" name="coa_id_3" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qFakturCoas as $qFkC)
                                                                <option @if($coaId==$qFkC->id){{ 'selected' }}@endif value="{{ $qFkC->id }}">
                                                                    {{ $qFkC->coa_code_complete.' - '.$qFkC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_3')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Piutang</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_4" value="4">
                                                        <input type="hidden" name="debet_or_credit_4" value="Credit">
                                                        <input type="hidden" name="desc_4" value="Sales Pajak">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'sales pajak\'')
                                                            ->first();
                                                            $coaId = old('coa_id_4')?old('coa_id_4'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_4" value="@if(old('coa_dtl_id_4')){{ old('coa_dtl_id_4') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_4') is-invalid @enderror" id="coa_id_4" name="coa_id_4" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qFakturCoas as $qFkC)
                                                                <option @if($coaId==$qFkC->id){{ 'selected' }}@endif value="{{ $qFkC->id }}">
                                                                    {{ $qFkC->coa_code_complete.' - '.$qFkC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_4')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Sales Pajak</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_5" value="5">
                                                        <input type="hidden" name="debet_or_credit_5" value="Credit">
                                                        <input type="hidden" name="desc_5" value="PPN Keluaran">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'ppn keluaran\'')
                                                            ->first();
                                                            $coaId = old('coa_id_5')?old('coa_id_5'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_5" value="@if(old('coa_dtl_id_5')){{ old('coa_dtl_id_5') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_5') is-invalid @enderror" id="coa_id_5" name="coa_id_5" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qFakturCoas as $qFkC)
                                                                <option @if($coaId==$qFkC->id){{ 'selected' }}@endif value="{{ $qFkC->id }}">
                                                                    {{ $qFkC->coa_code_complete.' - '.$qFkC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_5')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>PPN Keluaran</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(2)
                                                {{-- nota retur --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="5">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="Sales Retur Pajak">
                                                        @php
                                                            $qNotaReturCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'P',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'sales retur pajak\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qNotaReturCoas as $qNrC)
                                                                <option @if($coaId==$qNrC->id){{ 'selected' }}@endif value="{{ $qNrC->id }}">
                                                                    {{ $qNrC->coa_code_complete.' - '.$qNrC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Sales Retur Pajak</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_2" value="2">
                                                        <input type="hidden" name="debet_or_credit_2" value="Debet">
                                                        <input type="hidden" name="desc_2" value="PPN Keluaran">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'ppn keluaran\'')
                                                            ->first();
                                                            $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qNotaReturCoas as $qNrC)
                                                                <option @if($coaId==$qNrC->id){{ 'selected' }}@endif value="{{ $qNrC->id }}">
                                                                    {{ $qNrC->coa_code_complete.' - '.$qNrC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>PPN Keluaran</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_3" value="3">
                                                        <input type="hidden" name="debet_or_credit_3" value="Credit">
                                                        <input type="hidden" name="desc_3" value="Piutang">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'piutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_3')?old('coa_id_3'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_3" value="@if(old('coa_dtl_id_3')){{ old('coa_dtl_id_3') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_3') is-invalid @enderror" id="coa_id_3" name="coa_id_3" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qNotaReturCoas as $qNrC)
                                                                <option @if($coaId==$qNrC->id){{ 'selected' }}@endif value="{{ $qNrC->id }}">
                                                                    {{ $qNrC->coa_code_complete.' - '.$qNrC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_3')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Piutang</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_4" value="4">
                                                        <input type="hidden" name="debet_or_credit_4" value="Debet">
                                                        <input type="hidden" name="desc_4" value="Inventory">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                            $coaId = old('coa_id_4')?old('coa_id_4'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_4" value="@if(old('coa_dtl_id_4')){{ old('coa_dtl_id_4') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_4') is-invalid @enderror" id="coa_id_4" name="coa_id_4" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qNotaReturCoas as $qNrC)
                                                                <option @if($coaId==$qNrC->id){{ 'selected' }}@endif value="{{ $qNrC->id }}">
                                                                    {{ $qNrC->coa_code_complete.' - '.$qNrC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_4')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_5" value="5">
                                                        <input type="hidden" name="debet_or_credit_5" value="Credit">
                                                        <input type="hidden" name="desc_5" value="COGS">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'cogs\'')
                                                            ->first();
                                                            $coaId = old('coa_id_5')?old('coa_id_5'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_5" value="@if(old('coa_dtl_id_5')){{ old('coa_dtl_id_5') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_5') is-invalid @enderror" id="coa_id_5" name="coa_id_5" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qNotaReturCoas as $qNrC)
                                                                <option @if($coaId==$qNrC->id){{ 'selected' }}@endif value="{{ $qNrC->id }}">
                                                                    {{ $qNrC->coa_code_complete.' - '.$qNrC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_5')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>COGS</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(3)
                                                {{-- nota penjualan --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="4">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="COGS">
                                                        @php
                                                            $qNotaPenjualanCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'N',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'cogs\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qNotaPenjualanCoas as $qNpC)
                                                                <option @if($coaId==$qNpC->id){{ 'selected' }}@endif value="{{ $qNpC->id }}">
                                                                    {{ $qNpC->coa_code_complete.' - '.$qNpC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>COGS</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_2" value="2">
                                                        <input type="hidden" name="debet_or_credit_2" value="Credit">
                                                        <input type="hidden" name="desc_2" value="Inventory">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                            $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qNotaPenjualanCoas as $qNpC)
                                                                <option @if($coaId==$qNpC->id){{ 'selected' }}@endif value="{{ $qNpC->id }}">
                                                                    {{ $qNpC->coa_code_complete.' - '.$qNpC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_3" value="3">
                                                        <input type="hidden" name="debet_or_credit_3" value="Debet">
                                                        <input type="hidden" name="desc_3" value="Piutang">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'piutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_3')?old('coa_id_3'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_3" value="@if(old('coa_dtl_id_3')){{ old('coa_dtl_id_3') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_3') is-invalid @enderror" id="coa_id_3" name="coa_id_3" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qNotaPenjualanCoas as $qNpC)
                                                                <option @if($coaId==$qNpC->id){{ 'selected' }}@endif value="{{ $qNpC->id }}">
                                                                    {{ $qNpC->coa_code_complete.' - '.$qNpC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_3')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Piutang</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_4" value="4">
                                                        <input type="hidden" name="debet_or_credit_4" value="Credit">
                                                        <input type="hidden" name="desc_4" value="Sales Non Pajak">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'sales non pajak\'')
                                                            ->first();
                                                            $coaId = old('coa_id_4')?old('coa_id_4'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_4" value="@if(old('coa_dtl_id_4')){{ old('coa_dtl_id_4') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_4') is-invalid @enderror" id="coa_id_4" name="coa_id_4" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qNotaPenjualanCoas as $qNpC)
                                                                <option @if($coaId==$qNpC->id){{ 'selected' }}@endif value="{{ $qNpC->id }}">
                                                                    {{ $qNpC->coa_code_complete.' - '.$qNpC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_4')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Sales Non Pajak</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(4)
                                                {{-- retur non ppn --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="4">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="Sales Retur Non Pajak">
                                                        @php
                                                            $qReturCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'N',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'sales retur non pajak\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qReturCoas as $qReC)
                                                                <option @if($coaId==$qReC->id){{ 'selected' }}@endif value="{{ $qReC->id }}">
                                                                    {{ $qReC->coa_code_complete.' - '.$qReC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Sales Retur Non Pajak</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_2" value="2">
                                                        <input type="hidden" name="debet_or_credit_2" value="Credit">
                                                        <input type="hidden" name="desc_2" value="Piutang">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'piutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qReturCoas as $qReC)
                                                                <option @if($coaId==$qReC->id){{ 'selected' }}@endif value="{{ $qReC->id }}">
                                                                    {{ $qReC->coa_code_complete.' - '.$qReC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Piutang</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_3" value="3">
                                                        <input type="hidden" name="debet_or_credit_3" value="Debet">
                                                        <input type="hidden" name="desc_3" value="Inventory">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                            $coaId = old('coa_id_3')?old('coa_id_3'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_3" value="@if(old('coa_dtl_id_3')){{ old('coa_dtl_id_3') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_3') is-invalid @enderror" id="coa_id_3" name="coa_id_3" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qReturCoas as $qReC)
                                                                <option @if($coaId==$qReC->id){{ 'selected' }}@endif value="{{ $qReC->id }}">
                                                                    {{ $qReC->coa_code_complete.' - '.$qReC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_3')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_4" value="4">
                                                        <input type="hidden" name="debet_or_credit_4" value="Credit">
                                                        <input type="hidden" name="desc_4" value="COGS">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'cogs\'')
                                                            ->first();
                                                            $coaId = old('coa_id_4')?old('coa_id_4'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_4" value="@if(old('coa_dtl_id_4')){{ old('coa_dtl_id_4') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_4') is-invalid @enderror" id="coa_id_4" name="coa_id_4" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qReturCoas as $qReC)
                                                                <option @if($coaId==$qReC->id){{ 'selected' }}@endif value="{{ $qReC->id }}">
                                                                    {{ $qReC->coa_code_complete.' - '.$qReC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_4')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>COGS</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(5)
                                                {{-- receipt order ppn --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="3">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="Inventory">
                                                        @php
                                                            $qReceiptOrderCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'P',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qReceiptOrderCoas as $qRoC)
                                                                <option @if($coaId==$qRoC->id){{ 'selected' }}@endif value="{{ $qRoC->id }}">
                                                                    {{ $qRoC->coa_code_complete.' - '.$qRoC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Debet</td>
                                                </tr>
                                                {{-- <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_2" value="2">
                                                        <input type="hidden" name="debet_or_credit_2" value="Debet">
                                                        <input type="hidden" name="desc_2" value="Bea Masuk Import">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'bea masuk import\'')
                                                            ->first();
                                                            $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qReceiptOrderCoas as $qRoC)
                                                                <option @if($coaId==$qRoC->id){{ 'selected' }}@endif value="{{ $qRoC->id }}">
                                                                    {{ $qRoC->coa_code_complete.' - '.$qRoC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Bea Masuk Import</td>
                                                    <td>Debet</td>
                                                </tr> --}}
                                                {{-- <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_3" value="3">
                                                        <input type="hidden" name="debet_or_credit_3" value="Debet">
                                                        <input type="hidden" name="desc_3" value="PPN Masukan">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'ppn masukan\'')
                                                            ->first();
                                                            $coaId = old('coa_id_3')?old('coa_id_3'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_3" value="@if(old('coa_dtl_id_3')){{ old('coa_dtl_id_3') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_3') is-invalid @enderror" id="coa_id_3" name="coa_id_3" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qReceiptOrderCoas as $qRoC)
                                                                <option @if($coaId==$qRoC->id){{ 'selected' }}@endif value="{{ $qRoC->id }}">
                                                                    {{ $qRoC->coa_code_complete.' - '.$qRoC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_3')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>PPN Masukan</td>
                                                    <td>Debet</td>
                                                </tr> --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_2" value="2">
                                                        <input type="hidden" name="debet_or_credit_2" value="Debet">
                                                        <input type="hidden" name="desc_2" value="PPN Masukan">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'ppn masukan\'')
                                                            ->first();
                                                            $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qReceiptOrderCoas as $qRoC)
                                                                <option @if($coaId==$qRoC->id){{ 'selected' }}@endif value="{{ $qRoC->id }}">
                                                                    {{ $qRoC->coa_code_complete.' - '.$qRoC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>PPN Masukan</td>
                                                    <td>Debet</td>
                                                </tr>
                                                {{-- <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_4" value="4">
                                                        <input type="hidden" name="debet_or_credit_4" value="Credit">
                                                        <input type="hidden" name="desc_4" value="Hutang">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_4')?old('coa_id_4'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_4" value="@if(old('coa_dtl_id_4')){{ old('coa_dtl_id_4') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_4') is-invalid @enderror" id="coa_id_4" name="coa_id_4" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qReceiptOrderCoas as $qRoC)
                                                                <option @if($coaId==$qRoC->id){{ 'selected' }}@endif value="{{ $qRoC->id }}">
                                                                    {{ $qRoC->coa_code_complete.' - '.$qRoC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_4')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Credit</td>
                                                </tr> --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_3" value="3">
                                                        <input type="hidden" name="debet_or_credit_3" value="Credit">
                                                        <input type="hidden" name="desc_3" value="Hutang">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_3')?old('coa_id_3'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_3" value="@if(old('coa_dtl_id_3')){{ old('coa_dtl_id_3') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_3') is-invalid @enderror" id="coa_id_3" name="coa_id_3" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qReceiptOrderCoas as $qRoC)
                                                                <option @if($coaId==$qRoC->id){{ 'selected' }}@endif value="{{ $qRoC->id }}">
                                                                    {{ $qRoC->coa_code_complete.' - '.$qRoC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_3')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(6)
                                                {{-- receipt order non ppn --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="2">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="Inventory">
                                                        @php
                                                            $qReceiptOrderNonPPNCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'N',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qReceiptOrderNonPPNCoas as $qRoNonPpnC)
                                                                <option @if($coaId==$qRoNonPpnC->id){{ 'selected' }}@endif value="{{ $qRoNonPpnC->id }}">
                                                                    {{ $qRoNonPpnC->coa_code_complete.' - '.$qRoNonPpnC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_2" value="2">
                                                        <input type="hidden" name="debet_or_credit_2" value="Credit">
                                                        <input type="hidden" name="desc_2" value="Hutang">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qReceiptOrderNonPPNCoas as $qRoNonPpnC)
                                                                <option @if($coaId==$qRoNonPpnC->id){{ 'selected' }}@endif value="{{ $qRoNonPpnC->id }}">
                                                                    {{ $qRoNonPpnC->coa_code_complete.' - '.$qRoNonPpnC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(7)
                                                {{-- penerimaan customer ppn --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="6">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        {{-- <input type="hidden" name="desc_1" value="@if($methodId==2){{ 'Bank' }}@else{{ 'Cash' }}@endif"> --}}
                                                        <input type="hidden" name="desc_1" value="@switch($methodId)
                                                            @case(2)
                                                                {{ 'bank' }}
                                                                @php
                                                                    $methodNm = 'bank';
                                                                @endphp
                                                                @break
                                                            @case(3)
                                                                {{ 'customer deposit' }}
                                                                @php
                                                                    $methodNm = 'customer deposit';
                                                                @endphp
                                                                @break
                                                            @default
                                                                {{ 'cash' }}
                                                                @php
                                                                    $methodNm = 'cash';
                                                                @endphp
                                                        @endswitch">
                                                        @php
                                                            $qPenerimaanCustomerCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'P',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                                                            // ->whereRaw('LOWER(`desc`)=\''.($methodId==2?'bank':'cash').'\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPenerimaanCustomerCoas as $qPenCustC)
                                                                <option @if($coaId==$qPenCustC->id){{ 'selected' }}@endif value="{{ $qPenCustC->id }}">
                                                                    {{ $qPenCustC->coa_code_complete.' - '.$qPenCustC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        @switch($methodId)
                                                            @case(2)
                                                                {{ 'Bank' }}
                                                                @break
                                                            @case(3)
                                                                {{ 'Customer Deposit' }}
                                                                @break
                                                            @default
                                                                {{ 'Cash' }}
                                                        @endswitch
                                                    </td>
                                                    <td>Debet</td>
                                                </tr>

                                                <input type="hidden" name="order_no_2" value="2">
                                                <input type="hidden" name="debet_or_credit_2" value="Debet">
                                                <input type="hidden" name="desc_2" value="discount">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'discount\'')
                                                    ->first();
                                                    $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                <tr>
                                                    <td>
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPenerimaanCustomerCoas as $qPenCustC)
                                                                <option @if($coaId==$qPenCustC->id){{ 'selected' }}@endif value="{{ $qPenCustC->id }}">
                                                                    {{ $qPenCustC->coa_code_complete.' - '.$qPenCustC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Discount</td>
                                                    <td>Debet</td>
                                                </tr>

                                                <input type="hidden" name="order_no_3" value="3">
                                                <input type="hidden" name="debet_or_credit_3" value="Debet">
                                                <input type="hidden" name="desc_3" value="admin bank">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'admin bank\'')
                                                    ->first();
                                                    $coaId = old('coa_id_3')?old('coa_id_3'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_3" value="@if(old('coa_dtl_id_3')){{ old('coa_dtl_id_3') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                @if ($methodId==1)
                                                    <input type="hidden" name="coa_id_3" id="coa_id_3" value="0">
                                                @else
                                                    <tr>
                                                        <td>
                                                            <select class="form-select single-select @error('coa_id_3') is-invalid @enderror" id="coa_id_3" name="coa_id_3" style="text-align: left;">
                                                                <option value="#">Choose...</option>
                                                                @foreach ($qPenerimaanCustomerCoas as $qPenCustC)
                                                                    <option @if($coaId==$qPenCustC->id){{ 'selected' }}@endif value="{{ $qPenCustC->id }}">
                                                                        {{ $qPenCustC->coa_code_complete.' - '.$qPenCustC->coa_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('coa_id_3')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>Admin Bank</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                @endif

                                                <input type="hidden" name="order_no_4" value="4">
                                                <input type="hidden" name="debet_or_credit_4" value="Credit">
                                                <input type="hidden" name="desc_4" value="penerimaan lainnya">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'penerimaan lainnya\'')
                                                    ->first();
                                                    $coaId = old('coa_id_4')?old('coa_id_4'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_4" value="@if(old('coa_dtl_id_4')){{ old('coa_dtl_id_4') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                <tr>
                                                    <td>
                                                        <select class="form-select single-select @error('coa_id_4') is-invalid @enderror" id="coa_id_4" name="coa_id_4" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPenerimaanCustomerCoas as $qPenCustC)
                                                                <option @if($coaId==$qPenCustC->id){{ 'selected' }}@endif value="{{ $qPenCustC->id }}">
                                                                    {{ $qPenCustC->coa_code_complete.' - '.$qPenCustC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_4')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Penerimaan Lainnya</td>
                                                    <td>Credit</td>
                                                </tr>

                                                <input type="hidden" name="order_no_5" value="5">
                                                <input type="hidden" name="debet_or_credit_5" value="Credit">
                                                <input type="hidden" name="desc_5" value="biaya kirim">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                                                    ->first();
                                                    $coaId = old('coa_id_5')?old('coa_id_5'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_5" value="@if(old('coa_dtl_id_5')){{ old('coa_dtl_id_5') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                <tr>
                                                    <td>
                                                        <select class="form-select single-select @error('coa_id_5') is-invalid @enderror" id="coa_id_5" name="coa_id_5" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPenerimaanCustomerCoas as $qPenCustC)
                                                                <option @if($coaId==$qPenCustC->id){{ 'selected' }}@endif value="{{ $qPenCustC->id }}">
                                                                    {{ $qPenCustC->coa_code_complete.' - '.$qPenCustC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_5')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Biaya Kirim</td>
                                                    <td>Credit</td>
                                                </tr>

                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_6" value="6">
                                                        <input type="hidden" name="debet_or_credit_6" value="Credit">
                                                        <input type="hidden" name="desc_6" value="Piutang">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'piutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_6')?old('coa_id_6'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_6" value="@if(old('coa_dtl_id_6')){{ old('coa_dtl_id_6') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_6') is-invalid @enderror" id="coa_id_6" name="coa_id_6" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPenerimaanCustomerCoas as $qPenCustC)
                                                                <option @if($coaId==$qPenCustC->id){{ 'selected' }}@endif value="{{ $qPenCustC->id }}">
                                                                    {{ $qPenCustC->coa_code_complete.' - '.$qPenCustC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_6')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Piutang</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @if ($methodId==2)
                                                    @if (old('totalCoaRow'))
                                                        <input type="hidden" id="totalCoaRow" name="totalCoaRow" value="{{ old('totalCoaRow') }}">
                                                        @for ($i=1;$i<=old('totalCoaRow');$i++)
                                                            @if (old('coa_id_'.$i.'_add'))
                                                                <tr id="row_{{ $i }}_add">
                                                                    <td>
                                                                        <input type="hidden" name="order_no_{{ $i }}_add" value="{{ $i }}">
                                                                        <input type="hidden" name="debet_or_credit_{{ $i }}_add" value="Debet">
                                                                        <input type="hidden" name="desc_{{ $i }}_add" value="Bank">
                                                                        <input type="hidden" name="coa_dtl_id_{{ $i }}_add" value="{{ old('coa_dtl_id_'.$i.'_add') }}">
                                                                        <select class="form-select single-select @error('coa_id_'.$i.'_add') is-invalid @enderror"
                                                                            id="coa_id_{{ $i }}_add" name="coa_id_{{ $i }}_add" style="text-align: left;">
                                                                            <option value="#">Choose...</option>
                                                                            @foreach ($qPenerimaanCustomerCoas as $qPenCustC)
                                                                                <option @if(old('coa_id_'.$i.'_add')==$qPenCustC->id){{ 'selected' }}@endif value="{{ $qPenCustC->id }}">
                                                                                    {{ $qPenCustC->coa_code_complete.' - '.$qPenCustC->coa_name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('coa_id_'.$i.'_add')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </td>
                                                                    <td>Bank</td>
                                                                    <td>Debet</td>
                                                                    <td style="text-align:center;"><input type="checkbox" id="rowCheck_{{ $i }}_add" value="{{ $i }}"></td>
                                                                </tr>
                                                            @endif
                                                        @endfor
                                                    @else
                                                        @php
                                                            $i = 1;
                                                            $qAjExt = \App\Models\Mst_automatic_journal_detail_ext::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ]);
                                                        @endphp
                                                        <input type="hidden" id="totalCoaRow" name="totalCoaRow" value="{{ $qAjExt->count() }}">
                                                        @foreach ($qAjExt->get() as $a)
                                                            <tr id="row_{{ $i }}_add">
                                                                <td>
                                                                    <input type="hidden" name="order_no_{{ $i }}_add" value="{{ $i }}">
                                                                    <input type="hidden" name="debet_or_credit_{{ $i }}_add" value="Debet">
                                                                    <input type="hidden" name="desc_{{ $i }}_add" value="Bank">
                                                                    <input type="hidden" name="coa_dtl_id_{{ $i }}_add" value="{{ $a->id }}">
                                                                    <select class="form-select single-select @error('coa_id_'.$i.'_add') is-invalid @enderror"
                                                                        id="coa_id_{{ $i }}_add" name="coa_id_{{ $i }}_add" style="text-align: left;">
                                                                        <option value="#">Choose...</option>
                                                                        @foreach ($qPenerimaanCustomerCoas as $qPenCustC)
                                                                            <option @if($a->coa_code_id==$qPenCustC->id){{ 'selected' }}@endif value="{{ $qPenCustC->id }}">
                                                                                {{ $qPenCustC->coa_code_complete.' - '.$qPenCustC->coa_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('coa_id_'.$i.'_add')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td>Bank</td>
                                                                <td>Debet</td>
                                                                <td style="text-align:center;"><input type="checkbox" id="rowCheck_{{ $i }}_add" value="{{ $i }}"></td>
                                                            </tr>
                                                            @php
                                                                $i += 1;
                                                            @endphp
                                                        @endforeach
                                                    @endif
                                                @endif

                                                @break

                                            @case(8)
                                                {{-- pembayaran supplier ppn --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="7">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="Hutang">
                                                        @php
                                                            $qPembayaranSupplierPPNCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'P',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPembayaranSupplierPPNCoas as $qPenSupC)
                                                                <option @if($coaId==$qPenSupC->id){{ 'selected' }}@endif value="{{ $qPenSupC->id }}">
                                                                    {{ $qPenSupC->coa_code_complete.' - '.$qPenSupC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Debet</td>
                                                </tr>

                                                <input type="hidden" name="order_no_2" value="2">
                                                <input type="hidden" name="debet_or_credit_2" value="Debet">
                                                <input type="hidden" name="desc_2" value="Bank Admin">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'bank admin\'')
                                                    ->first();
                                                    $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                @if ($methodId==1)
                                                    <input type="hidden" name="coa_id_2" id="coa_id_2" value="0">
                                                @else
                                                    <tr>
                                                        <td>
                                                            <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                                <option value="#">Choose...</option>
                                                                @foreach ($qPembayaranSupplierPPNCoas as $qPenSupC)
                                                                    <option @if($coaId==$qPenSupC->id){{ 'selected' }}@endif value="{{ $qPenSupC->id }}">
                                                                        {{ $qPenSupC->coa_code_complete.' - '.$qPenSupC->coa_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('coa_id_2')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>Bank Admin</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                @endif

                                                <input type="hidden" name="order_no_3" value="3">
                                                <input type="hidden" name="debet_or_credit_3" value="Debet">
                                                <input type="hidden" name="desc_3" value="Biaya Asuransi">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'biaya asuransi\'')
                                                    ->first();
                                                    $coaId = old('coa_id_3')?old('coa_id_3'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_3" value="@if(old('coa_dtl_id_3')){{ old('coa_dtl_id_3') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                {{-- @if ($methodId==1)
                                                    <input type="hidden" name="coa_id_3" id="coa_id_3" value="0">
                                                @else --}}
                                                    <tr>
                                                        <td>
                                                            <select class="form-select single-select @error('coa_id_3') is-invalid @enderror" id="coa_id_3" name="coa_id_3" style="text-align: left;">
                                                                <option value="#">Choose...</option>
                                                                @foreach ($qPembayaranSupplierPPNCoas as $qPenSupC)
                                                                    <option @if($coaId==$qPenSupC->id){{ 'selected' }}@endif value="{{ $qPenSupC->id }}">
                                                                        {{ $qPenSupC->coa_code_complete.' - '.$qPenSupC->coa_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('coa_id_3')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>Biaya Asuransi</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                {{-- @endif --}}

                                                <input type="hidden" name="order_no_4" value="4">
                                                <input type="hidden" name="debet_or_credit_4" value="Debet">
                                                <input type="hidden" name="desc_4" value="Biaya Kirim">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                                                    ->first();
                                                    $coaId = old('coa_id_4')?old('coa_id_4'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_4" value="@if(old('coa_dtl_id_4')){{ old('coa_dtl_id_4') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                {{-- @if ($methodId==1)
                                                    <input type="hidden" name="coa_id_4" id="coa_id_4" value="0">
                                                @else --}}
                                                    <tr>
                                                        <td>
                                                            <select class="form-select single-select @error('coa_id_4') is-invalid @enderror" id="coa_id_4" name="coa_id_4" style="text-align: left;">
                                                                <option value="#">Choose...</option>
                                                                @foreach ($qPembayaranSupplierPPNCoas as $qPenSupC)
                                                                    <option @if($coaId==$qPenSupC->id){{ 'selected' }}@endif value="{{ $qPenSupC->id }}">
                                                                        {{ $qPenSupC->coa_code_complete.' - '.$qPenSupC->coa_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('coa_id_4')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>Biaya Kirim</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                {{-- @endif --}}

                                                <input type="hidden" name="order_no_5" value="5">
                                                <input type="hidden" name="debet_or_credit_5" value="Debet">
                                                <input type="hidden" name="desc_5" value="Biaya Lainnya">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'biaya lainnya\'')
                                                    ->first();
                                                    $coaId = old('coa_id_5')?old('coa_id_5'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_5" value="@if(old('coa_dtl_id_5')){{ old('coa_dtl_id_5') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                {{-- @if ($methodId==1)
                                                    <input type="hidden" name="coa_id_5" id="coa_id_5" value="0">
                                                @else --}}
                                                    <tr>
                                                        <td>
                                                            <select class="form-select single-select @error('coa_id_5') is-invalid @enderror" id="coa_id_5" name="coa_id_5" style="text-align: left;">
                                                                <option value="#">Choose...</option>
                                                                @foreach ($qPembayaranSupplierPPNCoas as $qPenSupC)
                                                                    <option @if($coaId==$qPenSupC->id){{ 'selected' }}@endif value="{{ $qPenSupC->id }}">
                                                                        {{ $qPenSupC->coa_code_complete.' - '.$qPenSupC->coa_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('coa_id_5')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>Biaya Lainnya</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                {{-- @endif --}}

                                                <input type="hidden" name="order_no_6" value="6">
                                                <input type="hidden" name="debet_or_credit_6" value="Credit">
                                                <input type="hidden" name="desc_6" value="Discount">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'discount\'')
                                                    ->first();
                                                    $coaId = old('coa_id_6')?old('coa_id_6'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_6" value="@if(old('coa_dtl_id_6')){{ old('coa_dtl_id_6') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                {{-- @if ($methodId==1)
                                                    <input type="hidden" name="coa_id_6" id="coa_id_6" value="0">
                                                @else --}}
                                                    <tr>
                                                        <td>
                                                            <select class="form-select single-select @error('coa_id_6') is-invalid @enderror" id="coa_id_6" name="coa_id_6" style="text-align: left;">
                                                                <option value="#">Choose...</option>
                                                                @foreach ($qPembayaranSupplierPPNCoas as $qPenSupC)
                                                                    <option @if($coaId==$qPenSupC->id){{ 'selected' }}@endif value="{{ $qPenSupC->id }}">
                                                                        {{ $qPenSupC->coa_code_complete.' - '.$qPenSupC->coa_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('coa_id_6')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>Discount</td>
                                                        <td>Credit</td>
                                                    </tr>
                                                {{-- @endif --}}

                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_7" value="7">
                                                        <input type="hidden" name="debet_or_credit_7" value="Credit">
                                                        @php
                                                            $methodNm = '';
                                                        @endphp
                                                        <input type="hidden" name="desc_7" value="@switch($methodId)
                                                            @case(2)
                                                                {{ 'Bank' }}
                                                                @php
                                                                    $methodNm = 'bank';
                                                                @endphp
                                                                @break
                                                            @case(3)
                                                                {{ 'Advance payment' }}
                                                                @php
                                                                    $methodNm = 'advance payment';
                                                                @endphp
                                                                @break
                                                            @default
                                                                {{ 'Cash' }}
                                                                @php
                                                                    $methodNm = 'cash';
                                                                @endphp
                                                        @endswitch">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\''.$methodNm.'\'')
                                                            ->first();
                                                            $coaId = old('coa_id_7')?old('coa_id_7'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_7" value="@if(old('coa_dtl_id_7')){{ old('coa_dtl_id_7') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_7') is-invalid @enderror" id="coa_id_7" name="coa_id_7" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPembayaranSupplierPPNCoas as $qPenSupC)
                                                                <option @if($coaId==$qPenSupC->id){{ 'selected' }}@endif value="{{ $qPenSupC->id }}">
                                                                    {{ $qPenSupC->coa_code_complete.' - '.$qPenSupC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_7')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>@switch($methodId)
                                                            @case(2)
                                                                {{ 'Bank' }}
                                                                @php
                                                                    $methodNm = 'bank';
                                                                @endphp
                                                                @break
                                                            @case(3)
                                                                {{ 'Advance payment' }}
                                                                @php
                                                                    $methodNm = 'advance payment';
                                                                @endphp
                                                                @break
                                                            @default
                                                                {{ 'Cash' }}
                                                                @php
                                                                    $methodNm = 'cash';
                                                                @endphp
                                                        @endswitch</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @if ($methodId==2)
                                                    @if (old('totalCoaRow'))
                                                        <input type="hidden" id="totalCoaRow" name="totalCoaRow" value="{{ old('totalCoaRow') }}">
                                                        @for ($i=1;$i<=old('totalCoaRow');$i++)
                                                            @if (old('coa_id_'.$i.'_add'))
                                                                <tr id="row_{{ $i }}_add">
                                                                    <td>
                                                                        <input type="hidden" name="order_no_{{ $i }}_add" value="{{ $i }}">
                                                                        <input type="hidden" name="debet_or_credit_{{ $i }}_add" value="Credit">
                                                                        <input type="hidden" name="desc_{{ $i }}_add" value="Bank">
                                                                        <input type="hidden" name="coa_dtl_id_{{ $i }}_add" value="{{ old('coa_dtl_id_'.$i.'_add') }}">
                                                                        <select class="form-select single-select @error('coa_id_'.$i.'_add') is-invalid @enderror"
                                                                            id="coa_id_{{ $i }}_add" name="coa_id_{{ $i }}_add" style="text-align: left;">
                                                                            <option value="#">Choose...</option>
                                                                            @foreach ($qPembayaranSupplierPPNCoas as $qPenSupC)
                                                                                <option @if(old('coa_id_'.$i.'_add')==$qPenSupC->id){{ 'selected' }}@endif value="{{ $qPenSupC->id }}">
                                                                                    {{ $qPenSupC->coa_code_complete.' - '.$qPenSupC->coa_name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('coa_id_'.$i.'_add')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </td>
                                                                    <td>Bank</td>
                                                                    <td>Credit</td>
                                                                    <td style="text-align:center;"><input type="checkbox" id="rowCheck_{{ $i }}_add" value="{{ $i }}"></td>
                                                                </tr>
                                                            @endif
                                                        @endfor
                                                    @else
                                                        @php
                                                            $i = 1;
                                                            $qAjExt = \App\Models\Mst_automatic_journal_detail_ext::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ]);
                                                        @endphp
                                                        <input type="hidden" id="totalCoaRow" name="totalCoaRow" value="{{ $qAjExt->count() }}">
                                                        @foreach ($qAjExt->get() as $a)
                                                            <tr id="row_{{ $i }}_add">
                                                                <td>
                                                                    <input type="hidden" name="order_no_{{ $i }}_add" value="{{ $i }}">
                                                                    <input type="hidden" name="debet_or_credit_{{ $i }}_add" value="Credit">
                                                                    <input type="hidden" name="desc_{{ $i }}_add" value="Bank">
                                                                    <input type="hidden" name="coa_dtl_id_{{ $i }}_add" value="{{ $a->id }}">
                                                                    <select class="form-select single-select @error('coa_id_'.$i.'_add') is-invalid @enderror"
                                                                        id="coa_id_{{ $i }}_add" name="coa_id_{{ $i }}_add" style="text-align: left;">
                                                                        <option value="#">Choose...</option>
                                                                        @foreach ($qPembayaranSupplierPPNCoas as $qPenSupC)
                                                                            <option @if($a->coa_code_id==$qPenSupC->id){{ 'selected' }}@endif value="{{ $qPenSupC->id }}">
                                                                                {{ $qPenSupC->coa_code_complete.' - '.$qPenSupC->coa_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('coa_id_'.$i.'_add')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td>Bank</td>
                                                                <td>Credit</td>
                                                                <td style="text-align:center;"><input type="checkbox" id="rowCheck_{{ $i }}_add" value="{{ $i }}"></td>
                                                            </tr>
                                                            @php
                                                                $i += 1;
                                                            @endphp
                                                        @endforeach
                                                    @endif
                                                @endif

                                                @if ($methodId==3)
                                                    @if (old('totalCoaRow'))
                                                        <input type="hidden" id="totalCoaRow" name="totalCoaRow" value="{{ old('totalCoaRow') }}">
                                                        @for ($i=1;$i<=old('totalCoaRow');$i++)
                                                            @if (old('coa_id_'.$i.'_add'))
                                                                <tr id="row_{{ $i }}_add">
                                                                    <td>
                                                                        <input type="hidden" name="order_no_{{ $i }}_add" value="{{ $i }}">
                                                                        <input type="hidden" name="debet_or_credit_{{ $i }}_add" value="Credit">
                                                                        <input type="hidden" name="desc_{{ $i }}_add" value="Advance Payment">
                                                                        <input type="hidden" name="coa_dtl_id_{{ $i }}_add" value="{{ old('coa_dtl_id_'.$i.'_add') }}">
                                                                        <select class="form-select single-select @error('coa_id_'.$i.'_add') is-invalid @enderror"
                                                                            id="coa_id_{{ $i }}_add" name="coa_id_{{ $i }}_add" style="text-align: left;">
                                                                            <option value="#">Choose...</option>
                                                                            @foreach ($qPembayaranSupplierPPNCoas as $qPenSupC)
                                                                                <option @if(old('coa_id_'.$i.'_add')==$qPenSupC->id){{ 'selected' }}@endif value="{{ $qPenSupC->id }}">
                                                                                    {{ $qPenSupC->coa_code_complete.' - '.$qPenSupC->coa_name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('coa_id_'.$i.'_add')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </td>
                                                                    <td>Advance Payment</td>
                                                                    <td>Credit</td>
                                                                    <td style="text-align:center;"><input type="checkbox" id="rowCheck_{{ $i }}_add" value="{{ $i }}"></td>
                                                                </tr>
                                                            @endif
                                                        @endfor
                                                    @else
                                                        @php
                                                            $i = 1;
                                                            $qAjExt = \App\Models\Mst_automatic_journal_detail_ext::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ]);
                                                        @endphp
                                                        <input type="hidden" id="totalCoaRow" name="totalCoaRow" value="{{ $qAjExt->count() }}">
                                                        @foreach ($qAjExt->get() as $a)
                                                            <tr id="row_{{ $i }}_add">
                                                                <td>
                                                                    <input type="hidden" name="order_no_{{ $i }}_add" value="{{ $i }}">
                                                                    <input type="hidden" name="debet_or_credit_{{ $i }}_add" value="Credit">
                                                                    <input type="hidden" name="desc_{{ $i }}_add" value="Advance Payment">
                                                                    <input type="hidden" name="coa_dtl_id_{{ $i }}_add" value="{{ $a->id }}">
                                                                    <select class="form-select single-select @error('coa_id_'.$i.'_add') is-invalid @enderror"
                                                                        id="coa_id_{{ $i }}_add" name="coa_id_{{ $i }}_add" style="text-align: left;">
                                                                        <option value="#">Choose...</option>
                                                                        @foreach ($qPembayaranSupplierPPNCoas as $qPenSupC)
                                                                            <option @if($a->coa_code_id==$qPenSupC->id){{ 'selected' }}@endif value="{{ $qPenSupC->id }}">
                                                                                {{ $qPenSupC->coa_code_complete.' - '.$qPenSupC->coa_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('coa_id_'.$i.'_add')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td>Advance Payment</td>
                                                                <td>Credit</td>
                                                                <td style="text-align:center;"><input type="checkbox" id="rowCheck_{{ $i }}_add" value="{{ $i }}"></td>
                                                            </tr>
                                                            @php
                                                                $i += 1;
                                                            @endphp
                                                        @endforeach
                                                    @endif
                                                @endif

                                                @break

                                            @case(9)

                                                @break

                                            @case(10)

                                                @break

                                            @case(11)
                                                {{-- stock adjustment --}}
                                                <tr>
                                                    <td colspan="3" style="font-weight: 700;">
                                                        Stock Adjustment Plus
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="4">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="Inventory-Plus">
                                                        @php
                                                            $qStockAdjustmentCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'P',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory-plus\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qStockAdjustmentCoas as $qStockAdjC)
                                                                <option @if($coaId==$qStockAdjC->id){{ 'selected' }}@endif value="{{ $qStockAdjC->id }}">
                                                                    {{ $qStockAdjC->coa_code_complete.' - '.$qStockAdjC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_2" value="2">
                                                        <input type="hidden" name="debet_or_credit_2" value="Credit">
                                                        <input type="hidden" name="desc_2" value="COGS-Plus">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'cogs-plus\'')
                                                            ->first();
                                                            $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qStockAdjustmentCoas as $qStockAdjC)
                                                                <option @if($coaId==$qStockAdjC->id){{ 'selected' }}@endif value="{{ $qStockAdjC->id }}">
                                                                    {{ $qStockAdjC->coa_code_complete.' - '.$qStockAdjC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>COGS</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" style="font-weight: 700;">
                                                        Stock Adjustment Minus
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_3" value="3">
                                                        <input type="hidden" name="debet_or_credit_3" value="Debet">
                                                        <input type="hidden" name="desc_3" value="COGS-Minus">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'cogs-minus\'')
                                                            ->first();
                                                            $coaId = old('coa_id_3')?old('coa_id_3'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_3" value="@if(old('coa_dtl_id_3')){{ old('coa_dtl_id_3') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_3') is-invalid @enderror" id="coa_id_3" name="coa_id_3" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qStockAdjustmentCoas as $qStockAdjC)
                                                                <option @if($coaId==$qStockAdjC->id){{ 'selected' }}@endif value="{{ $qStockAdjC->id }}">
                                                                    {{ $qStockAdjC->coa_code_complete.' - '.$qStockAdjC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_3')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>COGS</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_4" value="4">
                                                        <input type="hidden" name="debet_or_credit_4" value="Credit">
                                                        <input type="hidden" name="desc_4" value="Inventory-Minus">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory-minus\'')
                                                            ->first();
                                                            $coaId = old('coa_id_4')?old('coa_id_4'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_4" value="@if(old('coa_dtl_id_4')){{ old('coa_dtl_id_4') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_4') is-invalid @enderror" id="coa_id_4" name="coa_id_4" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qStockAdjustmentCoas as $qStockAdjC)
                                                                <option @if($coaId==$qStockAdjC->id){{ 'selected' }}@endif value="{{ $qStockAdjC->id }}">
                                                                    {{ $qStockAdjC->coa_code_complete.' - '.$qStockAdjC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_4')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(12)
                                                {{-- stock transfer --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="2">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="Transfer In">
                                                        @php
                                                            $qStockTransferCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where([
                                                                'local'=>'A',
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'branch_in_id'=>$branch_in_id_r,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'transfer in\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qStockTransferCoas as $qStockTrfC)
                                                                <option @if($coaId==$qStockTrfC->id){{ 'selected' }}@endif value="{{ $qStockTrfC->id }}">
                                                                    {{ $qStockTrfC->coa_code_complete.' - '.$qStockTrfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Transfer In</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_2" value="2">
                                                        <input type="hidden" name="debet_or_credit_2" value="Credit">
                                                        <input type="hidden" name="desc_2" value="Transfer Out">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'branch_in_id'=>$branch_in_id_r,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'transfer out\'')
                                                            ->first();
                                                            $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qStockTransferCoas as $qStockTrfC)
                                                                <option @if($coaId==$qStockTrfC->id){{ 'selected' }}@endif value="{{ $qStockTrfC->id }}">
                                                                    {{ $qStockTrfC->coa_code_complete.' - '.$qStockTrfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Transfer Out</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(13)
                                                {{-- pembayaran supplier non ppn --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="7">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="Hutang">
                                                        @php
                                                            $qPembayaranSupplierNonPPNCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'N',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPembayaranSupplierNonPPNCoas as $qPembSuppNonPPNfC)
                                                                <option @if($coaId==$qPembSuppNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembSuppNonPPNfC->id }}">
                                                                    {{ $qPembSuppNonPPNfC->coa_code_complete.' - '.$qPembSuppNonPPNfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Debet</td>
                                                </tr>

                                                <input type="hidden" name="order_no_2" value="2">
                                                <input type="hidden" name="debet_or_credit_2" value="Debet">
                                                <input type="hidden" name="desc_2" value="Bank Admin">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'bank admin\'')
                                                    ->first();
                                                    $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                @if ($methodId==1)
                                                    <input type="hidden" name="coa_id_2" id="coa_id_2" value="0">
                                                @else
                                                    <tr>
                                                        <td>
                                                            <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                                <option value="#">Choose...</option>
                                                                @foreach ($qPembayaranSupplierNonPPNCoas as $qPembSuppNonPPNfC)
                                                                    <option @if($coaId==$qPembSuppNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembSuppNonPPNfC->id }}">
                                                                        {{ $qPembSuppNonPPNfC->coa_code_complete.' - '.$qPembSuppNonPPNfC->coa_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('coa_id_2')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>Bank Admin</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                @endif

                                                <input type="hidden" name="order_no_3" value="3">
                                                <input type="hidden" name="debet_or_credit_3" value="Debet">
                                                <input type="hidden" name="desc_3" value="Biaya Asuransi">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'biaya asuransi\'')
                                                    ->first();
                                                    $coaId = old('coa_id_3')?old('coa_id_3'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_3" value="@if(old('coa_dtl_id_3')){{ old('coa_dtl_id_3') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                {{-- @if ($methodId==1)
                                                    <input type="hidden" name="coa_id_3" id="coa_id_3" value="0">
                                                @else --}}
                                                    <tr>
                                                        <td>
                                                            <select class="form-select single-select @error('coa_id_3') is-invalid @enderror" id="coa_id_3" name="coa_id_3" style="text-align: left;">
                                                                <option value="#">Choose...</option>
                                                                @foreach ($qPembayaranSupplierNonPPNCoas as $qPembSuppNonPPNfC)
                                                                    <option @if($coaId==$qPembSuppNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembSuppNonPPNfC->id }}">
                                                                        {{ $qPembSuppNonPPNfC->coa_code_complete.' - '.$qPembSuppNonPPNfC->coa_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('coa_id_3')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>Biaya Asuransi</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                {{-- @endif --}}

                                                <input type="hidden" name="order_no_4" value="4">
                                                <input type="hidden" name="debet_or_credit_4" value="Debet">
                                                <input type="hidden" name="desc_4" value="Biaya Kirim">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                                                    ->first();
                                                    $coaId = old('coa_id_4')?old('coa_id_4'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_4" value="@if(old('coa_dtl_id_4')){{ old('coa_dtl_id_4') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                {{-- @if ($methodId==1)
                                                    <input type="hidden" name="coa_id_4" id="coa_id_4" value="0">
                                                @else --}}
                                                    <tr>
                                                        <td>
                                                            <select class="form-select single-select @error('coa_id_4') is-invalid @enderror" id="coa_id_4" name="coa_id_4" style="text-align: left;">
                                                                <option value="#">Choose...</option>
                                                                @foreach ($qPembayaranSupplierNonPPNCoas as $qPembSuppNonPPNfC)
                                                                    <option @if($coaId==$qPembSuppNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembSuppNonPPNfC->id }}">
                                                                        {{ $qPembSuppNonPPNfC->coa_code_complete.' - '.$qPembSuppNonPPNfC->coa_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('coa_id_4')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>Biaya Kirim</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                {{-- @endif --}}

                                                <input type="hidden" name="order_no_5" value="5">
                                                <input type="hidden" name="debet_or_credit_5" value="Debet">
                                                <input type="hidden" name="desc_5" value="Biaya Lainnya">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'biaya lainnya\'')
                                                    ->first();
                                                    $coaId = old('coa_id_5')?old('coa_id_5'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_5" value="@if(old('coa_dtl_id_5')){{ old('coa_dtl_id_5') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                {{-- @if ($methodId==1)
                                                    <input type="hidden" name="coa_id_5" id="coa_id_5" value="0">
                                                @else --}}
                                                    <tr>
                                                        <td>
                                                            <select class="form-select single-select @error('coa_id_5') is-invalid @enderror" id="coa_id_5" name="coa_id_5" style="text-align: left;">
                                                                <option value="#">Choose...</option>
                                                                @foreach ($qPembayaranSupplierNonPPNCoas as $qPembSuppNonPPNfC)
                                                                    <option @if($coaId==$qPembSuppNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembSuppNonPPNfC->id }}">
                                                                        {{ $qPembSuppNonPPNfC->coa_code_complete.' - '.$qPembSuppNonPPNfC->coa_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('coa_id_5')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>Biaya Lainnya</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                {{-- @endif --}}

                                                <input type="hidden" name="order_no_6" value="6">
                                                <input type="hidden" name="debet_or_credit_6" value="Credit">
                                                <input type="hidden" name="desc_6" value="Discount">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'discount\'')
                                                    ->first();
                                                    $coaId = old('coa_id_6')?old('coa_id_6'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_6" value="@if(old('coa_dtl_id_6')){{ old('coa_dtl_id_6') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                {{-- @if ($methodId==1)
                                                    <input type="hidden" name="coa_id_6" id="coa_id_6" value="0">
                                                @else --}}
                                                    <tr>
                                                        <td>
                                                            <select class="form-select single-select @error('coa_id_6') is-invalid @enderror" id="coa_id_6" name="coa_id_6" style="text-align: left;">
                                                                <option value="#">Choose...</option>
                                                                @foreach ($qPembayaranSupplierNonPPNCoas as $qPembSuppNonPPNfC)
                                                                    <option @if($coaId==$qPembSuppNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembSuppNonPPNfC->id }}">
                                                                        {{ $qPembSuppNonPPNfC->coa_code_complete.' - '.$qPembSuppNonPPNfC->coa_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('coa_id_6')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>Discount</td>
                                                        <td>Credit</td>
                                                    </tr>
                                                {{-- @endif --}}

                                                @php
                                                    $methodNm = '';
                                                @endphp
                                                @switch($methodId)
                                                    @case(2)
                                                        @php
                                                            $methodNm = 'Bank';
                                                        @endphp
                                                        @break
                                                    @case(3)
                                                        @php
                                                            $methodNm = 'Advance Payment';
                                                        @endphp
                                                        @break
                                                    @default
                                                        @php
                                                            $methodNm = 'Cash';
                                                        @endphp
                                                @endswitch
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_7" value="7">
                                                        <input type="hidden" name="debet_or_credit_7" value="Credit">
                                                        <input type="hidden" name="desc_7" value="{{ $methodNm }}">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                                                            ->first();
                                                            $coaId = old('coa_id_7')?old('coa_id_7'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_7" value="@if(old('coa_dtl_id_7')){{ old('coa_dtl_id_7') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_7') is-invalid @enderror" id="coa_id_7" name="coa_id_7" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPembayaranSupplierNonPPNCoas as $qPembSuppNonPPNfC)
                                                                <option @if($coaId==$qPembSuppNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembSuppNonPPNfC->id }}">
                                                                    {{ $qPembSuppNonPPNfC->coa_code_complete.' - '.$qPembSuppNonPPNfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_7')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>{{ $methodNm }}</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @if ($methodId==2)
                                                    @if (old('totalCoaRow'))
                                                        <input type="hidden" id="totalCoaRow" name="totalCoaRow" value="{{ old('totalCoaRow') }}">
                                                        @for ($i=1;$i<=old('totalCoaRow');$i++)
                                                            @if (old('coa_id_'.$i.'_add'))
                                                                <tr id="row_{{ $i }}_add">
                                                                    <td>
                                                                        <input type="hidden" name="order_no_{{ $i }}_add" value="{{ $i }}">
                                                                        <input type="hidden" name="debet_or_credit_{{ $i }}_add" value="Credit">
                                                                        <input type="hidden" name="desc_{{ $i }}_add" value="Bank">
                                                                        <input type="hidden" name="coa_dtl_id_{{ $i }}_add" value="{{ old('coa_dtl_id_'.$i.'_add') }}">
                                                                        <select class="form-select single-select @error('coa_id_'.$i.'_add') is-invalid @enderror"
                                                                            id="coa_id_{{ $i }}_add" name="coa_id_{{ $i }}_add" style="text-align: left;">
                                                                            <option value="#">Choose...</option>
                                                                            @foreach ($qPembayaranSupplierNonPPNCoas as $qPembSuppNonPPNfC)
                                                                                <option @if(old('coa_id_'.$i.'_add')==$qPembSuppNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembSuppNonPPNfC->id }}">
                                                                                    {{ $qPembSuppNonPPNfC->coa_code_complete.' - '.$qPembSuppNonPPNfC->coa_name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('coa_id_'.$i.'_add')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </td>
                                                                    <td>Bank</td>
                                                                    <td>Credit</td>
                                                                    <td style="text-align:center;"><input type="checkbox" id="rowCheck_{{ $i }}_add" value="{{ $i }}"></td>
                                                                </tr>
                                                            @endif
                                                        @endfor
                                                    @else
                                                        @php
                                                            $i = 1;
                                                            $qAjExt = \App\Models\Mst_automatic_journal_detail_ext::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ]);
                                                        @endphp
                                                        <input type="hidden" id="totalCoaRow" name="totalCoaRow" value="{{ $qAjExt->count() }}">
                                                        @foreach ($qAjExt->get() as $a)
                                                            <tr id="row_{{ $i }}_add">
                                                                <td>
                                                                    <input type="hidden" name="order_no_{{ $i }}_add" value="{{ $i }}">
                                                                    <input type="hidden" name="debet_or_credit_{{ $i }}_add" value="Credit">
                                                                    <input type="hidden" name="desc_{{ $i }}_add" value="Bank">
                                                                    <input type="hidden" name="coa_dtl_id_{{ $i }}_add" value="{{ $a->id }}">
                                                                    <select class="form-select single-select @error('coa_id_'.$i.'_add') is-invalid @enderror"
                                                                        id="coa_id_{{ $i }}_add" name="coa_id_{{ $i }}_add" style="text-align: left;">
                                                                        <option value="#">Choose...</option>
                                                                        @foreach ($qPembayaranSupplierNonPPNCoas as $qPembSuppNonPPNfC)
                                                                            <option @if($a->coa_code_id==$qPembSuppNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembSuppNonPPNfC->id }}">
                                                                                {{ $qPembSuppNonPPNfC->coa_code_complete.' - '.$qPembSuppNonPPNfC->coa_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('coa_id_'.$i.'_add')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td>Bank</td>
                                                                <td>Credit</td>
                                                                <td style="text-align:center;"><input type="checkbox" id="rowCheck_{{ $i }}_add" value="{{ $i }}"></td>
                                                            </tr>
                                                            @php
                                                                $i += 1;
                                                            @endphp
                                                        @endforeach
                                                    @endif
                                                @endif

                                                @if ($methodId==3)
                                                    @if (old('totalCoaRow'))
                                                        <input type="hidden" id="totalCoaRow" name="totalCoaRow" value="{{ old('totalCoaRow') }}">
                                                        @for ($i=1;$i<=old('totalCoaRow');$i++)
                                                            @if (old('coa_id_'.$i.'_add'))
                                                                <tr id="row_{{ $i }}_add">
                                                                    <td>
                                                                        <input type="hidden" name="order_no_{{ $i }}_add" value="{{ $i }}">
                                                                        <input type="hidden" name="debet_or_credit_{{ $i }}_add" value="Credit">
                                                                        <input type="hidden" name="desc_{{ $i }}_add" value="{{ $methodNm }}">
                                                                        <input type="hidden" name="coa_dtl_id_{{ $i }}_add" value="{{ old('coa_dtl_id_'.$i.'_add') }}">
                                                                        <select class="form-select single-select @error('coa_id_'.$i.'_add') is-invalid @enderror"
                                                                            id="coa_id_{{ $i }}_add" name="coa_id_{{ $i }}_add" style="text-align: left;">
                                                                            <option value="#">Choose...</option>
                                                                            @foreach ($qPembayaranSupplierNonPPNCoas as $qPembSuppNonPPNfC)
                                                                                <option @if(old('coa_id_'.$i.'_add')==$qPembSuppNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembSuppNonPPNfC->id }}">
                                                                                    {{ $qPembSuppNonPPNfC->coa_code_complete.' - '.$qPembSuppNonPPNfC->coa_name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('coa_id_'.$i.'_add')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </td>
                                                                    <td>{{ $methodNm }}</td>
                                                                    <td>Credit</td>
                                                                    <td style="text-align:center;"><input type="checkbox" id="rowCheck_{{ $i }}_add" value="{{ $i }}"></td>
                                                                </tr>
                                                            @endif
                                                        @endfor
                                                    @else
                                                        @php
                                                            $i = 1;
                                                            $qAjExt = \App\Models\Mst_automatic_journal_detail_ext::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ]);
                                                        @endphp
                                                        <input type="hidden" id="totalCoaRow" name="totalCoaRow" value="{{ $qAjExt->count() }}">
                                                        @foreach ($qAjExt->get() as $a)
                                                            <tr id="row_{{ $i }}_add">
                                                                <td>
                                                                    <input type="hidden" name="order_no_{{ $i }}_add" value="{{ $i }}">
                                                                    <input type="hidden" name="debet_or_credit_{{ $i }}_add" value="Credit">
                                                                    <input type="hidden" name="desc_{{ $i }}_add" value="{{ $methodNm }}">
                                                                    <input type="hidden" name="coa_dtl_id_{{ $i }}_add" value="{{ $a->id }}">
                                                                    <select class="form-select single-select @error('coa_id_'.$i.'_add') is-invalid @enderror"
                                                                        id="coa_id_{{ $i }}_add" name="coa_id_{{ $i }}_add" style="text-align: left;">
                                                                        <option value="#">Choose...</option>
                                                                        @foreach ($qPembayaranSupplierNonPPNCoas as $qPembSuppNonPPNfC)
                                                                            <option @if($a->coa_code_id==$qPembSuppNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembSuppNonPPNfC->id }}">
                                                                                {{ $qPembSuppNonPPNfC->coa_code_complete.' - '.$qPembSuppNonPPNfC->coa_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('coa_id_'.$i.'_add')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td>{{ $methodNm }}</td>
                                                                <td>Credit</td>
                                                                <td style="text-align:center;"><input type="checkbox" id="rowCheck_{{ $i }}_add" value="{{ $i }}"></td>
                                                            </tr>
                                                            @php
                                                                $i += 1;
                                                            @endphp
                                                        @endforeach
                                                    @endif
                                                @endif

                                                @break

                                            @case(14)
                                                {{-- penerimaan customer non ppn --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="6">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="@switch($methodId)
                                                            @case(2)
                                                                {{ 'Bank' }}
                                                                @php
                                                                    $methodNm = 'bank';
                                                                @endphp
                                                                @break
                                                            @case(3)
                                                                {{ 'Customer Deposit' }}
                                                                @php
                                                                    $methodNm = 'customer deposit';
                                                                @endphp
                                                                @break
                                                            @default
                                                                {{ 'Cash' }}
                                                                @php
                                                                    $methodNm = 'cash';
                                                                @endphp
                                                        @endswitch">
                                                        @php
                                                            $qPenerimaanCustNonPPNCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'N',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPenerimaanCustNonPPNCoas as $qPembCustNonPPNfC)
                                                                <option @if($coaId==$qPembCustNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembCustNonPPNfC->id }}">
                                                                    {{ $qPembCustNonPPNfC->coa_code_complete.' - '.$qPembCustNonPPNfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        @switch($methodId)
                                                            @case(2)
                                                                {{ 'Bank' }}
                                                                @break
                                                            @case(3)
                                                                {{ 'Customer Deposit' }}
                                                                @break
                                                            @default
                                                                {{ 'Cash' }}
                                                        @endswitch
                                                    </td>
                                                    <td>Debet</td>
                                                </tr>

                                                <input type="hidden" name="order_no_2" value="2">
                                                <input type="hidden" name="debet_or_credit_2" value="Debet">
                                                <input type="hidden" name="desc_2" value="Discount">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'discount\'')
                                                    ->first();
                                                    $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                <tr>
                                                    <td>
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPenerimaanCustNonPPNCoas as $qPembCustNonPPNfC)
                                                                <option @if($coaId==$qPembCustNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembCustNonPPNfC->id }}">
                                                                    {{ $qPembCustNonPPNfC->coa_code_complete.' - '.$qPembCustNonPPNfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Discount</td>
                                                    <td>Debet</td>
                                                </tr>

                                                <input type="hidden" name="order_no_3" value="3">
                                                <input type="hidden" name="debet_or_credit_3" value="Debet">
                                                <input type="hidden" name="desc_3" value="Admin Bank">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'admin bank\'')
                                                    ->first();
                                                    $coaId = old('coa_id_3')?old('coa_id_3'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_3" value="@if(old('coa_dtl_id_3')){{ old('coa_dtl_id_3') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                @if ($methodId==1)
                                                    <input type="hidden" name="coa_id_3" id="coa_id_3" value="0">
                                                @else
                                                    <tr>
                                                        <td>
                                                            <select class="form-select single-select @error('coa_id_3') is-invalid @enderror" id="coa_id_3" name="coa_id_3" style="text-align: left;">
                                                                <option value="#">Choose...</option>
                                                                @foreach ($qPenerimaanCustNonPPNCoas as $qPembCustNonPPNfC)
                                                                    <option @if($coaId==$qPembCustNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembCustNonPPNfC->id }}">
                                                                        {{ $qPembCustNonPPNfC->coa_code_complete.' - '.$qPembCustNonPPNfC->coa_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('coa_id_3')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>Admin Bank</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                @endif

                                                <input type="hidden" name="order_no_4" value="4">
                                                <input type="hidden" name="debet_or_credit_4" value="Credit">
                                                <input type="hidden" name="desc_4" value="Penerimaan Lainnya">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'penerimaan lainnya\'')
                                                    ->first();
                                                    $coaId = old('coa_id_4')?old('coa_id_4'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_4" value="@if(old('coa_dtl_id_4')){{ old('coa_dtl_id_4') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                <tr>
                                                    <td>
                                                        <select class="form-select single-select @error('coa_id_4') is-invalid @enderror" id="coa_id_4" name="coa_id_4" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPenerimaanCustNonPPNCoas as $qPembCustNonPPNfC)
                                                                <option @if($coaId==$qPembCustNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembCustNonPPNfC->id }}">
                                                                    {{ $qPembCustNonPPNfC->coa_code_complete.' - '.$qPembCustNonPPNfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_4')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Penerimaan Lainnya</td>
                                                    <td>Credit</td>
                                                </tr>

                                                <input type="hidden" name="order_no_5" value="5">
                                                <input type="hidden" name="debet_or_credit_5" value="Credit">
                                                <input type="hidden" name="desc_5" value="Biaya Kirim">
                                                @php
                                                    $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                        'auto_journal_id'=>$qAutomaticJournal->id,
                                                        'method_id'=>$methodId,
                                                        'branch_id'=>$branchId,
                                                        'active'=>'Y',
                                                    ])
                                                    ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                                                    ->first();
                                                    $coaId = old('coa_id_5')?old('coa_id_5'):($qAj?$qAj->coa_code_id:0);
                                                @endphp
                                                <input type="hidden" name="coa_dtl_id_5" value="@if(old('coa_dtl_id_5')){{ old('coa_dtl_id_5') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                <tr>
                                                    <td>
                                                        <select class="form-select single-select @error('coa_id_5') is-invalid @enderror" id="coa_id_5" name="coa_id_5" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPenerimaanCustNonPPNCoas as $qPembCustNonPPNfC)
                                                                <option @if($coaId==$qPembCustNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembCustNonPPNfC->id }}">
                                                                    {{ $qPembCustNonPPNfC->coa_code_complete.' - '.$qPembCustNonPPNfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_5')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Biaya Kirim</td>
                                                    <td>Credit</td>
                                                </tr>

                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_6" value="6">
                                                        <input type="hidden" name="debet_or_credit_6" value="Credit">
                                                        <input type="hidden" name="desc_6" value="Piutang">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'piutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_6')?old('coa_id_6'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_6" value="@if(old('coa_dtl_id_6')){{ old('coa_dtl_id_6') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id6_6') is-invalid @enderror" id="coa_id_6" name="coa_id_6" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPenerimaanCustNonPPNCoas as $qPembCustNonPPNfC)
                                                                <option @if($coaId==$qPembCustNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembCustNonPPNfC->id }}">
                                                                    {{ $qPembCustNonPPNfC->coa_code_complete.' - '.$qPembCustNonPPNfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_6')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Piutang</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @if ($methodId==2)
                                                    @if (old('totalCoaRow'))
                                                        <input type="hidden" id="totalCoaRow" name="totalCoaRow" value="{{ old('totalCoaRow') }}">
                                                        @for ($i=1;$i<=old('totalCoaRow');$i++)
                                                            @if (old('coa_id_'.$i.'_add'))
                                                                <tr id="row_{{ $i }}_add">
                                                                    <td>
                                                                        <input type="hidden" name="order_no_{{ $i }}_add" value="{{ $i }}">
                                                                        <input type="hidden" name="debet_or_credit_{{ $i }}_add" value="Debet">
                                                                        <input type="hidden" name="desc_{{ $i }}_add" value="Bank">
                                                                        <input type="hidden" name="coa_dtl_id_{{ $i }}_add" value="{{ old('coa_dtl_id_'.$i.'_add') }}">
                                                                        <select class="form-select single-select @error('coa_id_'.$i.'_add') is-invalid @enderror"
                                                                            id="coa_id_{{ $i }}_add" name="coa_id_{{ $i }}_add" style="text-align: left;">
                                                                            <option value="#">Choose...</option>
                                                                            @foreach ($qPenerimaanCustNonPPNCoas as $qPembCustNonPPNfC)
                                                                                <option @if(old('coa_id_'.$i.'_add')==$qPembCustNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembCustNonPPNfC->id }}">
                                                                                    {{ $qPembCustNonPPNfC->coa_code_complete.' - '.$qPembCustNonPPNfC->coa_name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('coa_id_'.$i.'_add')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </td>
                                                                    <td>Bank</td>
                                                                    <td>Debet</td>
                                                                    <td style="text-align:center;"><input type="checkbox" id="rowCheck_{{ $i }}_add" value="{{ $i }}"></td>
                                                                </tr>
                                                            @endif
                                                        @endfor
                                                    @else
                                                        @php
                                                            $i = 1;
                                                            $qAjExt = \App\Models\Mst_automatic_journal_detail_ext::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ]);
                                                        @endphp
                                                        <input type="hidden" id="totalCoaRow" name="totalCoaRow" value="{{ $qAjExt->count() }}">
                                                        @foreach ($qAjExt->get() as $a)
                                                            <tr id="row_{{ $i }}_add">
                                                                <td>
                                                                    <input type="hidden" name="order_no_{{ $i }}_add" value="{{ $i }}">
                                                                    <input type="hidden" name="debet_or_credit_{{ $i }}_add" value="Debet">
                                                                    <input type="hidden" name="desc_{{ $i }}_add" value="Bank">
                                                                    <input type="hidden" name="coa_dtl_id_{{ $i }}_add" value="{{ $a->id }}">
                                                                    <select class="form-select single-select @error('coa_id_'.$i.'_add') is-invalid @enderror"
                                                                        id="coa_id_{{ $i }}_add" name="coa_id_{{ $i }}_add" style="text-align: left;">
                                                                        <option value="#">Choose...</option>
                                                                        @foreach ($qPenerimaanCustNonPPNCoas as $qPembCustNonPPNfC)
                                                                            <option @if($a->coa_code_id==$qPembCustNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPembCustNonPPNfC->id }}">
                                                                                {{ $qPembCustNonPPNfC->coa_code_complete.' - '.$qPembCustNonPPNfC->coa_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('coa_id_'.$i.'_add')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                                <td>Bank</td>
                                                                <td>Debet</td>
                                                                <td style="text-align:center;"><input type="checkbox" id="rowCheck_{{ $i }}_add" value="{{ $i }}"></td>
                                                            </tr>
                                                            @php
                                                                $i += 1;
                                                            @endphp
                                                        @endforeach
                                                    @endif
                                                @endif

                                                @break

                                            @case(15)
                                                {{-- purchase retur ppn --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="3">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="Hutang">
                                                        @php
                                                            $qPurchaseReturCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'P',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPurchaseReturCoas as $qPRePPNfC)
                                                                <option @if($coaId==$qPRePPNfC->id){{ 'selected' }}@endif value="{{ $qPRePPNfC->id }}">
                                                                    {{ $qPRePPNfC->coa_code_complete.' - '.$qPRePPNfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_2" value="2">
                                                        <input type="hidden" name="debet_or_credit_2" value="Credit">
                                                        <input type="hidden" name="desc_2" value="Inventory">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                            $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPurchaseReturCoas as $qPRePPNfC)
                                                                <option @if($coaId==$qPRePPNfC->id){{ 'selected' }}@endif value="{{ $qPRePPNfC->id }}">
                                                                    {{ $qPRePPNfC->coa_code_complete.' - '.$qPRePPNfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_3" value="3">
                                                        <input type="hidden" name="debet_or_credit_3" value="Credit">
                                                        <input type="hidden" name="desc_3" value="PPN Masukan">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'ppn masukan\'')
                                                            ->first();
                                                            $coaId = old('coa_id_3')?old('coa_id_3'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_3" value="@if(old('coa_dtl_id_3')){{ old('coa_dtl_id_3') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_3') is-invalid @enderror" id="coa_id_3" name="coa_id_3" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPurchaseReturCoas as $qPRePPNfC)
                                                                <option @if($coaId==$qPRePPNfC->id){{ 'selected' }}@endif value="{{ $qPRePPNfC->id }}">
                                                                    {{ $qPRePPNfC->coa_code_complete.' - '.$qPRePPNfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_3')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>PPN Masukan</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(16)
                                                {{-- purchase retur non ppn --}}
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="coa_row_count" value="2">
                                                        <input type="hidden" name="order_no_1" value="1">
                                                        <input type="hidden" name="debet_or_credit_1" value="Debet">
                                                        <input type="hidden" name="desc_1" value="Hutang">
                                                        @php
                                                            $qPurchaseReturNonPPNCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                                                            ->where(function ($query) {
                                                                $query->where([
                                                                    'local'=>'N',
                                                                ])
                                                                ->orWhere([
                                                                    'local'=>'A',
                                                                ]);
                                                            })
                                                            ->where([
                                                                'active'=>'Y',
                                                            ])
                                                            ->orderBy('coa_code_complete','ASC')
                                                            ->orderBy('coa_name','ASC')
                                                            ->get();

                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                            $coaId = old('coa_id_1')?old('coa_id_1'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_1" value="@if(old('coa_dtl_id_1')){{ old('coa_dtl_id_1') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_1') is-invalid @enderror" id="coa_id_1" name="coa_id_1" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPurchaseReturNonPPNCoas as $qPReNonPPNfC)
                                                                <option @if($coaId==$qPReNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPReNonPPNfC->id }}">
                                                                    {{ $qPReNonPPNfC->coa_code_complete.' - '.$qPReNonPPNfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="order_no_2" value="2">
                                                        <input type="hidden" name="debet_or_credit_2" value="Credit">
                                                        <input type="hidden" name="desc_2" value="Inventory">
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                            $coaId = old('coa_id_2')?old('coa_id_2'):($qAj?$qAj->coa_code_id:0);
                                                        @endphp
                                                        <input type="hidden" name="coa_dtl_id_2" value="@if(old('coa_dtl_id_2')){{ old('coa_dtl_id_2') }}@else{{ $qAj?$qAj->id:0 }}@endif">
                                                        <select class="form-select single-select @error('coa_id_2') is-invalid @enderror" id="coa_id_2" name="coa_id_2" style="text-align: left;">
                                                            <option value="#">Choose...</option>
                                                            @foreach ($qPurchaseReturNonPPNCoas as $qPReNonPPNfC)
                                                                <option @if($coaId==$qPReNonPPNfC->id){{ 'selected' }}@endif value="{{ $qPReNonPPNfC->id }}">
                                                                    {{ $qPReNonPPNfC->coa_code_complete.' - '.$qPReNonPPNfC->coa_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('coa_id_2')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @default

                                        @endswitch
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @if ($methodId==2)
                        <div class="card" style="margin-top: 15px;">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <input type="button" id="add-bank" class="btn btn-primary px-5" value="Add Bank">
                                        <input type="button" id="del-bank" class="btn btn-danger px-5" value="Delete Bank">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="save-btn" class="btn btn-primary px-5" value="Save">
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end row-->
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        @if ($qAutomaticJournal->id==12)
            // branch transfer out
            $("#branch_in_id").change(function(){
                location.href = '{{ url("admin/automatic-journal/".$qAutomaticJournal->id."/edit") }}?branch_id='+($("#branchId").val()===undefined?'':$("#branchId").val())+
                    '&branch_in_id='+($("#branch_in_id").val()===undefined?'':$("#branch_in_id").val());
            });
            // branch transfer in
            $("#branchId").change(function(){
                location.href = '{{ url("admin/automatic-journal/".$qAutomaticJournal->id."/edit") }}?branch_id='+($("#branchId").val()===undefined?'':$("#branchId").val())+
                    '&branch_in_id='+($("#branch_in_id").val()===undefined?'':$("#branch_in_id").val());
            });
        @else
            // methodId
            $("#methodId").change(function(){
                location.href = '{{ url('admin/automatic-journal/'.$qAutomaticJournal->id.'/edit') }}?method_id='+($("#methodId").val()===undefined?'':$("#methodId").val())+
                    '&branch_id='+($("#branchId").val()===undefined?'':$("#branchId").val());
            });

            // branch
            $("#branchId").change(function(){
                location.href = '{{ url('admin/automatic-journal/'.$qAutomaticJournal->id.'/edit') }}?method_id='+($("#methodId").val()===undefined?'':$("#methodId").val())+
                    '&branch_id='+($("#branchId").val()===undefined?'':$("#branchId").val());
            });
        @endif

        $("#save-btn").click(function() {
            if(!confirm("Data will be saved.\nContinue?")){
                event.preventDefault();
            }else{
                $("#ope").val('sv');
                $("#submit-form").submit();
            }
        });

        $("#back-btn").click(function() {
            location.href = '{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder) }}';
        });

        @if ($methodId==2)
            @php
                $select = '';
            @endphp
            $("#add-bank").click(function() {
                let totalRow = parseInt($("#totalCoaRow").val());
                let rowNo = totalRow+1;

                @switch($qAutomaticJournal->id)
                    @case(7)
                        // penerimaan customer ppn
                        @php
                            $qPenerimaanCustPPNCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                            ->where(function ($query) {
                                $query->where([
                                    'local'=>'P',
                                ])
                                ->orWhere([
                                    'local'=>'A',
                                ]);
                            })
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('coa_code_complete','ASC')
                            ->orderBy('coa_name','ASC')
                            ->get();
                        @endphp
                        @foreach ($qPenerimaanCustPPNCoas as $q)
                            @php
                                $select .= '<option value="'.$q->id.'">'.$q->coa_code_complete.' - '.$q->coa_name.'</option>';
                            @endphp
                        @endforeach
                        let vHtml = '<tr id="row_'+rowNo+'_add">'+
                            '<td>'+
                                '<input type="hidden" name="order_no_'+rowNo+'_add" value="'+rowNo+'">'+
                                '<input type="hidden" name="debet_or_credit_'+rowNo+'_add" value="Debet">'+
                                '<input type="hidden" name="desc_'+rowNo+'_add" value="Bank">'+
                                '<input type="hidden" name="coa_dtl_id_'+rowNo+'_add" value="0">'+
                                '<select class="form-select single-select" id="coa_id_'+rowNo+'_add" name="coa_id_'+rowNo+'_add" style="text-align: left;">'+
                                    '<option value="#">Choose...</option>{!! $select !!}'+
                                '</select>'+
                            '</td>'+
                            '<td>Bank</td>'+
                            '<td>Debet</td>'+
                            '<td style="text-align:center;"><input type="checkbox" id="rowCheck_'+rowNo+'_add" value="'+rowNo+'"></td>'+
                        '</tr>';
                        @break

                    @case(8)
                        // pembayaran supplier ppn
                        @php
                            $qPembayaranSupplierPPNCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                            ->where(function ($query) {
                                $query->where([
                                    'local'=>'P',
                                ])
                                ->orWhere([
                                    'local'=>'A',
                                ]);
                            })
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('coa_code_complete','ASC')
                            ->orderBy('coa_name','ASC')
                            ->get();
                        @endphp
                        @foreach ($qPembayaranSupplierPPNCoas as $q)
                            @php
                                $select .= '<option value="'.$q->id.'">'.$q->coa_code_complete.' - '.$q->coa_name.'</option>';
                            @endphp
                        @endforeach
                        let vHtml = '<tr id="row_'+rowNo+'_add">'+
                            '<td>'+
                                '<input type="hidden" name="order_no_'+rowNo+'_add" value="'+rowNo+'">'+
                                '<input type="hidden" name="debet_or_credit_'+rowNo+'_add" value="Credit">'+
                                '<input type="hidden" name="desc_'+rowNo+'_add" value="Bank">'+
                                '<input type="hidden" name="coa_dtl_id_'+rowNo+'_add" value="0">'+
                                '<select class="form-select single-select" id="coa_id_'+rowNo+'_add" name="coa_id_'+rowNo+'_add" style="text-align: left;">'+
                                    '<option value="#">Choose...</option>{!! $select !!}'+
                                '</select>'+
                            '</td>'+
                            '<td>Bank</td>'+
                            '<td>Credit</td>'+
                            '<td style="text-align:center;"><input type="checkbox" id="rowCheck_'+rowNo+'_add" value="'+rowNo+'"></td>'+
                        '</tr>';
                        @break

                    @case(13)
                        // pembayaran supplier non ppn
                        @php
                            $qPembayaranSupplierNonPPNCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                            ->where(function ($query) {
                                $query->where([
                                    'local'=>'N',
                                ])
                                ->orWhere([
                                    'local'=>'A',
                                ]);
                            })
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('coa_code_complete','ASC')
                            ->orderBy('coa_name','ASC')
                            ->get();
                        @endphp
                        @foreach ($qPembayaranSupplierNonPPNCoas as $q)
                            @php
                                $select .= '<option value="'.$q->id.'">'.$q->coa_code_complete.' - '.$q->coa_name.'</option>';
                            @endphp
                        @endforeach
                        let vHtml = '<tr id="row_'+rowNo+'_add">'+
                            '<td>'+
                                '<input type="hidden" name="order_no_'+rowNo+'_add" value="'+rowNo+'">'+
                                '<input type="hidden" name="debet_or_credit_'+rowNo+'_add" value="Credit">'+
                                '<input type="hidden" name="desc_'+rowNo+'_add" value="Bank">'+
                                '<input type="hidden" name="coa_dtl_id_'+rowNo+'_add" value="0">'+
                                '<select class="form-select single-select" id="coa_id_'+rowNo+'_add" name="coa_id_'+rowNo+'_add" style="text-align: left;">'+
                                    '<option value="#">Choose...</option>{!! $select !!}'+
                                '</select>'+
                            '</td>'+
                            '<td>Bank</td>'+
                            '<td>Credit</td>'+
                            '<td style="text-align:center;"><input type="checkbox" id="rowCheck_'+rowNo+'_add" value="'+rowNo+'"></td>'+
                        '</tr>';
                        @break

                    @case(14)
                        // penerimaan customer non ppn
                        @php
                            $qPenerimaanCustNonPPNCoas = \App\Models\Mst_coa::where('is_master_coa','<>','Y')
                            ->where(function ($query) {
                                $query->where([
                                    'local'=>'N',
                                ])
                                ->orWhere([
                                    'local'=>'A',
                                ]);
                            })
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('coa_code_complete','ASC')
                            ->orderBy('coa_name','ASC')
                            ->get();
                        @endphp
                        @foreach ($qPenerimaanCustNonPPNCoas as $q)
                            @php
                                $select .= '<option value="'.$q->id.'">'.$q->coa_code_complete.' - '.$q->coa_name.'</option>';
                            @endphp
                        @endforeach
                        let vHtml = '<tr id="row_'+rowNo+'_add">'+
                            '<td>'+
                                '<input type="hidden" name="order_no_'+rowNo+'_add" value="'+rowNo+'">'+
                                '<input type="hidden" name="debet_or_credit_'+rowNo+'_add" value="Debet">'+
                                '<input type="hidden" name="desc_'+rowNo+'_add" value="Bank">'+
                                '<input type="hidden" name="coa_dtl_id_'+rowNo+'_add" value="0">'+
                                '<select class="form-select single-select" id="coa_id_'+rowNo+'_add" name="coa_id_'+rowNo+'_add" style="text-align: left;">'+
                                    '<option value="#">Choose...</option>{!! $select !!}'+
                                '</select>'+
                            '</td>'+
                            '<td>Bank</td>'+
                            '<td>Debet</td>'+
                            '<td style="text-align:center;"><input type="checkbox" id="rowCheck_'+rowNo+'_add" value="'+rowNo+'"></td>'+
                        '</tr>';
                        @break

                    @default
                @endswitch

                // let vHtml = '<tr id="row_'+rowNo+'_add">'+
                //     '<td>'+
                //         '<input type="hidden" name="order_no_'+rowNo+'_add" value="'+rowNo+'">'+
                //         '<input type="hidden" name="debet_or_credit_'+rowNo+'_add" value="Credit">'+
                //         '<input type="hidden" name="desc_'+rowNo+'_add" value="Bank">'+
                //         '<input type="hidden" name="coa_dtl_id_'+rowNo+'_add" value="0">'+
                //         '<select class="form-select single-select" id="coa_id_'+rowNo+'_add" name="coa_id_'+rowNo+'_add" style="text-align: left;">'+
                //             '<option value="#">Choose...</option>{!! $select !!}'+
                //         '</select>'+
                //     '</td>'+
                //     '<td>Bank</td>'+
                //     '<td>Credit</td>'+
                //     '<td style="text-align:center;"><input type="checkbox" id="rowCheck_'+rowNo+'_add" value="'+rowNo+'"></td>'+
                // '</tr>';
                $("#new-coa-row").append(vHtml);
                $("#totalCoaRow").val(rowNo);

                $('.single-select').select2({
                    theme: 'bootstrap4',
                    width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                    placeholder: $(this).data('placeholder'),
                    allowClear: Boolean($(this).data('allow-clear')),
                });
            });

            $("#del-bank").click(function() {
                for (i=1;i<=$("#totalCoaRow").val();i++) {
                    if ($("#rowCheck_"+i+"_add").is(':checked')) {
                        $("#row_"+i+"_add").remove();
                    }
                }
            });
        @endif

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
