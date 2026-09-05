<?php

namespace App\Http\Controllers\adm;

use App\Models\User;
use App\Models\Mst_menu;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Mst_menu_user;
use App\Rules\UserEmailRegUnique;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    protected $title = 'User Management';
    protected $folder = 'user-management';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // super user & pak sulian+pak yadi+maeger only
        if (Auth::user()->id != 1 && 
            Auth::user()->email!='sulian@intimotor.com' && 
            Auth::user()->email!='sujayadi.office@gmail.com' && 
            Auth::user()->email!='maeger@koidigital.co.id') {
            return redirect('err-notif');
        }

        if (Auth::user()->id == 1 || 
            Auth::user()->email=='sulian@intimotor.com' || 
            Auth::user()->email=='sujayadi.office@gmail.com' || 
            Auth::user()->email=='maeger@koidigital.co.id') {
            $query = User::leftJoin('userdetails','users.id','=','userdetails.user_id')
            ->leftJoin('mst_globals','userdetails.section_id','=','mst_globals.id')
            ->leftJoin('mst_branches','userdetails.branch_id','=','mst_branches.id')
            ->select(
                'users.name',
                'users.slug',
                'users.email',
                'userdetails.initial',
                'userdetails.phone1',
                'userdetails.branch_id',
                'userdetails.active',
                'mst_globals.title_ind as section_name',
                'mst_branches.name as branch_name',
            )
            ->when(Auth::user()->id!=1, function($q1){
                $q1->where('users.id', '<>', 1);
            })
            ->orderBy('userdetails.initial', 'ASC')
            ->orderBy('users.name', 'ASC')
            ->get();
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'users' => $query
            ];

            return view('adm.user-management.index', $data);
        }else{
            return redirect('err-notif');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // super user & pak sulian only
        if (Auth::user()->id != 1 && 
            Auth::user()->email!='sulian@intimotor.com' && 
            Auth::user()->email!='sujayadi.office@gmail.com' && 
            Auth::user()->email!='maeger@koidigital.co.id') {
            return redirect('err-notif');
        }

        if (Auth::user()->id == 1 || Auth::user()->email=='sulian@intimotor.com' || 
            Auth::user()->email=='sujayadi.office@gmail.com' || 
            Auth::user()->email=='maeger@koidigital.co.id') {
            $sections = Mst_global::where([
                'data_cat' => 'employee-section',
                'active' => 'Y'
            ])
            ->orderBy('title_ind','ASC')
            ->get();
            $branches = Mst_branch::where([
                'active' => 'Y'
            ])
            ->orderBy('name','ASC')
            ->get();
            $menus = Mst_menu::where('active','=','Y')
            ->orderBy('name','ASC')
            ->get();
            $menusCount = Mst_menu::where('active','=','Y')
            ->count();
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'sections' => $sections,
                'branches' => $branches,
                'menus' => $menus,
                'menusCount' => $menusCount,
            ];
            return view('adm.user-management.create', $data);
        }else{
            return redirect('err-notif');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // super user & pak sulian only
        if (Auth::user()->id != 1 && 
            Auth::user()->email!='sulian@intimotor.com' && 
            Auth::user()->email!='sujayadi.office@gmail.com' && 
            Auth::user()->email!='maeger@koidigital.co.id') {
            return redirect('err-notif');
        }

        if (Auth::user()->id == 1 || Auth::user()->email=='sulian@intimotor.com' || 
            Auth::user()->email=='sujayadi.office@gmail.com' || 
            Auth::user()->email=='maeger@koidigital.co.id') {
            Validator::make($request->all(), [
                'fullname' => 'required|max:255',
                'initial' => 'required|max:3',
                'profile_pic' => 'image|file|max:1024|mimes:jpg,jpeg,png|dimensions:max_width=300,max_height=300|nullable',
                'signage_pic' => 'image|file|max:1024|mimes:jpg,jpeg,png|dimensions:max_width=400,max_height=300',
                'phone1' => 'required|max:64',
                'date_of_birth' => 'required|date',
                'nik' => 'max:255|nullable',
                'uname' => 'required|max:255|email:rfc|unique:App\Models\User,email',
                'pwd' => 'required|max:255',
                'c_pwd' => 'required|max:255|same:pwd',
                'section_id' => 'required|numeric',
                'branch_id' => 'required|numeric',
            ], [
                'fullname.required' => 'The fullname field is required.',
                'profile_pic.required' => 'The profile picture field is required.',
                'profile_pic.image' => 'The profile picture must be an image.',
                'signage_pic.required' => 'The signage picture field is required.',
                'signage_pic.image' => 'The signage picture must be an image.',
                'phone1.required' => 'The phone field is required.',
                'uname.required' => 'The username field is required.',
                'uname.email' => 'The username must be a valid email address.',
                'uname.unique' => 'The username has already been taken.',
                'pwd.required' => 'The password field is required.',
                'c_pwd.required' => 'The confirmation password field is required.',
                'c_pwd.same' => 'The confirmation password and password must match.',
                'section_id.required' => 'Please select a valid section.',
                'branch_id.required' => 'Please select a valid branch.',
            ])
            ->validate();

            // table Users
            $insUser = User::create([
                'name' => $request->fullname,
                'email' => $request->uname,
                'password' => Hash::make($request->pwd),
                'email_verified_at' => now() //Carbon instance
            ]);

            // id yang baru terbentuk
            $maxId = $insUser->id;
            // $maxId = User::max('id');

            // upload profile picture
            $realpath = $_SERVER['DOCUMENT_ROOT'].'/upl/employees';
            $img01 = null;
            if ($request->file('profile_pic')) {
                $extension = $request->file('profile_pic')->extension();
                $img01 = uniqid().'_'.strtotime('now').'.'.$extension;
                $request->file('profile_pic')->move($realpath, $img01);
            }

            // upload signage picture
            $img02 = null;
            if ($request->file('signage_pic')) {
                $extension = $request->file('signage_pic')->extension();
                $img02 = uniqid().'_'.strtotime('now').'.'.$extension;
                $request->file('signage_pic')->move($realpath, $img02);
            }

            $isSalesman = 'N';
            if($request->is_salesman=='on'){$isSalesman = 'Y';}
            $isDirector = 'N';
            if($request->is_director=='on'){$isDirector = 'Y';}
            $isBranchHead = 'N';
            if($request->is_branch_head=='on'){$isBranchHead = 'Y';}

            // table Userdetails
            $insUserdetail = Userdetail::create([
                'user_id' => $maxId,
                'initial' => $request->initial,
                'date_of_birth' => $request->date_of_birth,
                // 'address',
                // 'country_id',
                // 'province_id',
                // 'city_id',
                // 'district_id',
                // 'sub_district_id',
                'position' => null,
                'section_id' => $request->section_id,
                'profile_pic' => $img01,
                'signage_pic' => $img02,
                'phone1' => $request->phone1,
                // 'phone2',
                'id_no' => $request->nik,
                'branch_id' => $request->branch_id,
                'is_salesman' => $isSalesman,
                'is_director' => $isDirector,
                'is_branch_head' => $isBranchHead,
                'active' => 'Y'
            ]);

            $menuCount = Mst_menu::where('active','=','Y')
            ->count();
            for($i=0;$i<$menuCount;$i++){
                $isMenuActive = 'N';
                if($request['menuCheck'.$i]=='on'){$isMenuActive = 'Y';}

                $insMenu = Mst_menu_user::create([
                    'menu_id' => $request['menu_id_'.$i],
                    'user_id' => $maxId,
                    'user_access_read' => $isMenuActive,
                    'user_access_update' => $isMenuActive,
                    'user_access_delete' => $isMenuActive,
                    'active' => 'Y',
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id
                ]);
            }

            event(new Registered($insUser));
            // return redirect('/admin/user-management');
            session()->flash('status', 'New data has been inserted successfully.');
            return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder);
        }else{
            return redirect('err-notif');
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        // super user & pak sulian only
        if (Auth::user()->id != 1 && 
            Auth::user()->email!='sulian@intimotor.com' && 
            Auth::user()->email!='sujayadi.office@gmail.com' && 
            Auth::user()->email!='maeger@koidigital.co.id') {
            return redirect('err-notif');
        }

        if (Auth::user()->id == 1 || Auth::user()->email=='sulian@intimotor.com' || 
            Auth::user()->email=='sujayadi.office@gmail.com' || 
            Auth::user()->email=='maeger@koidigital.co.id') {
            $user = User::where('slug','=',urldecode($slug))
            ->first();
            if($user){
                $userdetail = Userdetail::where('user_id','=',$user->id)
                ->first();
                $sections = Mst_global::where([
                    'data_cat' => 'employee-section',
                    'active' => 'Y'
                ])
                ->orderBy('title_ind','ASC')
                ->get();
                $branches = Mst_branch::where([
                    'active' => 'Y'
                ])
                ->orderBy('name','ASC')
                ->get();
                $menus = Mst_menu::where('active','=','Y')
                ->orderBy('name','ASC')
                ->get();
                $menusCount = Mst_menu::where('active','=','Y')
                ->count();
                $data = [
                    'title' => $this->title,
                    'folder' => $this->folder,
                    'sections' => $sections,
                    'branches' => $branches,
                    'menus' => $menus,
                    'menusCount' => $menusCount,
                    'user' => $user,
                    'userdetail' => $userdetail,
                ];
                return view('adm.user-management.show', $data);
            }else{
                $data = [
                    'errNotif' => 'The data you are looking for is not found'
                ];
                return view('error-notif.not-found-notif', $data);
            }
        }else{
            return redirect('err-notif');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        // super user & pak sulian only
        if (Auth::user()->id != 1 && 
            Auth::user()->email!='sulian@intimotor.com' && 
            Auth::user()->email!='sujayadi.office@gmail.com' && 
            Auth::user()->email!='maeger@koidigital.co.id') {
            return redirect('err-notif');
        }

        if (Auth::user()->id == 1 || Auth::user()->email=='sulian@intimotor.com' || 
            Auth::user()->email=='sujayadi.office@gmail.com' || 
            Auth::user()->email=='maeger@koidigital.co.id') {
            $user = User::where('slug','=',urldecode($slug))
            ->first();
            if($user){
                $userdetail = Userdetail::where('user_id','=',$user->id)
                ->first();
                $sections = Mst_global::where([
                    'data_cat' => 'employee-section',
                    'active' => 'Y'
                ])
                ->orderBy('title_ind','ASC')
                ->get();
                $branches = Mst_branch::where([
                    'active' => 'Y'
                ])
                ->orderBy('name','ASC')
                ->get();
                $menus = Mst_menu::where('active','=','Y')
                ->orderBy('name','ASC')
                ->get();
                $menusCount = Mst_menu::where('active','=','Y')
                ->count();
                $data = [
                    'title' => $this->title,
                    'folder' => $this->folder,
                    'sections' => $sections,
                    'branches' => $branches,
                    'menus' => $menus,
                    'menusCount' => $menusCount,
                    'user' => $user,
                    'userdetail' => $userdetail,
                ];
                return view('adm.user-management.edit', $data);
            }else{
                $data = [
                    'errNotif' => 'The data you are looking for is not found'
                ];
                return view('error-notif.not-found-notif', $data);
            }
        }else{
            return redirect('err-notif');
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        // super user & pak sulian only
        if (Auth::user()->id != 1 && 
            Auth::user()->email!='sulian@intimotor.com' && 
            Auth::user()->email!='sujayadi.office@gmail.com' && 
            Auth::user()->email!='maeger@koidigital.co.id') {
            return redirect('err-notif');
        }

        if (Auth::user()->id == 1 || Auth::user()->email=='sulian@intimotor.com' || 
            Auth::user()->email=='sujayadi.office@gmail.com' || 
            Auth::user()->email=='maeger@koidigital.co.id') {
            $user = User::where('slug','=',urldecode($slug))
            ->first();
            Validator::make($request->all(), [
                'fullname' => 'required|max:255',
                'initial' => 'required|max:3',
                'profile_pic' => 'image|file|max:1024|mimes:jpg,jpeg,png|dimensions:max_width=300,max_height=300|nullable',
                'signage_pic' => 'image|file|max:1024|mimes:jpg,jpeg,png|dimensions:max_width=400,max_height=300',
                'phone1' => 'required|max:64',
                'date_of_birth' => 'required|date',
                'nik' => 'max:255|nullable',
                // 'uname' => 'required|max:255|email:rfc|unique:App\Models\User,email',
                'uname' => ['required','max:255','email:rfc',new UserEmailRegUnique($request->uname)],
                'pwd' => 'max:255',
                // 'pwd' => 'required|max:255',
                'c_pwd' => 'required_with:pwd|max:255|same:pwd',
                'section_id' => 'required|numeric',
                'branch_id' => 'required|numeric',
            ], [
                'fullname.required' => 'The fullname field is required.',
                // 'profile_pic.required' => 'The profile picture field is required.',
                'profile_pic.image' => 'The profile picture must be an image.',
                'signage_pic.required' => 'The signage picture field is required.',
                'signage_pic.image' => 'The signage picture must be an image.',
                'phone1.required' => 'The phone field is required.',
                'uname.required' => 'The username field is required.',
                'uname.email' => 'The username must be a valid email address.',
                'uname.unique' => 'The username has already been taken.',
                // 'pwd.required' => 'The password field is required.',
                'c_pwd.required_with' => 'The confirmation password field is required with password field.',
                'c_pwd.same' => 'The confirmation password and password must match.',
                'section_id.required' => 'Please select a valid section.',
                'branch_id.required' => 'Please select a valid branch.',
            ])
            ->validate();

            // table Users
            if($request->pwd!=''){
                $insUser = User::where('id','=',$user->id)
                ->update([
                    'name' => $request->fullname,
                    'email' => $request->uname,
                    'password' => Hash::make($request->pwd),
                ]);
            }else{
                $insUser = User::where('id','=',$user->id)
                ->update([
                    'name' => $request->fullname,
                    'email' => $request->uname,
                    // 'password' => Hash::make($request->pwd),
                ]);
            }

            // upload profile picture
            $realpath = $_SERVER['DOCUMENT_ROOT'].'/upl/employees';
            $img01 = $request->profile_pic_tmp;
            if ($request->file('profile_pic')) {
                $extension = $request->file('profile_pic')->extension();
                $img01 = uniqid().'_'.strtotime('now').'.'.$extension;
                $request->file('profile_pic')->move($realpath, $img01);
            }

            // upload signage picture
            $img02 = $request->signage_pic_tmp;
            if ($request->file('signage_pic')) {
                $extension = $request->file('signage_pic')->extension();
                $img02 = uniqid().'_'.strtotime('now').'.'.$extension;
                $request->file('signage_pic')->move($realpath, $img02);
            }

            $isSalesman = 'N';
            if($request->is_salesman=='on'){$isSalesman = 'Y';}
            $isDirector = 'N';
            if($request->is_director=='on'){$isDirector = 'Y';}
            $isBranchHead = 'N';
            if($request->is_branch_head=='on'){$isBranchHead = 'Y';}

            // table Userdetails
            $insUserdetail = Userdetail::where('user_id','=',$user->id)
            ->update([
                'date_of_birth' => $request->date_of_birth,
                'initial' => $request->initial,
                // 'address',
                // 'country_id',
                // 'province_id',
                // 'city_id',
                // 'district_id',
                // 'sub_district_id',
                'position' => null,
                'section_id' => $request->section_id,
                'profile_pic' => $img01,
                'signage_pic' => $img02,
                'phone1' => $request->phone1,
                // 'phone2',
                'id_no' => $request->nik,
                'branch_id' => $request->branch_id,
                'is_salesman' => $isSalesman,
                'is_director' => $isDirector,
                'is_branch_head' => $isBranchHead,
                'active' => 'Y'
            ]);

            $menuCount = Mst_menu::where('active','=','Y')
            ->count();
            for($i=0;$i<$menuCount;$i++){
                $isMenuActive = 'N';
                if($request['menuCheck'.$i]=='on'){$isMenuActive = 'Y';}

                $cekMenu = Mst_menu_user::where([
                    'menu_id' => $request['menu_id_'.$i],
                    'user_id' => $user->id,
                ])
                ->first();
                if($cekMenu){
                    // update
                    $updMenu = Mst_menu_user::where([
                        'menu_id' => $request['menu_id_'.$i],
                        'user_id' => $user->id,
                    ])
                    ->update([
                        'user_access_read' => $isMenuActive,
                        'user_access_update' => $isMenuActive,
                        'user_access_delete' => $isMenuActive,
                        'active' => 'Y',
                        'updated_by' => Auth::user()->id
                    ]);
                }else{
                    $insMenu = Mst_menu_user::create([
                        'menu_id' => $request['menu_id_'.$i],
                        'user_id' => $user->id,
                        'user_access_read' => $isMenuActive,
                        'user_access_update' => $isMenuActive,
                        'user_access_delete' => $isMenuActive,
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                }
            }

            // event(new Registered($insUser));
            session()->flash('status', 'Existing data has been updated successfully.');
            return redirect(ENV('ADMIN_FOLDER_NAME').'/'.$this->folder);
        }else{
            return redirect('err-notif');
        }

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
