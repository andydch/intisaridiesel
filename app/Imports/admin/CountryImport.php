<?php

namespace App\Imports\admin;

use App\Models\Mst_country;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;

class CountryImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $id = $row[0];
            $country_name = $row[1];
            $active = $row[2];
            if (strtolower($id) != 'id') {
                $query = Mst_country::where('id', '=', $id)->first();
                if (!$query) {
                    $ins = Mst_country::create([
                        'country_name' => $country_name,
                        'active' => $active,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                } else {
                    $upd = Mst_country::where('id', '=', $id)
                        ->update([
                            'country_name' => $country_name,
                            'active' => $active,
                            'updated_by' => Auth::user()->id
                        ]);
                }
            }
        }
    }
}
