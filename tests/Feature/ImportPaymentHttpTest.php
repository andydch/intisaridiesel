<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ImportPaymentHttpTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function user_lain_ditolak_403()
    {
        $userLain = User::where('email', '!=', 'andydch@koidigital.co.id')->first();
        $this->actingAs($userLain)->get('/import_payment')->assertStatus(403);
    }

    /** @test */
    public function andydch_dapat_halaman_import()
    {
        $andy = User::where('email', 'andydch@koidigital.co.id')->first();
        $this->actingAs($andy)->get('/import_payment')
            ->assertStatus(200)
            ->assertSee('Import Payment');
    }

    /** @test */
    public function guest_diarahkan_ke_login()
    {
        $this->get('/import_payment')->assertRedirect('/login');
    }

    /** @test */
    public function template_dapat_diunduh_oleh_andydch()
    {
        $andy = User::where('email', 'andydch@koidigital.co.id')->first();

        $response = $this->actingAs($andy)->get('/import_payment/template');
        $response->assertStatus(200);
        $this->assertStringContainsString(
            'spreadsheetml', (string) $response->headers->get('content-type'));

        // validasi isi: xlsx sah, 2 sheet, judul kolom sesuai kontrak importer
        $tmp = storage_path('app/_uji_template.xlsx');
        file_put_contents($tmp, $response->streamedContent());

        $ss = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);
        $this->assertSame('kartu-hutang', $ss->getSheet(0)->getTitle());
        $this->assertSame('Invoice No', $ss->getSheet(0)->getCell('M1')->getValue());
        $this->assertSame('kartu-piutang', $ss->getSheet(1)->getTitle());
        $this->assertSame('PPN/NON PPN', $ss->getSheet(1)->getCell('H1')->getValue());
        @unlink($tmp);
    }

    /** @test */
    public function guest_template_diarahkan_ke_login()
    {
        $this->get('/import_payment/template')->assertRedirect('/login');
    }

    /** @test */
    public function user_lain_template_ditolak_403()
    {
        $userLain = User::where('email', '!=', 'andydch@koidigital.co.id')->first();
        $this->actingAs($userLain)->get('/import_payment/template')->assertStatus(403);
    }
}
