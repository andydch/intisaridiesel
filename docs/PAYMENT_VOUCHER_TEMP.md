# Pembayaran Supplier (Temp) — Dokumentasi

**URL:** `/tx/payment-voucher-temp` (route `payment-voucher-temp.create` GET & `store` POST)  
**Gate:** hanya `andydch@koidigital.co.id` & `maeger@koidigital.co.id` (nav + controller `abort_unless` 403). Menu berada **di dalam** `@if ($queryPaymentVoucher || Auth::id==1)` pertama (Finance & Accounting, `menu_id=50`, baris 1048 `nav.blade.php`).

## Alur
1. User pilih **Supplier*** → **NPWP*** (`P`/`N`) → **No Tagihan Supplier***. Setiap pilih reload GET `?supplier=&npwp=&tagihan=` sehingga server isi label dari `tx_hutang_tmp` terpilih.
2. Filter `tx_hutang_tmp` : `YEAR(jurnal_date)<2026` + belum dibayar (`cts_id NOT IN tx_payment_vouchers.tagihan_supplier_id active Y`) + sesuai combo.
3. Semua field lain **label** (Metode Pembayaran, Pembayaran Via, No Rekening, Giro No, Total, Journal Date, Journal Type, Branch).
4. Tabel RO (`tx_tagihan_supplier_details` join `tx_receipt_orders` where `active Y`) + `tfoot` Total / VAT / Total Biaya Lain-lain / Grand Total.
5. **Save** → `POST` dengan `hutang_tmp_id` → validasi + `DB::transaction`:
   - **Race-safe nomor PV:** `Auto_inc where identity_name='tx_payment_vouchers' ->lockForUpdate()` + hitung `newInc` (reset tahunan) + `payment_voucher_no = env(P_PAYMENT_VOUCHER).date('y').'-'.pad5` + `exists()` double-check + `usleep` backoff + retry 3×.
   - Resolve supplier, hitung `paymentTotal = P ? round(total/(1+vat/100),2) : total`, proporsional `factor = paymentTotal / sumDetail`, insert header `Tx_payment_voucher` (`is_draft N`, `is_full_payment Y`).
   - Insert detail per RO (`Tx_payment_voucher_invoice` `total_payment = total_price_per_ro * factor`, `total_payment_after_vat`, `is_full_payment Y`).
   - **Auto-approve** `approved_by=5`, `approved_at=now()`.
   - **Automatic Journal** (Task 5): `lockForUpdate()` pada `Auto_inc tx_general_journal` / `tx_lokal_journal` + `lastCounterIfAny` per `ymTemp` tetap dipertahankan + `exists()` duplikat `general_journal_no` + retry 3×. PPN (P) → `tx_general_journal` (id 8), Non-PPN (N) → `tx_lokal_journal` (id 13). Jika setup `mst_automatic_journal_detail` tak match branch+method, jurnal skip dengan flash warning.
6. Redirect ke GET kosong dengan flash `status` sukses.

## Mapping Field
| `tx_hutang_tmp` | `tx_payment_vouchers` |
|---|---|
| `kode_supplier` → `Mst_supplier.id` | `supplier_id` |
| `journal_type` (P/N) | `payment_type_id` + `journal_type_id` |
| `jurnal_date` | `payment_date` + `reference_date` |
| `total` → `paymentTotal` / `payment_total_after_vat` | `payment_total`, `payment_total_after_vat` |
| `metode_bayar_id` | `payment_mode` |
| `coa_id` | `coa_id` (must `is_cashflow Y`) |
| `cts_id` | `tagihan_supplier_id` |
| `no_giro` | `reference_no` |
| `admin_bank`, `biaya_asuransi`, `biaya_kirim`, `biaya_lain`, `discount` | sama |

## Aturan
- `jurnal_date` wajib < 2026.
- Tagihan belum dibayar (cek `tagihan_supplier_id` active Y).
- `coa_id` wajib cashflow aktif.
- `payment_reference_id` dibiarkan null (tmp tak punya ref_id).
- `payment_date` bisa backdate 2024/2025; counter jurnal per-ym menangani.

## Cara Uji & Rollback
```bash
mysql -uroot db_mni -e "SELECT payment_voucher_no,payment_type_id,payment_total,payment_total_after_vat,approved_by FROM tx_payment_vouchers ORDER BY id DESC LIMIT 1\G"
mysql -uroot db_mni -e "SELECT general_journal_no,automatic_journal_id,total_debit FROM tx_general_journal WHERE module_no=(SELECT payment_voucher_no FROM tx_payment_vouchers ORDER BY id DESC LIMIT 1)\G"
# rollback uji:
mysql -uroot db_mni -e "DELETE FROM tx_payment_voucher_invoices WHERE payment_voucher_id=(SELECT id FROM tx_payment_vouchers ORDER BY id DESC LIMIT 1); DELETE FROM tx_payment_vouchers WHERE remark='Pembayaran Supplier (Temp)' ORDER BY id DESC LIMIT 1; UPDATE Auto_inc SET id_auto_inc=id_auto_inc-1 WHERE identity_name='tx_payment_vouchers';"
```

## Files
- `routes/web.php` (+use `PaymentVoucherTempServerSideController`)
- `resources/views/layouts/nav.blade.php` (nested `@if` email di blok pertama)
- `app/Http/Controllers/tx/PaymentVoucherTempServerSideController.php` (create + store race-safe)
- `resources/views/tx/payment-voucher-temp/create.blade.php` + `breadcrumb.blade.php`

## Troubleshooting 2026-08-31 — ro.description 42S22
- Gejala: GET tx/payment-voucher-temp → 500 SQLSTATE[42S22]: Unknown column 'ro.description' at PaymentVoucherTempServerSideController:88
- Penyebab: select ro.description; skema tx_receipt_orders memakai remark (Tx_receipt_order fillable + DESCRIBE 44 kolom).
- Fix: ro.description → ro.remark as description (alias, Blade {{ $ro->description }} tetap).
- Verifikasi: grep -rn "ro\.description" app =0; tinker GET kosong & lengkap Illuminate\View\View OK; laravel.log tidak tambah 42S22 baru.

## Revisi 2026-08-31 — PV by Journal Date + hapus label + rollback (154400)
- Nomor PV: PVM{yy} dari Journal Date (06/02/2024 → PVM24-), MAX-scan PVM{yy}-% + lock, PVM24-00823 → PVM24-00824 else PVM24-00001 (tested: pv count 0 after rollback, branch 9/Bank reason).
- UI: hapus Branch & Daftar RO (Tagihan) header (blade 83,88 removed, view:clear OK).
- Jurnal: rollback bila tidak bisa dibentuk → status-error 'Jurnal tidak bisa dibentuk: ...' + DB::rollBack().
