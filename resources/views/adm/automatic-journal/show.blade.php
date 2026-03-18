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
            <form id="submit-form">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    {{-- @if($errors->any())
                    Error:
                    {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                    @endif --}}
                    <div class="card">
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
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
                                        @error('branchId')
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
                                        @error('branchId')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            <div class="row mb-3">
                                <label for="branchId" class="col-sm-3 col-form-label">Branch @if($qAutomaticJournal->id==12){{ 'Transfer Out' }}@endif</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('branchId') is-invalid @enderror" id="branchId" name="branchId">
                                        <option value="#">Choose...</option>
                                        @php
                                            $branchId = old('branchId')?old('branchId'):(isset($branch_id)?$branch_id:0);
                                        @endphp
                                        @foreach ($qBranch as $qB)
                                            <option @if ($branchId==$qB->id) {{ 'selected' }} @endif value="{{ $qB->id }}">{{ $qB->name }}</option>
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $isPpn = 'N';
                                            $isNonPpn = 'N';
                                            if ($qAutomaticJournal->id==1 || $qAutomaticJournal->id==2 || $qAutomaticJournal->id==5 || $qAutomaticJournal->id==7 ||
                                                $qAutomaticJournal->id==8 || $qAutomaticJournal->id==11 || $qAutomaticJournal->id==12){
                                                $isPpn = 'Y';
                                            }
                                            if ($qAutomaticJournal->id==3 || $qAutomaticJournal->id==4 || $qAutomaticJournal->id==6 || $qAutomaticJournal->id==13 ||
                                                $qAutomaticJournal->id==14){
                                                $isNonPpn = 'Y';
                                            }
                                        @endphp
                                        @switch($qAutomaticJournal->id)
                                            @case(1)
                                                {{-- faktur --}}
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'cogs\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>COGS</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'piutang\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Piutang</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'sales pajak\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Sales Pajak</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'ppn keluaran\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>PPN Keluaran</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(2)
                                                {{-- nota retur --}}
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'sales retur pajak\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Sales Retur Pajak</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'ppn keluaran\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>PPN Keluaran</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'piutang\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Piutang</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'cogs\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>COGS</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(3)
                                                {{-- nota penjualan --}}
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'cogs\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>COGS</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'piutang\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Piutang</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'sales non pajak\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Sales Non Pajak</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(4)
                                                {{-- retur ppn --}}
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'sales retur non pajak\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Sales Retur Non Pajak</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'piutang\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Piutang</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'cogs\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>COGS</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(5)
                                                {{-- receipt order ppn --}}
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Debet</td>
                                                </tr>
                                                {{-- <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'bea masuk import\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Bea Masuk Import</td>
                                                    <td>Debet</td>
                                                </tr> --}}
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'ppn masukan\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>PPN Masukan</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(6)
                                                {{-- receipt order non ppn --}}
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(7)
                                                <tr>
                                                    <td>
                                                        @switch($methodId)
                                                            @case(2)
                                                                @php
                                                                    $methodNm = 'Bank';                                                                    
                                                                @endphp
                                                                @break
                                                            @case(3)
                                                                @php
                                                                    $methodNm = 'Customer Deposit';                                                                    
                                                                @endphp
                                                                @break
                                                            @default
                                                                @php
                                                                    $methodNm = 'Cash';                                                                    
                                                                @endphp
                                                        @endswitch
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            // ->whereRaw('LOWER(`desc`)=\'cash\'')
                                                            ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>{{ $methodNm }}</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'discount\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Discount</td>
                                                    <td>Debet</td>
                                                </tr>
                                                @if ($methodId!=1)
                                                    <tr>
                                                        <td>
                                                            @php
                                                                $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                    'auto_journal_id'=>$qAutomaticJournal->id,
                                                                    'method_id'=>$methodId,
                                                                    'branch_id'=>$branchId,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->whereRaw('LOWER(`desc`)=\'admin bank\'')
                                                                ->first();
                                                            @endphp
                                                            @if ($qAj)
                                                                @php
                                                                    $qCoa = \App\Models\Mst_coa::where([
                                                                        'id'=>$qAj->coa_code_id,
                                                                        'active'=>'Y',
                                                                    ])
                                                                    ->first();
                                                                @endphp
                                                                @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                            @endif
                                                        </td>
                                                        <td>Admin Bank</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'penerimaan lainnya\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Penerimaan Lainnya</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Biaya Kirim</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'piutang\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Piutang</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @if ($methodId==2)
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
                                                                @php
                                                                    $qCoa = \App\Models\Mst_coa::where([
                                                                        'id'=>$a->coa_code_id,
                                                                        'active'=>'Y',
                                                                    ])
                                                                    ->first();
                                                                @endphp
                                                                @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                            </td>
                                                            <td>Bank</td>
                                                            <td>Debet</td>
                                                        </tr>
                                                        @php
                                                            $i += 1;
                                                        @endphp
                                                    @endforeach
                                                @endif

                                                @break

                                            @case(8)
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Debet</td>
                                                </tr>
                                                @if ($methodId==2 || $methodId==3)
                                                    <tr>
                                                        <td>
                                                            @php
                                                                $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                    'auto_journal_id'=>$qAutomaticJournal->id,
                                                                    'method_id'=>$methodId,
                                                                    'branch_id'=>$branchId,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->whereRaw('LOWER(`desc`)=\'bank admin\'')
                                                                ->first();
                                                            @endphp
                                                            @if ($qAj)
                                                                @php
                                                                    $qCoa = \App\Models\Mst_coa::where([
                                                                        'id'=>$qAj->coa_code_id,
                                                                        'active'=>'Y',
                                                                    ])
                                                                    ->first();
                                                                @endphp
                                                                @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                            @endif
                                                        </td>
                                                        <td>Bank Admin</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'biaya asuransi\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Biaya Asuransi</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Biaya Kirim</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'biaya lainnya\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Biaya Lainnya</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'discount\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Discount</td>
                                                    <td>Credit</td>
                                                </tr>
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
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>{{ $methodNm }}</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @if ($methodId==2)
                                                    {{-- bank --}}
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
                                                                @php
                                                                    $qCoa = \App\Models\Mst_coa::where([
                                                                        'id'=>$a->coa_code_id,
                                                                        'active'=>'Y',
                                                                    ])
                                                                    ->first();
                                                                @endphp
                                                                @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                            </td>
                                                            <td>Bank</td>
                                                            <td>Credit</td>
                                                        </tr>
                                                        @php
                                                            $i += 1;
                                                        @endphp
                                                    @endforeach
                                                @endif
                                                @if ($methodId==3)
                                                    {{-- advance payment --}}
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
                                                                @php
                                                                    $qCoa = \App\Models\Mst_coa::where([
                                                                        'id'=>$a->coa_code_id,
                                                                        'active'=>'Y',
                                                                    ])
                                                                    ->first();
                                                                @endphp
                                                                @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                            </td>
                                                            <td>Advance Payment</td>
                                                            <td>Credit</td>
                                                        </tr>
                                                        @php
                                                            $i += 1;
                                                        @endphp
                                                    @endforeach
                                                @endif

                                                @break

                                            @case(9)

                                                @break

                                            @case(10)

                                                @break

                                            @case(11)
                                                <tr>
                                                    <td colspan="3" style="font-weight: 700;">
                                                        Stock Adjustment Plus
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory-plus\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'cogs-plus\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
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
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'cogs-minus\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>COGS</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory-minus\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(12)
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branch_id,
                                                                'branch_in_id'=>$branch_in_id_r,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'transfer in\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Transfer In</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'branch_in_id'=>$branch_in_id_r,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'transfer out\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Transfer Out</td>
                                                    <td>Debet</td>
                                                </tr>

                                                @break

                                            @case(13)
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Debet</td>
                                                </tr>
                                                @if ($methodId==2 || $methodId==3)
                                                    <tr>
                                                        <td>
                                                            @php
                                                                $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                    'auto_journal_id'=>$qAutomaticJournal->id,
                                                                    'method_id'=>$methodId,
                                                                    'branch_id'=>$branchId,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->whereRaw('LOWER(`desc`)=\'bank admin\'')
                                                                ->first();
                                                            @endphp
                                                            @if ($qAj)
                                                                @php
                                                                    $qCoa = \App\Models\Mst_coa::where([
                                                                        'id'=>$qAj->coa_code_id,
                                                                        'active'=>'Y',
                                                                    ])
                                                                    // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                    //     $q->where([
                                                                    //         'branch_id'=>$branch_id,
                                                                    //     ]);
                                                                    // })
                                                                    ->where('is_master_coa','<>','Y')
                                                                    ->when($isPpn=='Y', function($q){
                                                                        $q->whereIn('local',['P','A']);
                                                                    })
                                                                    ->when($isNonPpn=='Y', function($q){
                                                                        $q->whereIn('local',['N','A']);
                                                                    })
                                                                    ->first();
                                                                @endphp
                                                                @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                            @endif
                                                        </td>
                                                        <td>Bank Admin</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'biaya asuransi\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Biaya Asuransi</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Biaya Kirim</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'biaya lainnya\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Biaya Lainnya</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'discount\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Discount</td>
                                                    <td>Credit</td>
                                                </tr>
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
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif                                                        
                                                        @endif
                                                    </td>
                                                    <td>{{ $methodNm }}</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @if ($methodId==2 || $methodId==3)
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
                                                                @php
                                                                    $qCoa = \App\Models\Mst_coa::where([
                                                                        'id'=>$a->coa_code_id,
                                                                        'active'=>'Y',
                                                                    ])
                                                                    // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                    //     $q->where([
                                                                    //         'branch_id'=>$branch_id,
                                                                    //     ]);
                                                                    // })
                                                                    ->where('is_master_coa','<>','Y')
                                                                    ->when($isPpn=='Y', function($q){
                                                                        $q->whereIn('local',['P','A']);
                                                                    })
                                                                    ->when($isNonPpn=='Y', function($q){
                                                                        $q->whereIn('local',['N','A']);
                                                                    })
                                                                    ->first();
                                                                @endphp
                                                                @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                            </td>
                                                            <td>{{ $methodNm }}</td>
                                                            <td>Credit</td>
                                                        </tr>
                                                        @php
                                                            $i += 1;
                                                        @endphp
                                                    @endforeach
                                                @endif

                                                @break

                                            @case(14)
                                                <tr>
                                                    <td>
                                                        @switch($methodId)
                                                            @case(2)
                                                                @php
                                                                    $methodNm = 'Bank';                                                                    
                                                                @endphp
                                                                @break
                                                            @case(3)
                                                                @php
                                                                    $methodNm = 'Customer Deposit';                                                                    
                                                                @endphp
                                                                @break
                                                            @default
                                                                @php
                                                                    $methodNm = 'Cash';                                                                    
                                                                @endphp
                                                        @endswitch
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            // ->whereRaw('LOWER(`desc`)=\'cash\'')
                                                            ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>{{ $methodNm }}</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'discount\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Discount</td>
                                                    <td>Debet</td>
                                                </tr>
                                                @if ($methodId!=1)
                                                    <tr>
                                                        <td>
                                                            @php
                                                                $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                    'auto_journal_id'=>$qAutomaticJournal->id,
                                                                    'method_id'=>$methodId,
                                                                    'branch_id'=>$branchId,
                                                                    'active'=>'Y',
                                                                ])
                                                                ->whereRaw('LOWER(`desc`)=\'admin bank\'')
                                                                ->first();
                                                            @endphp
                                                            @if ($qAj)
                                                                @php
                                                                    $qCoa = \App\Models\Mst_coa::where([
                                                                        'id'=>$qAj->coa_code_id,
                                                                        'active'=>'Y',
                                                                    ])
                                                                    // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                    //     $q->where([
                                                                    //         'branch_id'=>$branch_id,
                                                                    //     ]);
                                                                    // })
                                                                    ->where('is_master_coa','<>','Y')
                                                                    ->when($isPpn=='Y', function($q){
                                                                        $q->whereIn('local',['P','A']);
                                                                    })
                                                                    ->when($isNonPpn=='Y', function($q){
                                                                        $q->whereIn('local',['N','A']);
                                                                    })
                                                                    ->first();
                                                                @endphp
                                                                @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                            @endif
                                                        </td>
                                                        <td>Admin Bank</td>
                                                        <td>Debet</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'penerimaan lainnya\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Penerimaan Lainnya</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Biaya Kirim</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'method_id'=>$methodId,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'piutang\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Piutang</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @if ($methodId==2)
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
                                                                @php
                                                                    $qCoa = \App\Models\Mst_coa::where([
                                                                        'id'=>$a->coa_code_id,
                                                                        'active'=>'Y',
                                                                    ])
                                                                    // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                    //     $q->where([
                                                                    //         'branch_id'=>$branch_id,
                                                                    //     ]);
                                                                    // })
                                                                    ->where('is_master_coa','<>','Y')
                                                                    ->when($isPpn=='Y', function($q){
                                                                        $q->whereIn('local',['P','A']);
                                                                    })
                                                                    ->when($isNonPpn=='Y', function($q){
                                                                        $q->whereIn('local',['N','A']);
                                                                    })
                                                                    ->first();
                                                                @endphp
                                                                @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                            </td>
                                                            <td>Bank</td>
                                                            <td>Debet</td>
                                                        </tr>
                                                        @php
                                                            $i += 1;
                                                        @endphp
                                                    @endforeach
                                                @endif

                                                @break

                                            @case(15)
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branch_id,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Inventory</td>
                                                    <td>Credit</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'ppn masukan\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>PPN Masukan</td>
                                                    <td>Credit</td>
                                                </tr>

                                                @break

                                            @case(16)
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branch_id,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
                                                    </td>
                                                    <td>Hutang</td>
                                                    <td>Debet</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        @php
                                                            $qAj = \App\Models\Mst_automatic_journal_detail::where([
                                                                'auto_journal_id'=>$qAutomaticJournal->id,
                                                                'branch_id'=>$branchId,
                                                                'active'=>'Y',
                                                            ])
                                                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                                                            ->first();
                                                        @endphp
                                                        @if ($qAj)
                                                            @php
                                                                $qCoa = \App\Models\Mst_coa::where([
                                                                    'id'=>$qAj->coa_code_id,
                                                                    'active'=>'Y',
                                                                ])
                                                                // ->when(is_numeric($branch_id), function($q) use($branch_id){
                                                                //     $q->where([
                                                                //         'branch_id'=>$branch_id,
                                                                //     ]);
                                                                // })
                                                                ->where('is_master_coa','<>','Y')
                                                                ->when($isPpn=='Y', function($q){
                                                                    $q->whereIn('local',['P','A']);
                                                                })
                                                                ->when($isNonPpn=='Y', function($q){
                                                                    $q->whereIn('local',['N','A']);
                                                                })
                                                                ->first();
                                                            @endphp
                                                            @if ($qCoa){{ $qCoa->coa_code_complete.' - '.$qCoa->coa_name }}@endif
                                                        @endif
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

                            {{-- <div class="row mb-3">
                                <label for="fp_no" class="col-sm-3 col-form-label">FP No</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control @error('prefiks_code') is-invalid @enderror"
                                        maxlength="3" id="prefiks_code" name="prefiks_code" placeholder=""
                                        value="{{ $fp_nos->prefiks_code }}">
                                    @error('prefiks_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-8">
                                    <input readonly type="text" class="form-control @error('fp_no') is-invalid @enderror"
                                        maxlength="255" id="fp_no" name="fp_no" placeholder="FP No"
                                        value="{{ $fp_nos->fp_no }}">
                                    @error('fp_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    {{-- <input type="button" id="save-btn" class="btn btn-primary px-5" value="Save"> --}}
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
                location.href = '{{ url("admin/automatic-journal/".$qAutomaticJournal->id) }}?branch_id='+($("#branchId").val()===undefined?'':$("#branchId").val())+
                    '&branch_in_id='+($("#branch_in_id").val()===undefined?'':$("#branch_in_id").val());
            });
            // branch transfer in
            $("#branchId").change(function(){
                location.href = '{{ url("admin/automatic-journal/".$qAutomaticJournal->id) }}?branch_id='+($("#branchId").val()===undefined?'':$("#branchId").val())+
                    '&branch_in_id='+($("#branch_in_id").val()===undefined?'':$("#branch_in_id").val());
            });
        @else
            // methodId
            $("#methodId").change(function(){
                location.href = '{{ url('admin/automatic-journal/'.$qAutomaticJournal->id) }}?method_id='+($("#methodId").val()===undefined?'':$("#methodId").val())+
                    '&branch_id='+($("#branchId").val()===undefined?'':$("#branchId").val());
            });

            // branch
            $("#branchId").change(function(){
                location.href = '{{ url('admin/automatic-journal/'.$qAutomaticJournal->id) }}?method_id='+($("#methodId").val()===undefined?'':$("#methodId").val())+
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

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
