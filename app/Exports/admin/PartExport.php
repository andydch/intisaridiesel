<?php

namespace App\Exports\admin;

use App\Models\Mst_part;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PartExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function  __construct()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 1800);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Mst_part::leftJoin('mst_globals AS partType', 'mst_parts.part_type_id', '=', 'partType.id')
            ->leftJoin('mst_globals AS partCategory', 'mst_parts.part_category_id', '=', 'partCategory.id')
            ->leftJoin('mst_globals AS partBrand', 'mst_parts.brand_id', '=', 'partBrand.id')
            ->leftJoin('mst_globals AS partWeight', 'mst_parts.weight_id', '=', 'partWeight.id')
            ->leftJoin('mst_globals AS partQty', 'mst_parts.quantity_type_id', '=', 'partQty.id')
            ->select(
                'mst_parts.id',
                'mst_parts.part_number',
                'mst_parts.part_name',
                'mst_parts.slug',
                'mst_parts.part_type_id',
                'partType.title_ind AS partTypeName',
                'mst_parts.part_category_id',
                'partCategory.title_ind AS partCategoryName',
                'mst_parts.brand_id',
                'partBrand.title_ind AS partBrandName',
                'mst_parts.weight',
                'mst_parts.weight_id',
                'partWeight.title_ind AS partWeightName',
                'mst_parts.quantity_type_id',
                'partQty.title_ind AS partQtyName',
                'mst_parts.max_stock',
                'mst_parts.safety_stock',
                'mst_parts.price_list',
                'mst_parts.final_price',
                'mst_parts.avg_cost',
                'mst_parts.initial_cost',
                'mst_parts.final_cost',
                'mst_parts.total_cost',
                'mst_parts.total_sales',
                'mst_parts.active',
                'mst_parts.created_by',
                'mst_parts.updated_by',
                'mst_parts.created_at',
                'mst_parts.updated_at'
            )
            ->orderBy('mst_parts.id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'part number',
            'part name',
            'slug',
            'part type_id',
            'part type name',
            'part category_id',
            'part category name',
            'brand id',
            'part brand name',
            'weight',
            'weight id',
            'part weight name',
            'quantity type id',
            'part qty Name',
            'max stock',
            'safety stock',
            'price list',
            'final price',
            'avg cost',
            'initial cost',
            'final cost',
            'total cost',
            'total sales',
            'active',
            'created by',
            'updated by',
            'created at',
            'updated at'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // set text style
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],

            // Styling a specific cell by coordinate.
            // 'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }

    public function columnWidths(): array
    {
        // set column width
        return [
            'A' => 10,
            'B' => 20,
            'C' => 50,
            'D' => 50,
            'E' => 10,
            'F' => 50,
            'G' => 10,
            'H' => 50,
            'I' => 10,
            'J' => 50,
            'K' => 10,
            'L' => 10,
            'M' => 50,
            'N' => 10,
            'O' => 10,
            'P' => 10,
            'Q' => 10,
            'R' => 10,
            'S' => 10,
            'T' => 10,
        ];
    }
}
