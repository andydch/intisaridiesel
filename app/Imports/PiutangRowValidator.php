<?php

namespace App\Imports;

use Carbon\Carbon;

class PiutangRowValidator
{
    /**
     * Validasi v2 PIUTANG (A-P, 16 col). Hapus validasi lama.
     * Kolom: A Kode Cust(0), B Invoice Date(1), C INT BRANCH(2), D DPP(3), E PPN(4), F TOT(5), G JOURNAL TYPE(6), H METODE BAYAR(7), I Bayar Via(8), J COA CODE(9), K NO GIRO(10), L JOURNAL DATE(11), M DISC(12), N ADM BANK(13), O PENERIMAAN LAIN2(14), P BIAYA KIRIM(15)
     */
    public static function validate(array $row, int $barisKe): object
    {
        $kode = trim((string) ($row[0] ?? ''));
        if ($kode === '' && self::semuaKosong($row)) {
            return (object) ['ok' => false, 'skip' => true, 'error' => "Baris {$barisKe}: kosong"];
        }
        if ($kode === '') return self::err($barisKe, 'Kode Customer (A) wajib terisi');

        // Excel reads 0 as null — normalisasi PPN 0
        if (!isset($row[4]) || $row[4] === null || $row[4] === '') $row[4] = 0;

        // Validasi B-J harus terisi (B=1 s/d J=9)
        $wajibB_J = [
            1=>'B(Invoice Date)',2=>'C(INT BRANCH)',3=>'D(DPP)',4=>'E(PPN)',5=>'F(TOT)',
            6=>'G(JOURNAL TYPE)',7=>'H(METODE BAYAR)',8=>'I(Bayar Via)',9=>'J(COA CODE)'
        ];
        foreach ($wajibB_J as $idx=>$label) {
            if (trim((string)($row[$idx] ?? '')) === '') return self::err($barisKe, "Kolom $label harus terisi");
        }

        // Validasi B,L tanggal DD/MM/YYYY, L wajib diisi (B=1,L=11)
        $tglB = self::parseTanggal($row[1] ?? null);
        if (! $tglB) return self::err($barisKe, 'Invoice Date (B) harus DD/MM/YYYY');
        if (trim((string)($row[11] ?? '')) === '') return self::err($barisKe, 'Journal Date (L) wajib diisi');
        $tglL = self::parseTanggal($row[11] ?? null);
        if (! $tglL) return self::err($barisKe, 'Journal Date (L) harus DD/MM/YYYY');

        // G hanya P/N
        $jenis = strtoupper(trim((string)($row[6] ?? '')));
        if (!in_array($jenis, ['P','N'], true)) return self::err($barisKe, 'Journal Type (G) hanya boleh P atau N');

        // D-F numerik, D & F >0, E boleh 0
        $numeric = [3=>['label'=>'D(DPP)','gt0'=>true],4=>['label'=>'E(PPN)','gt0'=>false],5=>['label'=>'F(TOT)','gt0'=>true]];
        $angka=[];
        foreach ($numeric as $idx=>$cfg) {
            $v=self::toFloat($row[$idx]??null);
            if($v===null) return self::err($barisKe,"Kolom {$cfg['label']} harus numerik");
            if($cfg['gt0'] && $v<=0) return self::err($barisKe,"Kolom {$cfg['label']} harus lebih dari 0");
            $angka[$idx]=$v;
        }
        if (abs($angka[5] - ($angka[3] + $angka[4])) > 0.01) {
            return self::err($barisKe, 'Kolom F(TOT) harus = D(DPP)+E(PPN) (selisih >0.01)');
        }

        // C inisial cabang
        $branch = trim((string)($row[2] ?? ''));
        if ($branch==='') return self::err($barisKe,'INT BRANCH (C) wajib terisi');

        // H metode bayar sesuai env
        $metode = trim((string)($row[7] ?? ''));
        $allowed = array_map('trim', explode('|', (string)env('METHOD_BAYAR_SUPPLIER_NAME','Cash|Bank|Advance Payment')));
        // case-insensitive
        $allowedLower = array_map('strtolower', $allowed);
        if (!in_array(strtolower($metode), $allowedLower, true)) return self::err($barisKe,'Metode Bayar (H) harus '.implode('|',$allowed));

        // M-P jika diisi harus numerik, boleh 0, jika kosong akan diwarisi di SheetImport sebelumnya (tapi cek numerik jika terisi)
        foreach ([12=>'M(DISC)',13=>'N(ADM BANK)',14=>'O(PENERIMAAN LAIN)',15=>'P(BIAYA KIRIM)'] as $idx=>$label) {
            $raw=trim((string)($row[$idx]??''));
            if($raw!=='' && self::toFloat($raw)===null) return self::err($barisKe,"Kolom $label jika diisi harus numerik");
        }

        return (object)['ok'=>true,'skip'=>false,'data'=>(object)[
            'customerCode'=>$kode,
            'tanggal'=>$tglB,
            'branchName'=>$branch,
            'dppE'=>$angka[3],
            'ppnF'=>$angka[4],
            'totalG'=>$angka[5],
            'jenis'=>$jenis,
            'metodeNm'=>$metode,
            'bayarVia'=>trim((string)($row[8]??'')),
            'coaCode'=>self::normCoa($row[9]??null),
            'noGiro'=>trim((string)($row[10]??'')),
            'jurnalDate'=> $tglL,
            'discount'=> self::toFloat($row[12]??0) ?? 0,
            'adminBank'=> self::toFloat($row[13]??0) ?? 0,
            'penerimaanLain'=> self::toFloat($row[14]??0) ?? 0,
            'biayaKirim'=> self::toFloat($row[15]??0) ?? 0,
            'rawRow'=>$row,
        ]];
    }

    private static function err(int $n, string $m): object { return (object)['ok'=>false,'skip'=>false,'error'=>"Baris {$n}: {$m}"]; }
    private static function semuaKosong(array $row): bool {
        foreach(range(0,9) as $i) if(isset($row[$i]) && trim((string)$row[$i])!=='') return false;
        return true;
    }
    private static function parseTanggal(mixed $v): ?Carbon {
        if($v instanceof \DateTimeInterface) return Carbon::instance($v);
        if(is_numeric($v)){ $ts=((float)$v-25569)*86400; return $ts>0?Carbon::createFromTimestampUTC((int)$ts)->startOfDay():null; }
        $s=trim((string)$v); if($s==='') return null;
        foreach(['d/m/Y','Y-m-d','Y-m-d H:i:s','d/m/Y H:i:s'] as $fmt){ try{ $d=Carbon::hasFormat($s,$fmt)?Carbon::createFromFormat($fmt,$s):null; }catch(\Throwable){$d=null;} if($d) return $d->startOfDay(); }
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
