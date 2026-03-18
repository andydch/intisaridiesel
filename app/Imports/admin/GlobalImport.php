<?php

namespace App\Imports\admin;

use App\Models\Mst_global;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class GlobalImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $id = $row[0];
            $data_cat = $row[1];
            $title_ind = $row[2];
            $title_eng = $row[3];
            $slug = $row[4];
            $order_no = $row[5];
            $notes = $row[6];
            $small_desc_ind = $row[7];
            $small_desc_eng = $row[8];
            $long_desc_ind = $row[9];
            $long_desc_eng = $row[10];
            $string_val = $row[11];
            $numeric_val = $row[12];
            $active = $row[13];
            if (strtolower($id) != 'id') {
                $query = Mst_global::where('id', '=', $id)
                    ->first();
                if (!$query) {
                    $ins = Mst_global::create([
                        'data_cat' => $data_cat,
                        'title_ind' => $title_ind,
                        'title_eng' => $title_eng,
                        'order_no' => $order_no,
                        'notes' => $notes,
                        'small_desc_ind' => $small_desc_ind,
                        'small_desc_eng' => $small_desc_eng,
                        'long_desc_ind' => $long_desc_ind,
                        'long_desc_eng' => $long_desc_eng,
                        'string_val' => $string_val,
                        'numeric_val' => $numeric_val,
                        'active' => $active,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                } else {
                    $slug = SlugService::createSlug(Mst_global::class, 'slug', $title_eng);

                    $upd = Mst_global::where('id', '=', $id)
                        ->update([
                            'data_cat' => $data_cat,
                            'title_ind' => $title_ind,
                            'title_eng' => $title_eng,
                            'slug' => $slug,
                            'order_no' => $order_no,
                            'notes' => $notes,
                            'small_desc_ind' => $small_desc_ind,
                            'small_desc_eng' => $small_desc_eng,
                            'long_desc_ind' => $long_desc_ind,
                            'long_desc_eng' => $long_desc_eng,
                            'string_val' => $string_val,
                            'numeric_val' => $numeric_val,
                            'active' => $active,
                            'updated_by' => Auth::user()->id
                        ]);
                }
            }
        }
    }
}
