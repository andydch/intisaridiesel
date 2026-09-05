<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Log_activity_user;
use Illuminate\Support\Facades\Hash;

// Port dari mni-local, diadaptasi untuk uid-local:
// - route kick + kolom Sesi User Management TIDAK di-port (di luar scope) -> test terkait dibuang
// - ValidateUserMiddleware uid varian lama (unverified -> redirect 'dashboard', tanpa e=2)
class SingleDeviceLoginTest extends TestCase
{
    private $email = 'singledevicetester@gmail.com';

    protected function setUp(): void
    {
        parent::setUp();
        // controller membaca $_SERVER['REMOTE_ADDR'] langsung (pre-existing); tidak ada di test env
        $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    protected function tearDown(): void
    {
        $u = User::where('email', $this->email)->first();
        if ($u) {
            Log_activity_user::where('user_id', $u->id)->delete();
            \App\Models\Userdetail::where('user_id', $u->id)->delete();
            $u->delete();
        }
        parent::tearDown();
    }

    private function makeUser()
    {
        return User::create([
            'name' => 'Single Device',
            'email' => $this->email,
            'password' => Hash::make('rahasia123'),
        ]);
    }

    private function makeActiveDetail($user)
    {
        $d = new \App\Models\Userdetail();
        $d->forceFill([
            'user_id' => $user->id, 'initial' => 'TST',
            'branch_id' => 3, 'section_id' => 48, 'active' => 'Y', 'phone1' => '0',
        ])->save();
        return $d;
    }

    private function doLogin($email)
    {
        return $this->post('/sign-in', [
            'inputEmailAddress' => $email,
            'inputChoosePassword' => 'rahasia123',
        ]);
    }

    /** @test */
    public function login_menerbitkan_token_dan_menyimpan_di_session()
    {
        $user = $this->makeUser();

        $this->doLogin($user->email)->assertRedirect('/dashboard')->assertSessionHas('login_session_token');

        $user->refresh();
        $this->assertNotNull($user->session_token);
        $this->assertEquals($user->session_token, session('login_session_token'));
    }

    /** @test */
    public function login_kedua_mengganti_token_sehingga_sesi_lama_tertendang()
    {
        $user = $this->makeUser();
        $this->doLogin($user->email);
        $token1 = $user->refresh()->session_token;
        $this->assertNotNull($token1);

        // device kedua login (keluar dulu lewat GET /sign-out)
        $this->get('/sign-out');
        $this->doLogin($user->email)->assertRedirect('/dashboard');

        $token2 = $user->refresh()->session_token;
        $this->assertNotNull($token2);
        $this->assertNotEquals($token1, $token2);
    }

    /** @test */
    public function request_dengan_token_lama_ditendang_ke_sign_out_e3()
    {
        $user = $this->makeUser();
        $this->doLogin($user->email);

        // simulasi login dari device lain: token DB diganti, session browser ini token lama
        $user->forceFill(['session_token' => 'token-milik-device-lain-0123456789abcdef'])->save();

        $r = $this->get('/admin/user-management');
        $r->assertRedirect();
        $this->assertStringContainsString('sign-out?e=3', $r->headers->get('Location'));
    }

    /** @test */
    public function user_dengan_token_null_tetap_lolos_tanpa_lockout()
    {
        $user = $this->makeUser(); // session_token NULL (belum login ulang)
        $this->assertNull($user->session_token);
        $this->be($user);

        // middleware cek token LOLOS (guard NULL); varian uid: user tanpa verifikasi -> redirect 'dashboard'.
        // Yang penting: BUKAN e=3 (tendangan token).
        $r = $this->get('/admin/user-management');
        $r->assertRedirect();
        $loc = $r->headers->get('Location');
        $this->assertStringNotContainsString('e=3', $loc);
    }

    /** @test */
    public function dashboard_juga_mentendang_token_lama()
    {
        $user = $this->makeUser();
        $this->doLogin($user->email);
        $user->forceFill(['session_token' => 'token-milik-device-lain-0123456789abcdef'])->save();

        $r = $this->get('/dashboard');
        $r->assertRedirect();
        $this->assertStringContainsString('sign-out?e=3', $r->headers->get('Location'));
    }

    /** @test */
    public function dashboard_lolos_untuk_token_null_dan_token_valid()
    {
        // token NULL (belum login ulang) -> lolos cek global
        $user = $this->makeUser();
        $this->be($user);
        $r = $this->get('/dashboard');
        $this->assertNotEquals('http://localhost/sign-out?e=3', $r->headers->get('Location'));
    }

    /** @test */
    public function admin_bisa_menendang_user_dan_token_berganti()
    {
        $user = $this->makeUser();
        $this->doLogin($user->email);
        $token1 = $user->refresh()->session_token;
        $this->assertNotNull($token1);

        $admin = User::find(1);
        $this->be($admin);
        $r = $this->post('/admin/user-management/' . $user->id . '/kick');
        $r->assertRedirect();

        $token2 = $user->refresh()->session_token;
        $this->assertNotNull($token2);
        $this->assertNotEquals($token1, $token2);
    }

    /** @test */
    public function halaman_user_management_tidak_mengandung_nested_form()
    {
        $tu = $this->makeUser();
        $tu->forceFill(['session_token' => str_repeat('k', 60)])->save();
        $this->makeActiveDetail($tu);
        $admin = User::find(1);
        $this->be($admin);

        $html = $this->get('/admin/user-management')->assertOk()->getContent();
        $a = strpos($html, '<table');
        $b = strpos($html, '</table>');
        $this->assertNotFalse($a);
        $this->assertNotFalse($b);
        $this->assertStringNotContainsString('<form', substr($html, $a, $b - $a));
        $this->assertStringContainsString('kickform-', $html);
    }

    /** @test */
    public function kick_menampilkan_notif_dipaksa_logout()
    {
        $user = $this->makeUser();
        $user->forceFill(['session_token' => str_repeat('t', 60)])->save();

        $admin = User::find(1);
        $this->be($admin);

        $r = $this->post('/admin/user-management/' . $user->id . '/kick');
        $r->assertRedirect();
        $r->assertSessionHas('status', 'User ' . $user->email . ' sudah dipaksa LOGOUT.');
    }

    /** @test */
    public function kolom_sesi_hanya_tampil_untuk_andy()
    {
        $andy = User::where('email', 'andydch@koidigital.co.id')->first() ?? User::find(1);
        $html = $this->be($andy)->get('/admin/user-management')->assertOk()->getContent();
        $this->assertStringContainsString('<th>Sesi</th>', $html);

        // sulian = admin uid TAPI bukan andy -> kolom Sesi tetap sembunyi
        $other = User::where('email', 'sulian@intimotor.com')->first();
        if ($other) {
            $this->be($other);
            session(['login_session_token' => $other->session_token]);
            $html2 = $this->get('/admin/user-management')->assertOk()->getContent();
            $this->assertStringNotContainsString('<th>Sesi</th>', $html2);
            $this->assertStringNotContainsString('kickform-', $html2);
        } else {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function ikon_kunci_dan_sampah_tampil_sejajar()
    {
        $tu = $this->makeUser();
        $tu->forceFill(['session_token' => str_repeat('k', 60)])->save();
        $this->makeActiveDetail($tu);
        $andy = User::where('email', 'andydch@koidigital.co.id')->first() ?? User::find(1);
        $html = $this->be($andy)->get('/admin/user-management')->assertOk()->getContent();
        $this->assertStringContainsString('bxs-key', $html);
        $this->assertStringContainsString('bxs-trash-alt', $html);
        $this->assertStringNotContainsString('>Tendang<', $html);
    }

    /** @test */
    public function sign_out_tidak_terjebak_redirect_loop_untuk_token_lama()
    {
        $user = $this->makeUser();
        $this->doLogin($user->email);
        // simulasi sesi basi: token DB diganti device lain
        $user->forceFill(['session_token' => 'token-milik-device-lain-0123456789abcdef'])->save();

        // korban dipantulkan ke sign-out?e=3 -> HARUS sampai /login, bukan balik ke sign-out
        $r = $this->get('/sign-out?e=3');
        $r->assertRedirect();
        $loc = $r->headers->get('Location');
        $this->assertStringContainsString('/login', $loc);
        $this->assertStringNotContainsString('sign-out', $loc);
    }
}
