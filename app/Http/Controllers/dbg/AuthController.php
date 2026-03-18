<?php

namespace App\Http\Controllers\dbg;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // //registrasi user
        // $user = User::forceCreate([
        //     'name' => 'Andy DCH',
        //     'email' => 'andydch@koidigital.co.id',
        //     'password' => Hash::make('Lupa1234'),
        //     'email_verified_at' => now() //Carbon instance
        // ]);

        // event(new Registered($user));

        // Auth::login($user);
        // //registrasi user

        // validasi user 02
        $userLogin = ([
            'email' => 'andydch@koidigital.co.id',
            'password' => 'Lupa12345'
        ]);
        if (Auth::attempt($userLogin)) {
            echo 'ok brother';
        } else {
            echo 'ga ok brother';
        }
        // validasi user 02

        // tampilkan data user
        echo Auth::user() ? Auth::user()->name . '<br/>' : '-' . '<br/>';
        echo Auth::user() ? Auth::user()->email . '<br/>' : '-' . '<br/>';
        echo Auth::user() ? Auth::user()->id . '<br/>' : '-' . '<br/>';
        // tampilkan data user

        // // logout
        // Auth::logout();
        // // logout
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
    public function show(User $user)
    {
        //
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
    public function update(Request $request, User $user)
    {
        //
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
