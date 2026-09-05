<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class SysMenuTest extends TestCase
{
    /** @test */
    public function tamu_ditolak_ke_login()
    {
        $this->get('/_sys/migrate')->assertRedirect();
        $this->assertStringContainsString('login', $this->get('/_sys/migrate')->headers->get('Location'));
        $this->get('/_sys/optimize-clear')->assertRedirect();
    }

    /** @test */
    public function andy_bisa_jalankan_migrate()
    {
        $andy = User::where('email', 'andydch@koidigital.co.id')->first() ?? User::find(1);
        $r = $this->be($andy)->get('/_sys/migrate');
        $r->assertOk();
        $r->assertSee('MNI Migrate', false);
    }

    /** @test */
    public function andy_bisa_jalankan_optimize_clear()
    {
        $andy = User::where('email', 'andydch@koidigital.co.id')->first() ?? User::find(1);
        $r = $this->be($andy)->get('/_sys/optimize-clear');
        $r->assertOk();
        $r->assertSee('MNI Optimize Clear', false);
    }

    /** @test */
    public function user_lain_ditolak_403()
    {
        $user = User::where('id', '<>', 1)->first();
        $this->be($user);
        $this->get('/_sys/migrate')->assertForbidden();
        $this->get('/_sys/optimize-clear')->assertForbidden();
    }

    /** @test */
    public function mode_json_mengembalikan_status_ok()
    {
        $andy = User::where('email', 'andydch@koidigital.co.id')->first() ?? User::find(1);
        $r = $this->be($andy)->get('/_sys/migrate?json=1');
        $r->assertOk();
        $this->assertEquals('ok', $r->json('status'));
    }

    /** @test */
    public function oth_migrate_tertutup_untuk_tamu_dan_non_andy()
    {
        $this->get('/oth/migrate')->assertForbidden();
        $user = User::where('id', '<>', 1)->first();
        $this->be($user);
        $this->get('/oth/migrate')->assertForbidden();
        // jalur andy tetap terbuka
        $andy = User::where('email', 'andydch@koidigital.co.id')->first() ?? User::find(1);
        $this->be($andy);
        $this->get('/oth/migrate')->assertOk();
    }

    /** @test */
    public function sidebar_menampilkan_menu_sys_untuk_andy()
    {
        $andy = User::where('email', 'andydch@koidigital.co.id')->first() ?? User::find(1);
        $html = $this->be($andy)->get('/dashboard')->assertOk()->getContent();
        $this->assertStringContainsString('/_sys/migrate', $html);
        $this->assertStringContainsString('/_sys/optimize-clear', $html);
    }

    /** @test */
    public function sidebar_menyembunyikan_menu_sys_untuk_non_andy()
    {
        $other = User::where('email', 'maeger@koidigital.co.id')->first();
        if (!$other) { $this->assertTrue(true); return; }
        // selaraskan session agar lolos cek single-device global (bukan yang diuji di sini)
        $this->be($other);
        session(['login_session_token' => $other->session_token]);
        $html = $this->get('/dashboard')->assertOk()->getContent();
        $this->assertStringNotContainsString('/_sys/migrate', $html);
        $this->assertStringNotContainsString('/_sys/optimize-clear', $html);
    }
}
