<?php

namespace App\Imports\dbg;

use App\Models\Mst_sub_district;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ImportSubDistrict implements ToModel, WithStartRow, WithCustomCsvSettings
{
    public function startRow(): int
    {
        return 1;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => '|'
        ];
    }

    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
        $id = $row[0];
        $sub_district_name = $row[1];
        $district_id = $row[4];
        $created_by = 1;
        $updated_by = 1;
        $post_code = $row[9];
        $query = Mst_sub_district::where([
            'id' => $id
        ])
            ->first();
        if ($query) {
            // $ins = Mst_sub_district::create([
            //     'sub_district_name' => $sub_district_name,
            //     'district_id' => $district_id,
            //     'created_by' => $created_by,
            //     'updated_by' => $updated_by
            // ]);

            $ins = Mst_sub_district::where([
                'id' => $id
            ])
                ->update([
                    'post_code' => $post_code,
                ]);
        }
    }
}
