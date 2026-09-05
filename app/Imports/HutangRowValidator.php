<?php

namespace App\Imports;

use Carbon\Carbon;

class HutangRowValidator
{
    /**
     * Validasi v2 HUTANG (A-X, 24 col). Hapus validasi lama — hanya v2.
     * Kolom: A Kode Supplier(0), B RO Date(1), C Journal Type(2), D Currency(3), E DPP(4), F PPN(5), G TOT(6), H DPP FOB(7), I PPN FOB(8), J TOT FOB(9), K INT Branch(10), L Inv.No(11), M CTS/COA(12), N CTS PLAN DATE(13), O METODE BAYAR(14), P BAYAR VIA(15), Q KODE TRANSAK(16), R NO GIRO(17), S JOURNAL DATE(18), T ADM BANK(19), U BIAYA ASS(20), V BIAYA KIRIM(21), W BIAYA LAIN(22), X DISC(23)
     */
    public static function validate(array $row, int $barisKe): object
    {
        $kode = trim((string) ($row[0] ?? '')); // A
        if ($kode === '' && self::semuaKosong($row)) {
            return (object) ['ok' => false, 'skip' => true, 'error' => "Baris {$barisKe}: kosong"];
        }
        if ($kode === '') return self::err($barisKe, 'Kode Supplier (A) wajib terisi');

        // Excel reads 0 as null — normalisasi PPN/FOB 0 agar tidak dianggap kosong
        foreach ([5,8] as $idx) { if (!isset($row[$idx]) || $row[$idx] === null || $row[$idx] === '') $row[$idx] = 0; }
        foreach ([7,9] as $idx) { if (!isset($row[$idx]) || $row[$idx] === null) $row[$idx] = $row[$idx] ?? 0; }

        // Validasi B-Q harus terisi (B=1 s/d Q=16)
        $wajibB_Q = [
            1 => 'B(RO Date)', 2 => 'C(Journal Type)', 3 => 'D(Currency)', 4 => 'E(DPP)', 5 => 'F(PPN)', 6 => 'G(TOT)',
            7 => 'H(DPP FOB)', 8 => 'I(PPN FOB)', 9 => 'J(TOT FOB)', 10 => 'K(INT Branch)', 11 => 'L(Inv. No)',
            12 => 'M(CTS/COA)', 13 => 'N(CTS Plan Date)', 14 => 'O(Metode Bayar)', 15 => 'P(Bayar Via)', 16 => 'Q(Kode Transak)'
        ];
        foreach ($wajibB_Q as $idx => $label) {
            if (trim((string) ($row[$idx] ?? '')) === '') {
                return self::err($barisKe, "Kolom $label harus terisi");
            }
        }

        // Validasi B,N,S tanggal DD/MM/YYYY, S wajib diisi (S=18)
        $tglB = self::parseTanggal($row[1] ?? null);
        if (! $tglB) return self::err($barisKe, 'RO Date (B) harus DD/MM/YYYY');
        $tglN = self::parseTanggal($row[13] ?? null);
        if (! $tglN) return self::err($barisKe, 'CTS Plan Date (N) harus DD/MM/YYYY');
        if (trim((string) ($row[18] ?? '')) === '') return self::err($barisKe, 'Journal Date (S) wajib diisi');
        $tglS = self::parseTanggal($row[18] ?? null);
        if (! $tglS) return self::err($barisKe, 'Journal Date (S) harus DD/MM/YYYY');

        // C hanya P/N
        $jt = strtoupper(trim((string) ($row[2] ?? '')));
        if (! in_array($jt, ['P','N'], true)) return self::err($barisKe, 'Journal Type (C) hanya boleh P atau N');

        // E-J numerik, E & G >0, F & I boleh 0, H,J boleh 0? Spec: E-J numerik, E & G >0, F & I boleh 0, H,J?
        // Interpretasi: E(DPP) >0, F(PPN) >=0, G(TOT) >0, H-J boleh 0 (FOB boleh 0)
        $numericChecks = [
            4 => ['label'=>'E(DPP)', 'mustGT0'=>true],
            5 => ['label'=>'F(PPN)', 'mustGT0'=>false],
            6 => ['label'=>'G(TOT)', 'mustGT0'=>true],
            7 => ['label'=>'H(DPP FOB)', 'mustGT0'=>false],
            8 => ['label'=>'I(PPN FOB)', 'mustGT0'=>false],
            9 => ['label'=>'J(TOT FOB)', 'mustGT0'=>false],
        ];
        $angka = [];
        foreach ($numericChecks as $idx => $cfg) {
            $v = self::toFloat($row[$idx] ?? null);
            if ($v === null) return self::err($barisKe, "Kolom {$cfg['label']} harus numerik");
            if ($cfg['mustGT0'] && $v <= 0) return self::err($barisKe, "Kolom {$cfg['label']} harus lebih dari 0");
            $angka[$idx] = $v;
        }
        if (abs($angka[6] - ($angka[4] + $angka[5])) > 0.01) {
            return self::err($barisKe, 'Kolom G(TOT) harus = E(DPP)+F(PPN) (selisih >0.01)');
        }
        if (abs($angka[9] - ($angka[7] + $angka[8])) > 0.01) {
            return self::err($barisKe, 'Kolom J(TOT FOB) harus = H(DPP FOB)+I(PPN FOB) (selisih >0.01)');
        }

        // K inisial cabang harus sesuai mst_branches (cek tidak kosong di sini, lookup di pre-flight)
        $branch = trim((string) ($row[10] ?? ''));
        if ($branch === '') return self::err($barisKe, 'INT Branch (K) wajib terisi');

        // O metode bayar harus sesuai env METHOD_BAYAR_SUPPLIER_NAME
        $metode = trim((string) ($row[14] ?? ''));
        $allowed = array_map('trim', explode('|', (string) env('METHOD_BAYAR_SUPPLIER_NAME', 'Cash|Bank|Advance Payment')));
        // case-insensitive: Cash == CASH == cash
        $allowedLower = array_map('strtolower', $allowed);
        if (!in_array(strtolower($metode), $allowedLower, true)) return self::err($barisKe, 'Metode Bayar (O) harus '.implode('|',$allowed));

        // T-X jika diisi harus numerik, boleh 0
        foreach ([19=>'T(ADM BANK)',20=>'U(BIAYA ASS)',21=>'V(BIAYA KIRIM)',22=>'W(BIAYA LAIN)',23=>'X(DISC)'] as $idx=>$label) {
            $raw = trim((string) ($row[$idx] ?? ''));
            if ($raw !== '' && self::toFloat($raw) === null) return self::err($barisKe, "Kolom $label jika diisi harus numerik");
        }

        // Normalisasi currency D
        $curRaw = strtoupper(trim((string) ($row[3] ?? '')));
        if ($curRaw === 'RUPIAH' || $curRaw === 'RP') $cur = 'RP';
        elseif (str_contains($curRaw,'DOLLAR') || $curRaw==='USD') $cur='USD';
        else $cur = $curRaw;
        if (!in_array($cur, ['RP','USD'], true)) return self::err($barisKe, 'Currency (D) harus Rupiah atau Dollar');

        // Sukses
        return (object) ['ok'=>true, 'skip'=>false, 'data'=>(object)[
            'supplierCode'=>$kode,
            'tanggal'=>$tglB,
            'journalType'=>$jt,
            'currencyKode'=>$cur,
            'dppF'=>$angka[4],
            'ppnG'=>$angka[5],
            'dppPpnH'=>$angka[6],
            'dppI'=>$angka[7],
            'ppnJ'=>$angka[8],
            'dppPpnK'=>$angka[9],
            'branchName'=>$branch,
            'invoiceNo'=>trim((string)($row[11] ?? '')),
            'coaCode'=>self::normCoa($row[12] ?? null),
            'planDate'=>$tglN,
            'kodeTrans'=>trim((string)($row[16] ?? '')),
            'metodeBayar'=>$metode,
            'bayarVia'=>trim((string)($row[15] ?? '')),
            'noGiro'=>trim((string)($row[17] ?? '')),
            'jurnalDate'=> $tglS,
            'adminBank'=> self::toFloat($row[19] ?? 0) ?? 0,
            'biayaAss'=> self::toFloat($row[20] ?? 0) ?? 0,
            'biayaKirim'=> self::toFloat($row[21] ?? 0) ?? 0,
            'biayaLain'=> self::toFloat($row[22] ?? 0) ?? 0,
            'discount'=> self::toFloat($row[23] ?? 0) ?? 0,
            'rawRow'=>$row,
        ]];
    }

