<?php

namespace App\Http\Controllers\dbg;

use App\Http\Controllers\Controller;
use App\Models\Mst_part;
use Illuminate\Http\Request;

class JsonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $parts = Mst_part::when(request()->has('pnm') && request()->pnm<>'', function($q) {
            $q->where('part_number','LIKE','%'.request()->pnm.'%')
            ->orWhere('part_name','LIKE','%'.request()->pnm.'%');
        })
        ->orderBy('part_number','ASC')
        ->limit(50)
        ->get();

        $data = [
            'version' => '1',
            'title' => 'Test JSON',
            'home_page_url' => url('/dbg/gen-json'),
            'feed_url' => url('/dbg/gen-json'),
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
                // 'part_number' => $part->part_number,
                'part_name' => $partNumber.' : '.$partName,
            ];
        }

        return $data;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
