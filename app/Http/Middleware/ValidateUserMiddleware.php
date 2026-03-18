<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Mst_menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidateUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $router = app()->make('router');
        if (Auth::user()->id == 1) {
            // bebas access
            return $next($request);
        }

        $user = User::where('id', '=', Auth::user()->id)
        ->first();
        if ($user){
            if (is_null($user->email_verified_at)) {
                session()->flash('status-error', 'You are not verified user! Please, contact your administrator.');
                return redirect('dashboard');
            } else {
                // user bisa diverifikasi
                // return $next($request);
    
                $lg_ea_now = Auth::user()->email;
                $lg_ea = !is_null($request->lg_ea)?$request->lg_ea:'';
                if ($lg_ea_now<>'' && $lg_ea<>'' && $lg_ea<>$lg_ea_now){
                    // akses ke aplikasi dengan 2 user ID atau lebih dalam 1 brand browser yg sama
                    session()->flash('status-error', 'You are not verified user! Please, contact your administrator.');
                    return redirect()->to(url('sign-out?e=1'));
                }else{
                    return $next($request);
                }
            }
        }else{
            session()->flash('status-error', 'You are not verified user! Please, contact your administrator.');
            return redirect()->to(url('sign-out?e=1'));
        }

        // dd($router->getCurrentRoute()->uri);
        // $query = Mst_menu::rightJoin('mst_menu_users', 'mst_menu_users.menu_id', '=', 'mst_menus.id')
        //     ->where('mst_menus.uri', '=', $router->getCurrentRoute()->uri)
        //     ->where('mst_menus.active', '=', 'Y')
        //     ->where('mst_menu_users.user_id', '=', Auth::user()->id)
        //     ->where('mst_menu_users.user_access_read', '=', 'Y')
        //     ->first();
        // if ($query) {
        //     $user = User::where('id', '=', Auth::user()->id)->first();
        //     if (is_null($user->email_verified_at)) {
        //         session()->flash('status-error', 'You are not verified user! Please, contact your administrator.');
        //         return redirect('dashboard');
        //     } else {
        //         return $next($request);
        //     }
        // } else {
        //     $query = Mst_menu::where('mst_menus.uri', '=', $router->getCurrentRoute()->uri)
        //         ->where('mst_menus.active', '=', 'Y')
        //         ->first();
        //     if (!$query) {
        //         return $next($request);
        //     }
        //     return redirect('err-notif');
        // }
    }
}