    private static function err(int $n, string $m): object { return (object)['ok'=>false,'skip'=>false,'error'=>"Baris {$n}: {$m}"]; }
    private static function semuaKosong(array $row): bool {
        foreach (range(0,16) as $i) if (isset($row[$i]) && trim((string)$row[$i])!=='') return false;
        return true;
    }
    private static function parseTanggal(mixed $v): ?Carbon {
        if ($v instanceof \DateTimeInterface) return Carbon::instance($v);
        if (is_numeric($v)) { $ts=((float)$v-25569)*86400; return $ts>0?Carbon::createFromTimestampUTC((int)$ts)->startOfDay():null; }
        $s=trim((string)$v); if($s==='') return null;
        foreach (['d/m/Y','Y-m-d','Y-m-d H:i:s','d/m/Y H:i:s'] as $fmt) {
            try{ $d=Carbon::hasFormat($s,$fmt)?Carbon::createFromFormat($fmt,$s):null; }catch(\Throwable){$d=null;}
            if($d) return $d->startOfDay();
        }
        return null;
    }
    private static function toFloat(mixed $v): ?float {
        if($v===null||trim((string)$v)==='') return null;
        $raw=trim((string)$v); if($raw==='') return null; if($raw[0]==='=') return null;
        $s=preg_replace('/[^\d.\-]/','',$raw); return is_numeric($s)?(float)$s:null;
    }
    private static function normCoa(mixed $v): string {
        if($v===null||trim((string)$v)==='') return '';
        $s=preg_replace('/[^\d]/','',(string)$v); return $s!==''?$s:trim((string)$v);
    }
}
