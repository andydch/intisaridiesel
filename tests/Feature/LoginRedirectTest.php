<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class LoginRedirectTest extends TestCase
{
    /** @test */
    public function tamu_bisa_membuka_halaman_login()
    {
        $r = $this->get('/login');
        $r->assertOk();
        $r->assertSee('inputEmailAddress', false);
    }

    /** @test */
    public function user_login_yang_membuka_login_memantul_ke_dashboard()
    {
        $user = User::where('email', 'andydch@koidigital.co.id')->first() ?? User::first();
        $this->be($user);

        $r = $this->get('/login');
        $r->assertRedirect('/dashboard');
    }
}
