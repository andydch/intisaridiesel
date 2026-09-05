<?php

namespace Tests\Unit;

use App\Imports\PiutangRowValidator;
use Tests\TestCase;

class PiutangRowValidatorV2Test extends TestCase
{
    private function baseRow(): array
    {
        return ['ABA01','02/04/2025','SBY',3500000,385000,3885000,'P','Cash','Cash',11111,'abc 12345','05/04/2025',1000,2500,35000,5000];
    }

    /** @test */
    public function kolom_A_kosong_gagal(): void
    {
        $row = $this->baseRow(); $row[0]='';
        $res = PiutangRowValidator::validate($row, 2);
        $this->assertFalse($res->ok);
    }

    /** @test */
    public function kolom_G_bukan_PN_gagal(): void
    {
        $row = $this->baseRow(); $row[6]='X';
        $res = PiutangRowValidator::validate($row, 2);
        $this->assertFalse($res->ok);
        $this->assertStringContainsString('Journal Type', $res->error);
    }

    /** @test */
    public function kolom_D_0_gagal(): void
    {
        $row = $this->baseRow(); $row[3]=0;
        $res = PiutangRowValidator::validate($row, 2);
        $this->assertFalse($res->ok);
        $this->assertStringContainsString('lebih dari 0', $res->error);
    }

    /** @test */
    public function kolom_M_bukan_numerik_gagal(): void
    {
        $row = $this->baseRow(); $row[12]='abc';
        $res = PiutangRowValidator::validate($row, 2);
        $this->assertFalse($res->ok);
        $this->assertStringContainsString('numerik', $res->error);
    }

    /** @test */
    public function valid_row_ok(): void
    {
        $row = $this->baseRow();
        $res = PiutangRowValidator::validate($row, 2);
        $this->assertTrue($res->ok);
        $this->assertSame('ABA01', $res->data->customerCode);
    }
}
