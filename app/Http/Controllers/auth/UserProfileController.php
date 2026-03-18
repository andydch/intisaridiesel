<?php

namespace App\Http\Controllers\auth;

use App\Models\User;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class UserProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        ini_set('memory_limit', '1024M');

        $query = User::with('Userdetail')
            ->where('slug', '=', urldecode($slug))
            ->first();
        if ($query) {
            $userdetail = Userdetail::where('user_id','=',$query->id)
            ->first();

            $data = [
                'user' => $query,
                'userdetail' => $userdetail,
            ];
            return view('main.user-profile', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $user = User::where('slug','=',urldecode($slug))
        ->first();
        switch ($request->p) {
            case 'profile':
                Validator::make($request->all(), [
                    'fullname' => 'required|max:255',
                    'profile_pic' => 'image|file|max:1024|mimes:jpg,jpeg,png|dimensions:max_width=300,max_height=300|nullable',
                    'phone1' => 'required|max:64',
                    'date_of_birth' => 'required|date',
                    'nik' => 'required|max:255',
                ], [
                    'fullname.required' => 'The fullname field is required.',
                    'profile_pic.required' => 'The profile picture field is required.',
                    'profile_pic.image' => 'The profile picture must be an image.',
                    'phone1.required' => 'The phone field is required.',
                ])
                ->validate();

                // table Users
                $slug = SlugService::createSlug(User::class, 'slug', $request->fullname);
                $insUser = User::where('id','=',$user->id)
                ->update([
                    'name' => $request->fullname,
                    'slug' => $slug,
                ]);

                $realpath = $_SERVER['DOCUMENT_ROOT'].'/upl/employees';
                $img01 = $request->profile_pic_tmp;
                if ($request->file('profile_pic')) {
                    $extension = $request->file('profile_pic')->extension();
                    $img01 = uniqid().'_'.strtotime('now').'.'.$extension;
                    $request->file('profile_pic')->move($realpath, $img01);
                }

                // table Userdetails
                $insUserdetail = Userdetail::where('user_id','=',$user->id)
                ->update([
                    'date_of_birth' => $request->date_of_birth,
                    'profile_pic' => $img01,
                    'phone1' => $request->phone1,
                    'id_no' => $request->nik,
                    'active' => 'Y'
                ]);

                session()->flash('status', 'User Profile data has been updated successfully.');
                return redirect('user-profile/update-profile/'.urlencode($slug));

                break;
            case 'access':
                case 'profile':
                Validator::make($request->all(), [
                    'pwd' => 'required|max:255',
                    'c_pwd' => 'required|max:255|same:pwd',
                ], [
                    'pwd.required' => 'The password field is required.',
                    'c_pwd.required' => 'The confirmation password field is required.',
                    'c_pwd.same' => 'The confirmation password and password must match.',
                ])
                ->validate();

                // table Users
                $insUser = User::where('id','=',$user->id)
                ->update([
                    'password' => Hash::make($request->pwd),
                ]);

                session()->flash('status', 'User Profile data has been updated successfully.');
                return redirect('user-profile/update-profile/'.urlencode($user->slug));

                break;
            default:
                session()->flash('status-error', 'No data has been updated.');
                return redirect('sign-out');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
