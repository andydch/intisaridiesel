<?php

namespace App\Imports\admin;

use App\Models\Mst_branch;
use App\Models\Mst_brand_type;
use App\Models\Mst_part;
use App\Models\Mst_part_brand_type;
use App\Models\Mst_part_subtitution;
use App\Models\Tmp_part;
use App\Models\Tx_qty_part;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class PartImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach($rows as $row){
            $part_number = str_replace("-","",trim($row[0]," "));
            $part_number = str_replace("'","",$part_number);
            $part_number = str_replace(" ","",$part_number);    // hapus spasinya
            $part_name = !is_null($row[1])?$row[1]:'-';
            $part_type_id = $row[3];
            $part_category_id = $row[4];
            // $part_number_subtitutions = !is_null($row[6])?str_replace("-","",$row[6]):'';
            $brand_id = $row[10];
            // $brand_type_name = $row[11];
            $branch_initial = $row[11];
            $weight = 0;
            $weight_id = null;
            $quantity_type_id = $row[8];
            $part_brand = $row[10];
            // $max_stock = 0;
            // $safety_stock = 0;
            // $price_list = 0;
            // $final_price = 0;
            $avg_cost = !is_numeric($row[6])?0:$row[6];
            $qtyOHperBranch = !is_numeric($row[5])?0:$row[5];
            // $initial_cost = 0;
            // $final_cost = 0;
            // $total_cost = 0;
            // $total_sales = 0;
            // $fob_currency = 0;
            // $final_fob = 0;
            $active = 'Y';

            if ($part_number!='PARTS NO' && $part_number!='PARTSNO'){
                $insTmp = Tmp_part::create([
                    'part_number' => $part_number,
                    'branch_initial' => $branch_initial,
                ]);

                $part_id = 0;
                $qParts = Mst_part::whereRaw('REPLACE(part_number," ","")=?',[$part_number])
                ->first();
                // $qParts = Mst_part::where([
                //     'part_number' => $part_number,
                // ])
                // ->first();
                if($qParts){
                    $upd = Mst_part::where([
                        'id' => $qParts->id,
                    ])
                    ->update([
                        'part_number'=>$part_number,
                        'part_name'=>$part_name,
                        // 'part_type_id'=>$part_type_id,
                        // 'part_category_id'=>$part_category_id,
                        'brand_id'=>$brand_id,
                        // 'weight'=>$weight,
                        // 'weight_id'=>$weight_id,
                        'quantity_type_id'=>$quantity_type_id,
                        'part_brand'=>$part_brand,
                        // 'max_stock',
                        // 'safety_stock',
                        // 'price_list',
                        // 'final_price',
                        'avg_cost'=>$avg_cost,
                        // 'initial_cost',
                        // 'final_cost',
                        // 'total_cost',
                        // 'total_sales',
                        // 'fob_currency',
                        // 'final_fob',
                        'active' => $active,
                        'updated_by' => Auth::user()->id,
                    ]);
                    $part_id = $qParts->id;
                }else{
                    $ins = Mst_part::create([
                        'part_number'=>$part_number,
                        'part_name'=>$part_name,
                        'part_type_id'=>$part_type_id,
                        'part_category_id'=>$part_category_id,
                        'brand_id'=>$brand_id,
                        'weight'=>$weight,
                        'weight_id'=>$weight_id,
                        'quantity_type_id'=>$quantity_type_id,
                        'part_brand'=>$part_brand,
                        // 'max_stock',
                        // 'safety_stock',
                        // 'price_list',
                        // 'final_price',
                        'avg_cost'=>$avg_cost,
                        // 'initial_cost',
                        // 'final_cost',
                        // 'total_cost',
                        // 'total_sales',
                        // 'fob_currency',
                        // 'final_fob',
                        'active' => $active,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                    $part_id = $ins->id;
                }

                // master brand type
                // if (!is_null($brand_type_name)){
                //     $brand_type_id = 0;
                //     $qBrandTypes = Mst_brand_type::where([
                //         'brand_id' => $brand_id,
                //         'brand_type'=>$brand_type_name,
                //     ])
                //     ->first();
                //     if ($qBrandTypes){
                //         $brand_type_id = $qBrandTypes->id;
                //     }else{
                //         $insBTy = Mst_brand_type::create([
                //             'brand_id' => $brand_id,
                //             'brand_type' => $brand_type_name,
                //             'active' => 'Y',
                //             'created_by' => Auth::user()->id,
                //             'updated_by' => Auth::user()->id,
                //         ]);
                //         $brand_type_id = $insBTy->id;
                //     }
                // }

                // part brand type
                // $qPartBrandType = Mst_part_brand_type::where([
                //     'part_id' => $part_id,
                //     'brand_id' => $brand_id,
                //     'brand_type_id' => $brand_type_id,
                // ])
                // ->first();
                // if(!$qPartBrandType){
                //     $insPBT = Mst_part_brand_type::create([
                //         'part_id' => $part_id,
                //         'brand_id' => $brand_id,
                //         'brand_type_id' => $brand_type_id,
                //         'active' => 'Y',
                //         'created_by' => Auth::user()->id,
                //         'updated_by' => Auth::user()->id,
                //     ]);
                // }

                // branches
                $branches = Mst_branch::where('active','=','Y')
                ->get();
                foreach($branches AS $branch){
                    $partQty = Tx_qty_part::where([
                        'part_id' => $part_id,
                        'branch_id' => $branch->id,
                    ])
                    ->first();
                    if(!$partQty){
                        $insQty = Tx_qty_part::create([
                            'part_id' => $part_id,
                            'qty' => 0,
                            'branch_id' => $branch->id,
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }
                }

                // branch initial - $branch_initial
                $qBranch = Mst_branch::where([
                    'initial' => $branch_initial,
                ])
                ->first();
                if ($qBranch){
                    $updQty = Tx_qty_part::where([
                        'part_id' => $part_id,
                        'branch_id' => $qBranch->id,
                    ])
                    ->update([
                        'qty' => $qtyOHperBranch,
                        'updated_by' => Auth::user()->id,
                    ]);
                }

                // part number substitution
                // if($part_number_subtitutions!=''){
                //     $updSbt = Mst_part_subtitution::where([
                //         'part_id' => $part_id,
                //     ])
                //     ->update([
                //         'active' => 'N',
                //         'updated_by' => Auth::user()->id,
                //     ]);

                //     $part_sbt = explode(",",$part_number_subtitutions);
                //     foreach($part_sbt AS $sbt){
                //         $sbt_id = 0;
                //         $qParts = Mst_part::where([
                //             'part_number' => str_replace("-","",trim($sbt," ")),
                //         ])
                //         ->first();
                //         if($qParts){
                //             $sbt_id = $qParts->id;
                //         }
                //         $qPartSbt = Mst_part_subtitution::where([
                //             'part_id' => $part_id,
                //             'part_other_id' => $sbt_id,
                //         ])
                //         ->first();
                //         if($sbt_id>0){
                //             if ($qPartSbt){
                //                 $updSbt = Mst_part_subtitution::where([
                //                     'part_id' => $part_id,
                //                     'part_other_id' => $sbt_id,
                //                 ])
                //                 ->update([
                //                     'active' => 'Y',
                //                     'updated_by' => Auth::user()->id,
                //                 ]);
                //             }else{
                //                 $insSbt = Mst_part_subtitution::create([
                //                     'part_id' => $part_id,
                //                     'part_other_id' => $sbt_id,
                //                     'active' => 'Y',
                //                     'created_by' => Auth::user()->id,
                //                     'updated_by' => Auth::user()->id,
                //                 ]);
                //             }
                //         }
                //     }
                // }
            }
        }

        // cek qty yg belum ada part nya
        $qParts = Mst_part::whereNotIn('id',function($query){
            $query->select('part_id')
            ->from('tx_qty_parts');
        })
        ->get();
        foreach($qParts as $qP){
            $branches = Mst_branch::where('active','=','Y')
            ->get();
            foreach($branches AS $branch){
                $partQty = Tx_qty_part::where([
                    'part_id' => $qP->id,
                    'branch_id' => $branch->id,
                ])
                ->first();
                if(!$partQty){
                    $insQty = Tx_qty_part::create([
                        'part_id' => $qP->id,
                        'qty' => 0,
                        'branch_id' => $branch->id,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                }
            }
        }
    }
}
