<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Mst_part;
use Illuminate\Http\Request;

class PartsJsonController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        ini_set('memory_limit', '64M');
        ini_set('max_execution_time', 1800);
        
        $parts = Mst_part::when(request()->has('pnm') && request()->pnm<>'', function($q) {
            $q->where('part_number','LIKE','%'.request()->pnm.'%')
            ->orWhere('part_name','LIKE','%'.request()->pnm.'%');
        })
        ->where('active','=','Y')
        ->orderBy('part_number','ASC')
        ->get();

        $data = [
            'version' => '1',
            // 'date' => '2024-01-30 10:56:24',
            'title' => 'Parts Data',
            'home_page_url' => url('/parts-json'),
            'feed_url' => url('/parts-json'),
            'icon' => '',
            'favicon' => '',
            'items' => [],
        ];

        foreach($parts as $key => $part){
            $partName = str_replace('"','\"',$part->part_name);
            $partName = str_replace("'","\'",$partName);

            $partNumber = $part->part_number;
            if(strlen($partNumber)<11){
                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
            }else{
                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
            }

            $data['items'][$key] = [
                'id' => $part->id,
                'part_name' => $partNumber.' : '.$partName,
            ];
        }

        return $data;
    }
}
