<?php

namespace Tests\Unit;

use App\Imports\HutangRowValidator;
use Tests\TestCase;

class HutangRowValidatorV2Test extends TestCase
{
    private function baseRow(): array
    {
        return ['ABJ01','02/02/2024','P','Rupiah',5000000,550000,5550000,0,0,0,'SBY','abc 007',11211,'04/02/2024','Bank','Giro','TRX-001','G-001','05/02/2024',1000,2000,3000,4000,500];
    }

    /** @test */
    public function kolom_A_kosong_gagal(): void
    {
        $row = $this->baseRow(); $row[0]='';
        $res = HutangRowValidator::validate($row, 2);
        $this->assertFalse($res->ok);
        $this->assertStringContainsString('Kode Supplier', $res->error);
    }

    /** @test */
    public function kolom_B_tanggal_invalid_gagal(): void
    {
        $row = $this->baseRow(); $row[1]='31-02-2025';
        $res = HutangRowValidator::validate($row, 2);
        $this->assertFalse($res->ok);
        $this->assertStringContainsString('RO Date', $res->error);
    }

    /** @test */
    public function kolom_C_bukan_PN_gagal(): void
    {
        $row = $this->baseRow(); $row[2]='X';
        $res = HutangRowValidator::validate($row, 2);
        $this->assertFalse($res->ok);
        $this->assertStringContainsString('Journal Type', $res->error);
    }

    /** @test */
    public function kolom_E_0_gagal(): void
    {
        $row = $this->baseRow(); $row[4]=0;
        $res = HutangRowValidator::validate($row, 2);
        $this->assertFalse($res->ok);
        $this->assertStringContainsString('lebih dari 0', $res->error);
    }

    /** @test */
    public function kolom_T_bukan_numerik_gagal(): void
    {
        $row = $this->baseRow(); $row[19]='abc';
        $res = HutangRowValidator::validate($row, 2);
        $this->assertFalse($res->ok);
        $this->assertStringContainsString('numerik', $res->error);
    }

    /** @test */
    public function valid_row_ok(): void
    {
        $row = $this->baseRow();
        $res = HutangRowValidator::validate($row, 2);
        $this->assertTrue($res->ok);
        $this->assertSame('ABJ01', $res->data->supplierCode);
    }
}
