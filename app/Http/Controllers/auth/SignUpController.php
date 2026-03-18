<?php

namespace App\Http\Controllers\auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SignUpController extends Controller
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
        Validator::make($request->all(), [
            'inputFirstName' => 'required|max:155',
            'inputLastName' => 'required|max:100',
            'inputEmailAddress' => 'required|max:255|email:rfc|unique:App\Models\User,email',
            'inputChoosePassword' => 'required|max:100',
            'inputChooseConfirmPassword' => 'required|max:100|same:inputChoosePassword',
            // 'TermAndCondition' => 'accepted'
        ])->validate();

        // cek apakah registrasi pertama
        $count = User::count();
        if ($count == 0) {
            // user pertama, otomatis sebagai superuser
            $user = User::create([
                'name' => $request->inputFirstName . ' ' . $request->inputLastName,
                'email' => $request->inputEmailAddress,
                'password' => Hash::make($request->inputChoosePassword),
                'email_verified_at' => now() //Carbon instance
            ]);
        } else {
            $user = User::create([
                'name' => $request->inputFirstName . ' ' . $request->inputLastName,
                'email' => $request->inputEmailAddress,
                'password' => Hash::make($request->inputChoosePassword),
            ]);
        }
        event(new Registered($user));
        Auth::login($user); // login otomatis
        return redirect('/dashboard');
        // cek apakah registrasi pertama
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
