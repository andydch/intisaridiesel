<?php

namespace App\Http\Controllers\adm;

use App\Models\User;
use App\Models\Mst_menu;
use Illuminate\Http\Request;
use App\Models\Mst_menu_user;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Validator;

class MenuAccessController extends Controller
{
    public function  __construct()
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // super user only
        if (Auth::user()->id != 1) {
            return redirect('err-notif');
        }

        $query = User::where('id', '<>', 1)
        ->orderBy('name', 'ASC')
        ->get();
        $data = [
            'users' => $query
        ];

        return view('adm.menu.index', $data);
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
     * @param  \App\Models\Mst_province  $Mst_province
     * @return \Illuminate\Http\Response
     */
    public function show(Mst_province $Mst_province)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_province  $Mst_province
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // super user only
        if (Auth::user()->id != 1) {
            return redirect('err-notif');
        }

        $queryIn = Mst_menu::with(['menuUser'])
            ->select('id', 'name')
            ->whereIn('id', DB::table('mst_menu_users')
                ->where('user_id', $id)
                ->pluck('menu_id')
                ->toArray())
            ->orderBy('order_no', 'ASC')
            ->get();
        $queryNotIn = Mst_menu::select('id', 'name')
            ->whereNotIn('id', DB::table('mst_menu_users')
                ->where('user_id', $id)
                ->pluck('menu_id')
                ->toArray())
            ->orderBy('order_no', 'ASC')
            ->get();
        $queryUser = User::where('id', '=', $id)->first();
        $data = [
            'user_id' => $id,
            'queryIn' => $queryIn,
            'queryNotIn' => $queryNotIn
        ];
        return view('adm.menu.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mst_province  $Mst_province
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        for ($i = 0; $i < $request->inputTot; $i++) {
            $menuId = $request['menu_id' . ($i + 1)];

            $active = 'N';
            if ($request['menuCheck' . ($i + 1)] == 'on') {
                $active = 'Y';
            }

            $query = Mst_menu_user::where([
                'menu_id' => $menuId,
                'user_id' => $id
            ])
                ->first();
            if (!$query) {
                $ins = Mst_menu_user::create([
                    'menu_id' => $menuId,
                    'user_id' => $id,
                    'user_access_read' => $active,
                    'user_access_update' => $active,
                    'user_access_delete' => $active,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id
                ]);
            } else {
                $ins = Mst_menu_user::where([
                    'menu_id' => $menuId,
                    'user_id' => $id
                ])
                    ->update([
                        'menu_id' => $menuId,
                        'user_id' => $id,
                        'user_access_read' => $active,
                        'user_access_update' => $active,
                        'user_access_delete' => $active,
                        'updated_by' => Auth::user()->id
                    ]);
            }
        }

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('ADMIN_FOLDER_NAME') . '/menu');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_province  $Mst_province
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_province $Mst_province)
    {
        //
    }
}
