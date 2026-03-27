<?php

use App\Exports\admin\BranchExport;
use App\Exports\admin\CityExport;
use App\Exports\admin\CompanyExport;
use App\Exports\admin\CountryExport;
use App\Exports\admin\CourierExport;
use App\Exports\admin\CustomerExport;
use App\Exports\admin\CustomerShipmentExport;
use App\Exports\admin\DistrictExport;
use App\Exports\admin\ProvinceExport;
use App\Exports\admin\SalesmanExport;
use App\Exports\admin\SubDistrictExport;
use App\Exports\admin\SupplierExport;
use App\Exports\dbg\ScanExport;
use App\Exports\report\AlzReceivablesSummPerBranchExport;
use App\Exports\report\CustomerPaymentStatusExport;
use App\Exports\report\InventoryOverStockUnderStockExport;
use App\Exports\report\InventoryPerGudangPerPartNoExport;
use App\Exports\report\InventoryPerMerkPerPartNoExport;
// use App\Exports\report\Invoice2Export;
use App\Exports\report\InvoiceExport;
use App\Exports\report\KwitansiExport;
use App\Exports\report\MasterInventoryExport;
use App\Exports\report\OutstandingPurchaseOrderExport;
use App\Exports\report\OverdueReceivablesPerBranchExport;
use App\Exports\report\ReportAnalyzeDebtSummaryPerBranchExport;
use App\Exports\report\ReportBalanceSheetExport;
// use App\Exports\report\ReportCashFlow2026Export;
use App\Exports\report\ReportCashFlowDbgExport;
use App\Exports\report\ReportCashFlowExport;
use App\Exports\report\ReportChangeInAvgCostExport;
use App\Exports\report\ReportDebtOverduePerBranchExport;
use App\Exports\report\ReportFinanceGeneralLedgerExport;
use App\Exports\report\ReportFinanceIncomeStatementExport;
use App\Exports\report\ReportFinanceJournalExport;
use App\Exports\report\ReportFinanceKartuHutangExport;
use App\Exports\report\ReportFinanceKartuPiutangExport;
use App\Exports\report\ReportFinanceOperatingExpensesExport;
use App\Exports\report\ReportFinanceTransactionJournalExport;
use App\Exports\report\ReportFinanceTransactionPerAccountExport;
use App\Exports\report\ReportMovementOfPartsExport;
use App\Exports\report\ReportOutstandingPurchaseOrderPSExport;
use App\Exports\report\ReportPenjualanPerCustomerDetailFakturExport;
use App\Exports\report\ReportPenjualanPerCustomerPerPartsNoExport;
use App\Exports\report\ReportPenjualanPerCustomerPerPartsNoSoSjExport;
use App\Exports\report\ReportPurchasePerSupplierPerBranchExport;
use App\Exports\report\ReportPurchasePerSupplierPerPartsNoExport;
use App\Exports\report\ReportPurchasePerSupplierPerYearExport;
use App\Exports\report\ReportPurchaseReturExport;
use App\Exports\report\ReportPurchaseSummaryPerBranchPerBrandExport;
use App\Exports\report\ReportPurchaseSummaryPerSupplierExport;
use App\Exports\report\ReportPurchaseSupplierPaymentStatus02Export;
use App\Exports\report\ReportPurchaseSupplierPaymentStatusExport;
use App\Exports\report\ReportReturPenjualanDetailExport;
use App\Exports\report\ReportSalesActualVsTargetPerBranchExport;
use App\Exports\report\ReportSalesPerCustomerPerYearExport;
use App\Exports\report\ReportSalesPerFakturPerSalesOrderExport;
use App\Exports\report\ReportSalesTargetCustomerPerBranchExport;
use App\Exports\report\ReportSalesTargetPerBranchExport;
use App\Exports\report\ReportSummaryReturPenjualanExport;
use App\Exports\report\ReportSummarySalesPerBranchPerBrandExport;
use App\Exports\report\ReportSummarySalesPerBranchPerSalesmanExport;
use App\Exports\report\SalesPerBranchPerCustomerExport;
use App\Exports\report\StockInventoryAccPerBranchExport;
use App\Exports\report\SummaryStockPerGudangPerMerkExport;
use App\Exports\report\SummaryStockPerMerkPerGudangExport;
use App\Exports\report\TagihanSupplierExport;
use App\Http\Controllers\adm\AutomaticJournalController;
use App\Http\Controllers\adm\BranchController;
use App\Http\Controllers\adm\BranchImportController;
use App\Http\Controllers\adm\BranchTargetController;
use App\Http\Controllers\adm\BrandController;
use App\Http\Controllers\adm\BrandTypeController;
use App\Http\Controllers\adm\CityController;
use App\Http\Controllers\adm\CityImportController;
use App\Http\Controllers\adm\CoaController;
use App\Http\Controllers\adm\CompanyController;
use App\Http\Controllers\adm\CompanyImportController;
use App\Http\Controllers\adm\CountryController;
use App\Http\Controllers\adm\CountryImportController;
use App\Http\Controllers\adm\CourierController;
use App\Http\Controllers\adm\CourierImportController;
use App\Http\Controllers\adm\CourierTypeController;
use App\Http\Controllers\adm\CurrencyController;
use App\Http\Controllers\adm\CustomerController;
use App\Http\Controllers\adm\CustomerImportController;
use App\Http\Controllers\adm\CustomerShipmentController;
use App\Http\Controllers\adm\CustomerShipmentImportController;
use App\Http\Controllers\adm\DeliveryTypeController;
use App\Http\Controllers\adm\DistrictController;
use App\Http\Controllers\adm\DistrictImportController;
use App\Http\Controllers\adm\EmployeeSectionController;
use App\Http\Controllers\adm\EntityTypeController;
use App\Http\Controllers\adm\GenderController;
use App\Http\Controllers\adm\MemoLimitController;
use App\Http\Controllers\adm\MenuAccessController;
use App\Http\Controllers\adm\PartCategoryController;
use App\Http\Controllers\adm\PartController;
use App\Http\Controllers\adm\PartImportController;
use App\Http\Controllers\adm\PartTypeController;
use App\Http\Controllers\adm\PaymentReferenceController;
use App\Http\Controllers\adm\ProvinceController;
use App\Http\Controllers\adm\ProvinceImportController;
use App\Http\Controllers\adm\QuantityTypeController;
use App\Http\Controllers\adm\SalesmanController;
use App\Http\Controllers\adm\SalesmanImportController;
use App\Http\Controllers\adm\SalesmanTargetController;
use App\Http\Controllers\adm\SubDistrictController;
use App\Http\Controllers\adm\SubDistrictImportController;
use App\Http\Controllers\adm\SupplierController;
use App\Http\Controllers\adm\SupplierImportController;
use App\Http\Controllers\adm\SupplierTypeController;
use App\Http\Controllers\adm\TaxInvoiceController;
use App\Http\Controllers\adm\UserAccessController;
// use App\Http\Controllers\dbg\TestDatatableController;
use App\Http\Controllers\adm\UserManagementController;
use App\Http\Controllers\adm\VatController;
use App\Http\Controllers\adm\WeightTypeController;
use App\Http\Controllers\auth\SignInController;
use App\Http\Controllers\auth\SignOutController;
use App\Http\Controllers\auth\SignUpController;
use App\Http\Controllers\auth\UserProfileController;
use App\Http\Controllers\dbg\AuthController;
use App\Http\Controllers\dbg\BeginningBalancePerMonthDbgController;
use App\Http\Controllers\dbg\CreateWordController;
use App\Http\Controllers\dbg\DocumentController;
use App\Http\Controllers\dbg\GenFakturController;
// use App\Http\Controllers\dbg\GenRptCashFlowController;
use App\Http\Controllers\dbg\ImportSubDistrictController;
use App\Http\Controllers\dbg\JsonController;
use App\Http\Controllers\dbg\MenuController;
use App\Http\Controllers\dbg\PdfController;
use App\Http\Controllers\dbg\QuotationServerSideDbgController;
use App\Http\Controllers\dbg\RememberController;
use App\Http\Controllers\dbg\RptAnalizeDebtSummController;
use App\Http\Controllers\dbg\StockInventoryAccurationPerBranchController;
use App\Http\Controllers\dbg\StockMasterDbgController;
use App\Http\Controllers\dbg\UpdateQtyController;
use App\Http\Controllers\dbg\UpdAvgSOSJController;
use App\Http\Controllers\dbg\UpdGJController;
use App\Http\Controllers\dbg\UpdJournalDateController;
use App\Http\Controllers\dbg\UpdOHController;
use App\Http\Controllers\dbg\WhereController;
use App\Http\Controllers\main\DBranchController;
use App\Http\Controllers\main\DBranchTargetController;
use App\Http\Controllers\main\DBrandTypeController;
use App\Http\Controllers\main\DCityParamController;
use App\Http\Controllers\main\DCoaParamController;
use App\Http\Controllers\main\DCompanyParamController;
use App\Http\Controllers\main\DCountryParamController;
use App\Http\Controllers\main\DCourierController;
use App\Http\Controllers\main\DCustomerController;
use App\Http\Controllers\main\DDeliveryOrderController;
use App\Http\Controllers\main\DDeliveryOrderNonTaxController;
use App\Http\Controllers\main\DDistrictParamController;
use App\Http\Controllers\main\DGeneralJournalController;
use App\Http\Controllers\main\DGlobalParamController;
use App\Http\Controllers\main\DInvoiceController;
use App\Http\Controllers\main\DispBankAccNoController;
use App\Http\Controllers\main\DispBankAccNoForAcceptancePlanController;
use App\Http\Controllers\main\DispBankAccNoForCashFlowController;
use App\Http\Controllers\main\DispBankAccNoForCustomerController;
use App\Http\Controllers\main\DispBrandTypeByBrandController;
use App\Http\Controllers\main\DispBrandTypeItemController;
use App\Http\Controllers\main\DispCityByCountryController;
use App\Http\Controllers\main\DispCityController;
use App\Http\Controllers\main\DispCoaForGnLedgerRptController;
use App\Http\Controllers\main\DispCoaParentController;
use App\Http\Controllers\main\DispCourierController;
use App\Http\Controllers\main\DispCustomerByIdController;
use App\Http\Controllers\main\DispCustomerController;
use App\Http\Controllers\main\DispCustomerPicController;
use App\Http\Controllers\main\DispCustomerShipmentAddressController;
use App\Http\Controllers\main\DispCustPerFKController;
use App\Http\Controllers\main\DispDeliveryOrderByInvoiceController;
use App\Http\Controllers\main\DispDistrictController;
use App\Http\Controllers\main\DispDOController;
use App\Http\Controllers\main\DispDOnonTaxController;
use App\Http\Controllers\main\DispDOpartController;
use App\Http\Controllers\main\DispFKandSObyCustController;
use App\Http\Controllers\main\DispFKController;
use App\Http\Controllers\main\DispInvNoController;
use App\Http\Controllers\main\DispInvoicesPerCustController;
use App\Http\Controllers\main\DispInvoicesPerDOController;
use App\Http\Controllers\main\DispInvoicesPerSupplierController;
use App\Http\Controllers\main\DispInvpartNotaReturInfoController;
use App\Http\Controllers\main\DispITstockmasterController;
use App\Http\Controllers\main\DispMemoPartRefController;
use App\Http\Controllers\main\DispNPandSJbyCustController;
use App\Http\Controllers\main\DispNPController;
use App\Http\Controllers\main\DispOOstockmasterController;
use App\Http\Controllers\main\DispPAinvTotalPriceController;
use App\Http\Controllers\main\DispPartInfoController;
use App\Http\Controllers\main\DispPartInfoForStockAdjController;
use App\Http\Controllers\main\DispPartInfoRptController;
use App\Http\Controllers\main\DispPartPriceRefController;
use App\Http\Controllers\main\DispPartsInPQController;
use App\Http\Controllers\main\DispPartsInSQController;
use App\Http\Controllers\main\DispPaymentRefController;
use App\Http\Controllers\main\DispPoCurrencyController;
use App\Http\Controllers\main\DispPoPmController;
use App\Http\Controllers\main\DispPoPmWithInfoController;
use App\Http\Controllers\main\DispPQbySupplierController;
use App\Http\Controllers\main\DispProvinceController;
use App\Http\Controllers\main\DispQuotationPartRefController;
use App\Http\Controllers\main\DispQuotationSuratJalanPartRefController;
use App\Http\Controllers\main\DispReceiptOrderInfoController;
use App\Http\Controllers\main\DispReceiptOrderPartInfoController;
use App\Http\Controllers\main\DispReceiptOrderTotalPriceController;
use App\Http\Controllers\main\DispROinfoController;
use App\Http\Controllers\main\DispRoTagihanSupplierController;
use App\Http\Controllers\main\DispRoTagihanSupplierDtlController;
use App\Http\Controllers\main\DispRoTagihanSupplierNoRekBankController;
use App\Http\Controllers\main\DispSalesmanByBranchController;
use App\Http\Controllers\main\DispSalesmanController;
use App\Http\Controllers\main\DispSalesmanPerBranchController;
use App\Http\Controllers\main\DispSalesOrderInfoByIdController;
use App\Http\Controllers\main\DispSalesOrderWithInfoController;
use App\Http\Controllers\main\DispSalesOrderWithInfoNonTaxController;
use App\Http\Controllers\main\DispSalesTargetPerBranchPerYearController;
use App\Http\Controllers\main\DispShipmentAddressController;
use App\Http\Controllers\main\DispSimilarCourierController;
use App\Http\Controllers\main\DispSimilarCustomerCodeController;
use App\Http\Controllers\main\DispSimilarCustomerController;
use App\Http\Controllers\main\DispSimilarPartNumberController;
use App\Http\Controllers\main\DispSimilarSalesmanController;
use App\Http\Controllers\main\DispSimilarSupplierCodeController;
// use App\Http\Controllers\tx\ReceiptOrderApprovalController;
use App\Http\Controllers\main\DispSimilarSupplierController;
use App\Http\Controllers\main\DispSJbyCustController;
use App\Http\Controllers\main\DispSJdtlController;
use App\Http\Controllers\main\DispSJPartRefController;
use App\Http\Controllers\main\DispSObyCustController;
use App\Http\Controllers\main\DispSObyDOController;
use App\Http\Controllers\main\DispSOController;
use App\Http\Controllers\main\DispSOdateController;
use App\Http\Controllers\main\DispSOdtlController;
use App\Http\Controllers\main\DispSONonTaxController;
use App\Http\Controllers\main\DispSONonTaxDateController;
use App\Http\Controllers\main\DispSOPartRefController;
use App\Http\Controllers\main\DispSOstockmasterController;
use App\Http\Controllers\main\DispSQcustController;
use App\Http\Controllers\main\DispSQcustSuratJalanController;
use App\Http\Controllers\main\DispStockTransferPartRefController;
use App\Http\Controllers\main\DispSubDistrictController;
use App\Http\Controllers\main\DispSubDistrictItemController;
use App\Http\Controllers\main\DispSupplierBankByIdController;
use App\Http\Controllers\main\DispSupplierBankInfoCurrencyController;
use App\Http\Controllers\main\DispSupplierByIdController;
use App\Http\Controllers\main\DispSupplierController;
use App\Http\Controllers\main\DispSupplierPicController;
use App\Http\Controllers\main\DispSuratJalanInfoByIdController;
use App\Http\Controllers\main\DispSuratJalanWithInfoController;
use App\Http\Controllers\main\DispTagihanSupplierController;
use App\Http\Controllers\main\DispTagihanSupplierDetailController;
use App\Http\Controllers\main\DKwitansiController;
use App\Http\Controllers\main\DLokalJournalController;
use App\Http\Controllers\main\DMemoController;
use App\Http\Controllers\main\DNotaReturController;
use App\Http\Controllers\main\DNotaReturNonTaxController;
use App\Http\Controllers\main\DOrderController;
use App\Http\Controllers\main\DPartMasterStockController;
use App\Http\Controllers\main\DPaymentReceiptController;
use App\Http\Controllers\main\DPaymentVoucherController;
use App\Http\Controllers\main\DProvinceParamController;
use App\Http\Controllers\main\DPurchaseInquiryController;
use App\Http\Controllers\main\DPurchaseReturController;
use App\Http\Controllers\main\DQuotationController;
use App\Http\Controllers\main\DReceiptOrderController;
use App\Http\Controllers\main\DSalesmanTargetController;
use App\Http\Controllers\main\DSalesOrderController;
use App\Http\Controllers\main\DSalesQuotationController;
use App\Http\Controllers\main\DStockAdjustmentController;
use App\Http\Controllers\main\DStockAssemblyController;
use App\Http\Controllers\main\DStockDisAssemblyController;
use App\Http\Controllers\main\DStockTransferController;
use App\Http\Controllers\main\DSubDistrictParamController;
use App\Http\Controllers\main\DSupplierController;
use App\Http\Controllers\main\DSuratJalanController;
use App\Http\Controllers\main\PartsJsonController;
use App\Http\Controllers\manual\ManualCustomerImportController;
use App\Http\Controllers\manual\ManualSupplierImportController;
use App\Http\Controllers\ope\SyncCityController;
use App\Http\Controllers\ope\SyncCountryController;
use App\Http\Controllers\ope\SyncProvinceController;
use App\Http\Controllers\ope\SyncSubDistrictController;
use App\Http\Controllers\rpt\CustomerPaymentStatusController;
use App\Http\Controllers\rpt\ReportAlzReceivablesSummPerBranchController;
use App\Http\Controllers\rpt\ReportAnalyzeDebtSummaryPerBranchController;
use App\Http\Controllers\rpt\ReportBalanceSheetController;
// use App\Http\Controllers\rpt\ReportCashFlow2026Controller;
use App\Http\Controllers\rpt\ReportCashFlowController;
use App\Http\Controllers\rpt\ReportCashFlowDbgController;
use App\Http\Controllers\rpt\ReportChangeInAvgCostController;
use App\Http\Controllers\rpt\ReportDebtOverduePerBranchController;
use App\Http\Controllers\rpt\ReportFinanceGeneralLedgerController;
use App\Http\Controllers\rpt\ReportFinanceIncomeStatementController;
use App\Http\Controllers\rpt\ReportFinanceJournalController;
use App\Http\Controllers\rpt\ReportFinanceOperatingExpensesController;
use App\Http\Controllers\rpt\ReportFinanceTransactionJournalController;
use App\Http\Controllers\rpt\ReportFinanceTransactionPerAccountController;
use App\Http\Controllers\rpt\ReportInventoryOverStockUnderStockController;
use App\Http\Controllers\rpt\ReportInventoryPerGudangPerPartNoController;
use App\Http\Controllers\rpt\ReportInventoryPerMerkPerPartNoController;
use App\Http\Controllers\rpt\ReportKartuHutang;
use App\Http\Controllers\rpt\ReportKartuPiutang;
use App\Http\Controllers\rpt\ReportMasterInventoryController;
use App\Http\Controllers\rpt\ReportMovementOfPartsController;
use App\Http\Controllers\rpt\ReportOutstandingPurchaseOrderController;
use App\Http\Controllers\rpt\ReportOutstandingPurchaseOrderPSController;
use App\Http\Controllers\rpt\ReportOverdueReceivablesPerBranchController;
use App\Http\Controllers\rpt\ReportPenjualanPerCustomerDetailFakturController;
use App\Http\Controllers\rpt\ReportPenjualanPerCustomerPerPartsNoController;
use App\Http\Controllers\rpt\ReportPenjualanPerCustomerPerPartsNoSoSjController;
use App\Http\Controllers\rpt\ReportPenjualanPerCustomerPerTahunController;
use App\Http\Controllers\rpt\ReportPurchasePerSupplierPerBranchController;
use App\Http\Controllers\rpt\ReportPurchasePerSupplierPerPartsNoController;
use App\Http\Controllers\rpt\ReportPurchasePerSupplierPerYearController;
use App\Http\Controllers\rpt\ReportPurchaseReturController;
use App\Http\Controllers\rpt\ReportPurchaseSummaryPerBranchPerBrandController;
use App\Http\Controllers\rpt\ReportPurchaseSummaryPerSupplierController;
use App\Http\Controllers\rpt\ReportPurchaseSupplierPaymentStatus02Controller;
use App\Http\Controllers\rpt\ReportPurchaseSupplierPaymentStatusController;
// use App\Http\Controllers\tx\PaymentReceiptServerSideControllerGJ;
use App\Http\Controllers\rpt\ReportReturPenjualanDetailController;
use App\Http\Controllers\rpt\ReportSalesActualVsTargetPerBranchController;
use App\Http\Controllers\rpt\ReportSalesPerBranchPerCustomerController;
use App\Http\Controllers\rpt\ReportSalesPerCustomerPerYearController;
use App\Http\Controllers\rpt\ReportSalesPerFakturPerSalesOrderController;
use App\Http\Controllers\rpt\ReportSalesTargetCustomerPerBranchController;
use App\Http\Controllers\rpt\ReportSalesTargetPerBranchController;
use App\Http\Controllers\rpt\ReportStockInventoryAccPerBranchController;
use App\Http\Controllers\rpt\ReportSummaryPenjualanPerBranchPerBrandController;
use App\Http\Controllers\rpt\ReportSummaryReturPenjualanController;
use App\Http\Controllers\rpt\ReportSummarySalesPerBranchPerSalesmanController;
use App\Http\Controllers\rpt\ReportSummaryStockPerGudangPerMerkController;
use App\Http\Controllers\rpt\ReportSummaryStockPerMerkPerGudangController;
use App\Http\Controllers\tx\AcceptancePlanPerInvServerSideController;
use App\Http\Controllers\tx\AcceptancePlanServerSideController;
use App\Http\Controllers\tx\DeliveryOrderAdjustmentController;
use App\Http\Controllers\tx\DeliveryOrderFPController;
use App\Http\Controllers\tx\DeliveryOrderNonTaxPrintController;
use App\Http\Controllers\tx\DeliveryOrderNonTaxServerSideController;
use App\Http\Controllers\tx\DeliveryOrderServerSideController;
use App\Http\Controllers\tx\DownloadFakturPajakController;
use App\Http\Controllers\tx\FakturPrintController;
// use App\Http\Controllers\tx\GeneralJournalApprovalServerSideController;
use App\Http\Controllers\tx\GeneralJournalServerSideController;
use App\Http\Controllers\tx\InvoiceApprovalController;
use App\Http\Controllers\tx\InvoicePrintController;
use App\Http\Controllers\tx\InvoiceServerSideController;
use App\Http\Controllers\tx\InvoiceTaxInvController;
use App\Http\Controllers\tx\KwitansiApprovalController;
use App\Http\Controllers\tx\KwitansiPrintController;
use App\Http\Controllers\tx\KwitansiServerSideController;
// use App\Http\Controllers\tx\LokalJournalApprovalServerSideController;
use App\Http\Controllers\tx\LokalJournalServerSideController;
use App\Http\Controllers\tx\MemoPrintController;
use App\Http\Controllers\tx\MemoServerSideController;
use App\Http\Controllers\tx\NotaReturApprovalServerSideController;
use App\Http\Controllers\tx\NotaReturNonTaxApprovalServerSideController;
use App\Http\Controllers\tx\NotaReturPrintController;
use App\Http\Controllers\tx\NotaReturServerSideController;
use App\Http\Controllers\tx\OrderApprovalServerSideController;
use App\Http\Controllers\tx\OrderPrintController;
use App\Http\Controllers\tx\OrderServerSideController;
use App\Http\Controllers\tx\PaymentPlanPerRCServerSideController;
use App\Http\Controllers\tx\PaymentPlanServerSideController;
use App\Http\Controllers\tx\PaymentReceiptServerSideController;
use App\Http\Controllers\tx\PaymentVoucherApprovalServerSideController;
use App\Http\Controllers\tx\PaymentVoucherServerSideController;
use App\Http\Controllers\tx\PurchaseInquiryPrintController;
use App\Http\Controllers\tx\PurchaseInquiryServerSideController;
use App\Http\Controllers\tx\PurchaseReturApprovalServerSideController;
use App\Http\Controllers\tx\PurchaseReturPrintController;
use App\Http\Controllers\tx\PurchaseReturServerSideController;
use App\Http\Controllers\tx\QuotationPrintController;
use App\Http\Controllers\tx\QuotationServerSideController;
use App\Http\Controllers\tx\ReceiptOrderPrintController;
use App\Http\Controllers\tx\ReceiptOrderServerSideController;
use App\Http\Controllers\tx\ReturPrintController;
use App\Http\Controllers\tx\ReturServerSideController;
use App\Http\Controllers\tx\SalesOrderApprovalServerSideController;
use App\Http\Controllers\tx\SalesOrderPrintController;
use App\Http\Controllers\tx\SalesOrderServerSideController;
use App\Http\Controllers\tx\SalesProgressServerSideController;
use App\Http\Controllers\tx\SalesQuoPrintController;
use App\Http\Controllers\tx\SalesQuotationServerSideController;
use App\Http\Controllers\tx\StockAdjustmentPrintController;
use App\Http\Controllers\tx\StockAdjustmentServerSideController;
use App\Http\Controllers\tx\StockAssemblyPrintController;
use App\Http\Controllers\tx\StockAssemblyServerSideController;
use App\Http\Controllers\tx\StockDisAssemblyPrintController;
use App\Http\Controllers\tx\StockDisAssemblyServerSideController;
use App\Http\Controllers\tx\StockMasterPartController;
use App\Http\Controllers\tx\StockMasterServerSideController;
use App\Http\Controllers\tx\StockMasterStockCardController;
use App\Http\Controllers\tx\StockTransferApprovalServerSideController;
use App\Http\Controllers\tx\StockTransferPrintController;
use App\Http\Controllers\tx\StockTransferReceivedServerSideController;
use App\Http\Controllers\tx\StockTransferServerSideController;
use App\Http\Controllers\tx\SuratJalanApprovalServerSideController;
use App\Http\Controllers\tx\SuratJalanPrintController;
use App\Http\Controllers\tx\SuratJalanServerSideController;
use App\Http\Controllers\tx\TagihanSupplierServerSideController;
use App\Http\Controllers\tx\UploadFakturPajakController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
| - , 'cache.headers:public;max_age=2628000;etag'
*/

Route::group(
    [
        'middleware' => ['forceHttps']
    ],
    function () {
        Route::get('/', function () {
            return view('main.authentication-signin');
        });
        Route::get('/verification-notice', function () {
            echo 'gagal verifikasi';
        })->name('verification.notice');

        // registrasi & login
        Route::get('/login', function () {
            return view('main.authentication-signin');
        })->name('login');
        Route::get('/authentication-signin', function () {
            return view('main.authentication-signin');
        });
        Route::get('/authentication-signup', function () {
            return view('main.authentication-signup');
        });
        Route::resource('/sign-in', SignInController::class)->except([
            'index', 'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/sign-up', SignUpController::class)->except([
            'index', 'create', 'show', 'edit', 'update', 'destroy'
        ]);

        Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.verify');

        // registrasi & login
        Route::get('/err-notif', function () {
            $data = [
                'errNotif' => 'You are not allowed to access this page!'
            ];
            return view('error-notif.error-notif', $data);
        });

        Route::get('log-viewers', [LogViewerController::class, 'index']);
    }
);

Route::group(
    [
        'middleware' => ['forceHttps', 'auth']
    ],
    function () {
        Route::get('/dashboard', function () {
            return view('main.index');
        });
        Route::resource('/sign-out', SignOutController::class)->except(['create', 'show', 'store', 'edit', 'update', 'destroy']);
        Route::get('/user-profile/update-profile/{slug}', [UserProfileController::class, 'show']);
        Route::resource('/user-profile', UserProfileController::class)->except(['create', 'show', 'store', 'destroy']);
        Route::post('/disp_province', DispProvinceController::class);
        Route::post('/disp_coa_parent', DispCoaParentController::class);
        Route::post('/disp_city_by_country', DispCityByCountryController::class);
        Route::post('/disp_city', DispCityController::class);
        Route::post('/disp_district', DispDistrictController::class);
        Route::post('/disp_sub_district', DispSubDistrictController::class);
        Route::post('/disp_sub_district_postcode', DispSubDistrictItemController::class);
        Route::post('/disp_supplier_pic', DispSupplierPicController::class);
        Route::post('/disp_customer_pic', DispCustomerPicController::class);
        Route::post('/disp_supplier_currency', DispSupplierBankInfoCurrencyController::class);
        Route::post('/disp_similar_custname', DispSimilarCustomerController::class);
        Route::post('/disp-so-stock-master', DispSOstockmasterController::class);
        Route::post('/disp-oo-stock-master', DispOOstockmasterController::class);
        Route::post('/disp-it-stock-master', DispITstockmasterController::class);
        Route::post('/disp_similar_partno', DispSimilarPartNumberController::class);
        Route::post('/disp_similar_custcode', DispSimilarCustomerCodeController::class);
        Route::post('/disp_custinfo', DispCustomerController::class);
        Route::post('/disp_custinfo_byid', DispCustomerByIdController::class);
        Route::post('/disp_custinfo_shipment_address', DispCustomerShipmentAddressController::class);
        Route::post('/disp_similar_suppliername', DispSimilarSupplierController::class);
        Route::post('/disp_similar_suppliercode', DispSimilarSupplierCodeController::class);
        Route::post('/disp_supplierinfo', DispSupplierController::class);
        Route::post('/disp_supplierinfo_by_id', DispSupplierByIdController::class);
        Route::post('/disp_supplier_bank_info_by_id', DispSupplierBankByIdController::class);
        Route::post('/disp_tagihan_supplier', DispTagihanSupplierController::class);
        Route::post('/disp_tagihan_supplier_dtl', DispTagihanSupplierDetailController::class);
        Route::post('/disp_similar_salesmanname', DispSimilarSalesmanController::class);
        Route::post('/disp_salesmaninfo', DispSalesmanController::class);
        Route::post('/disp_salesmanbybranchinfo', DispSalesmanByBranchController::class);
        Route::post('/disp_similar_couriername', DispSimilarCourierController::class);
        Route::post('/disp_courierinfo', DispCourierController::class);
        Route::post('/disp_brand_type_item', DispBrandTypeItemController::class);
        Route::post('/disp_receipt_order_info', DispReceiptOrderInfoController::class);
        Route::post('/disp_part_info', DispPartInfoController::class);
        Route::post('/disp_part_info_rpt', DispPartInfoRptController::class);
        Route::post('/disp_receipt_order_part_info', DispReceiptOrderPartInfoController::class);
        Route::post('/disp_part_price_ref_info', DispPartPriceRefController::class);
        Route::post('/disp_part_info_for_stockadj', DispPartInfoForStockAdjController::class);
        Route::post('/disp_memo_part_ref_info', DispMemoPartRefController::class);
        Route::post('/disp_quotation_part_ref_info', DispQuotationPartRefController::class);
        Route::post('/disp_so_part_ref_info', DispSOPartRefController::class);
        Route::post('/disp_quotation_part_surat_jalan_ref_info', DispQuotationSuratJalanPartRefController::class);
        Route::post('/disp_sj_part_ref_info', DispSJPartRefController::class);
        Route::post('/disp_stocktransfer_part_ref_info', DispStockTransferPartRefController::class);
        Route::post('/disp_receiptorder_totalprice_info', DispReceiptOrderTotalPriceController::class);
        Route::post('/disp_pa_inv_totalprice_info', DispPAinvTotalPriceController::class);
        Route::post('/disp_inv_per_supplier_info', DispInvoicesPerSupplierController::class);
        Route::post('/disp_inv_per_cust_info', DispInvoicesPerCustController::class);
        Route::post('/disp_fk_so_by_cust', DispFKandSObyCustController::class);
        Route::post('/disp_so_by_cust', DispSObyCustController::class);
        Route::post('/disp_np_sj_by_cust', DispNPandSJbyCustController::class);
        Route::post('/disp_sj_by_cust', DispSJbyCustController::class);
        Route::post('/disp_so_info', DispSalesOrderInfoByIdController::class);
        Route::post('/disp_sj_info', DispSuratJalanInfoByIdController::class);
        Route::post('/disp_do_per_inv_info', DispDeliveryOrderByInvoiceController::class);
        Route::post('/disp_inv_per_do_info', DispInvoicesPerDOController::class);
        Route::post('/del_memo', DMemoController::class);
        Route::post('/del_purchase_inquiry', DPurchaseInquiryController::class);
        Route::post('/del_quotation', DQuotationController::class);
        Route::post('/del_pv', DPaymentVoucherController::class);
        Route::post('/del_preceipt', DPaymentReceiptController::class);
        Route::post('/del_sales_quotation', DSalesQuotationController::class);
        Route::post('/del_order', DOrderController::class);
        Route::post('/del_general_journal', DGeneralJournalController::class);
        Route::post('/del_lokal_journal', DLokalJournalController::class);
        Route::post('/del_receiptorder', DReceiptOrderController::class);
        Route::post('/del_purchaseretur', DPurchaseReturController::class);
        Route::post('/del_notaretur', DNotaReturController::class);
        Route::post('/del_notareturnontax', DNotaReturNonTaxController::class);
        Route::post('/del_deliveryorder', DDeliveryOrderController::class);
        Route::post('/del_deliveryorder_nontax', DDeliveryOrderNonTaxController::class);
        Route::post('/del_invoice', DInvoiceController::class);
        Route::post('/del_kwitansi', DKwitansiController::class);
        Route::post('/disp_parts_by_pq', DispPartsInPQController::class);
        Route::post('/disp_pq_by_supplier', DispPQbySupplierController::class);
        Route::post('/del_salesorder', DSalesOrderController::class);
        Route::post('/del_suratjalan', DSuratJalanController::class);
        Route::post('/disp_sq_cust', DispSQcustController::class);
        Route::post('/disp_sq_cust_sj', DispSQcustSuratJalanController::class);
        Route::post('/disp_parts_by_sq', DispPartsInSQController::class);
        Route::post('/disp_po_pm', DispPoPmController::class);
        Route::post('/disp_po_curr', DispPoCurrencyController::class);
        Route::post('/disp_po_pm_part', DispPoPmWithInfoController::class);
        Route::post('/disp_inv_no', DispInvNoController::class);
        Route::post('/disp_ro', DispROinfoController::class);
        Route::post('/disp_ro_tagihan_supplier', DispRoTagihanSupplierController::class);
        Route::post('/disp_ro_tagihan_supplier_norek', DispRoTagihanSupplierNoRekBankController::class);
        Route::post('/disp_ro_tagihan_supplier_dtl', DispRoTagihanSupplierDtlController::class);
        Route::post('/disp_inv_part_nota_retur', DispInvpartNotaReturInfoController::class);
        Route::post('/disp_so', DispSOController::class);
        Route::post('/disp_salesman_per_branch', DispSalesmanPerBranchController::class);
        Route::post('/disp_salestarget_per_branch_per_year', DispSalesTargetPerBranchPerYearController::class);
        Route::post('/disp_so_non_tax', DispSONonTaxController::class);
        Route::post('/disp_so_dtl', DispSOdtlController::class);
        Route::post('/disp_sj_dtl', DispSJdtlController::class);
        Route::post('/disp_so_date', DispSOdateController::class);
        Route::post('/disp_so_non_tax_date', DispSONonTaxDateController::class);
        Route::post('/disp_fk', DispFKController::class);
        Route::post('/disp_np', DispNPController::class);
        Route::post('/disp_do', DispDOController::class);
        Route::post('/disp_do_non_tax', DispDOnonTaxController::class);
        Route::post('/disp_do_part', DispDOpartController::class);
        Route::post('/disp_so_by_do', DispSObyDOController::class);
        Route::post('/disp_shipment_address', DispShipmentAddressController::class);
        Route::post('/disp_so_part', DispSalesOrderWithInfoController::class);
        Route::post('/disp_sj_part', DispSuratJalanWithInfoController::class);
        Route::post('/disp_so_part_non_tax', DispSalesOrderWithInfoNonTaxController::class);
        Route::post('/del_stocktransfer', DStockTransferController::class);
        Route::post('/del_stockassembly', DStockAssemblyController::class);
        Route::post('/del_stockadj', DStockAdjustmentController::class);
        Route::post('/del_stockdisassembly', DStockDisAssemblyController::class);
        Route::post('/del_globalparam', DGlobalParamController::class);
        Route::post('/del_brandtypeparam', DBrandTypeController::class);
        Route::post('/del_mstock', DPartMasterStockController::class);
        Route::post('/del_countryparam', DCountryParamController::class);
        Route::post('/del_provinceparam', DProvinceParamController::class);
        Route::post('/del_cityparam', DCityParamController::class);
        Route::post('/del_districtparam', DDistrictParamController::class);
        Route::post('/del_subdistrictparam', DSubDistrictParamController::class);
        Route::post('/del_branch', DBranchController::class);
        Route::post('/del_branch_target', DBranchTargetController::class);
        Route::post('/del_salesman_target', DSalesmanTargetController::class);
        Route::post('/del_customer', DCustomerController::class);
        Route::post('/del_supplier', DSupplierController::class);
        Route::post('/del_courier', DCourierController::class);
        Route::post('/del_company', DCompanyParamController::class);
        Route::post('/del_coa', DCoaParamController::class);
        Route::post('/disp_brand_type', DispBrandTypeByBrandController::class);
        Route::post('/disp_payment_ref', DispPaymentRefController::class);
        Route::post('/disp_bankaccno', DispBankAccNoController::class);
        Route::post('/disp_bankaccnoforcust', DispBankAccNoForCustomerController::class);
        Route::post('/disp_bankaccno_forcashflow', DispBankAccNoForCashFlowController::class);
        Route::post('/disp_custperfk', DispCustPerFKController::class);
        Route::post('/disp_bankaccno_foracceptanceplan', DispBankAccNoForAcceptancePlanController::class);
        Route::post('/disp_coa_for_gnledger_rpt', DispCoaForGnLedgerRptController::class);

        Route::get('/parts-json', PartsJsonController::class);
    }
);

Route::group(
    [
        'prefix' => 'tx',
        'middleware' => ['forceHttps', 'auth', 'valdUser']
    ],
    function () {
        // memo
        Route::resource('/memo', MemoServerSideController::class)->except(['destroy']);
        Route::resource('/print-memo', MemoPrintController::class)->except(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // order
        Route::resource('/order', OrderServerSideController::class)->except(['destroy']);
        Route::resource('/print-order', OrderPrintController::class)->except(['destroy']);
        // order - approval
        Route::resource('/order-approval', OrderApprovalServerSideController::class)->except(['show', 'destroy']);

        // receipt order
        Route::get('/receipt-order-index/{param?}', [ReceiptOrderServerSideController::class, 'index'])->name('ro.index');
        Route::resource('/receipt-order', ReceiptOrderServerSideController::class)->except(['destroy']);
        Route::resource('/print-receipt-order', ReceiptOrderPrintController::class)->except(['destroy']);
        Route::put('/receipt-order-journal-type/{id}', [ReceiptOrderServerSideController::class, 'updJournalType']);
        // receipt order - approval
        // Route::resource('/receipt-order-approval', ReceiptOrderApprovalController::class)->except(['create', 'store', 'edit', 'destroy']);

        // quotation
        Route::resource('/quotation', QuotationServerSideController::class)->except(['destroy']);
        Route::get('/print-quotation', QuotationPrintController::class);

        // sales quotation
        Route::resource('/sales-quotation', SalesQuotationServerSideController::class)->except(['destroy']);
        Route::get('/print-sales-quotation', SalesQuoPrintController::class);

        // sales order
        Route::resource('/sales-order', SalesOrderServerSideController::class)->except(['destroy']);
        Route::get('/print-sales-order', SalesOrderPrintController::class);
        // sales order - approval
        Route::resource('/sales-order-approval', SalesOrderApprovalServerSideController::class)->except(['show', 'destroy']);

        // sales progress
        Route::resource('/sales-progress', SalesProgressServerSideController::class)->except(['create', 'show', 'edit', 'update', 'destroy']);

        // surat jalan
        Route::resource('/surat-jalan', SuratJalanServerSideController::class)->except(['destroy']);
        Route::get('/print-surat-jalan', SuratJalanPrintController::class);
        // surat jalan - approval
        Route::resource('/surat-jalan-approval', SuratJalanApprovalServerSideController::class)->except(['show', 'destroy']);

        // purchase retur
        Route::resource('/purchase-retur', PurchaseReturServerSideController::class)->except(['destroy']);
        Route::resource('/print-purchase-retur', PurchaseReturPrintController::class)->except(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        // purchase retur approval
        Route::resource('/purchase-retur-approval', PurchaseReturApprovalServerSideController::class)->except(['create','store','edit','destroy']);

        // faktur, formerly delivery order
        Route::resource('/faktur', DeliveryOrderServerSideController::class)->except(['destroy']);
        Route::resource('/faktur-fp', DeliveryOrderFPController::class)->except(['index','create','store','show','destroy']);
        Route::get('/print-faktur', FakturPrintController::class);
        Route::resource('/delivery-order-adjustment', DeliveryOrderAdjustmentController::class)->except(['destroy']);

        //download faktur pajak
        Route::resource('/dl-faktur-pajak', DownloadFakturPajakController::class)->except(['index', 'show', 'edit', 'update', 'destroy']);

        //upload faktur pajak
        Route::resource('/upl-faktur-pajak', UploadFakturPajakController::class)->except(['index', 'show', 'edit', 'update', 'destroy']);

        // delivery order - non tax
        Route::resource('/delivery-order-local', DeliveryOrderNonTaxServerSideController::class)->except(['destroy']);
        Route::get('/print-do', DeliveryOrderNonTaxPrintController::class);

        // nota retur
        Route::resource('/nota-retur', NotaReturServerSideController::class)->except(['destroy']);
        Route::resource('/print-nota-retur', NotaReturPrintController::class)->except(['destroy']);
        // nota retur approval
        Route::resource('/nota-retur-approval', NotaReturApprovalServerSideController::class)->except(['create','store','edit','destroy']);

        // retur
        Route::resource('/retur', ReturServerSideController::class)->except(['destroy']);
        Route::resource('/print-retur', ReturPrintController::class)->except(['destroy']);
        // retur approval
        Route::resource('/retur-approval', NotaReturNonTaxApprovalServerSideController::class)->except(['create','store','edit','destroy']);

        // stock master
        Route::get('/stock-master/{param?}', [StockMasterServerSideController::class, 'index'])->name('stockmaster.index');
        Route::post('/stock-master-post', [StockMasterServerSideController::class, 'store'])->name('stockmaster.store');

        Route::resource('/stock-master-stock-card', StockMasterStockCardController::class)->except(['destroy']);
        Route::resource('/stock-master-part', StockMasterPartController::class)->except(['index','destroy']);
        Route::post('/stock-master-import-part', PartImportController::class);

        // stock transfer
        Route::resource('/stock-transfer', StockTransferServerSideController::class)->except(['destroy']);
        Route::resource('/stock-transfer-print', StockTransferPrintController::class)->except(['destroy']);
        // stock transfer approval
        Route::resource('/stock-transfer-approval', StockTransferApprovalServerSideController::class)->except(['create','store','edit','destroy']);
        // stock transfer received
        Route::resource('/stock-transfer-received', StockTransferReceivedServerSideController::class)->except(['create','store','edit','destroy']);

        // stock assembly
        Route::resource('/stock-assembly', StockAssemblyServerSideController::class)->except(['destroy']);
        Route::resource('/stock-assembly-print', StockAssemblyPrintController::class)->except(['destroy']);

        // stock disassembly
        Route::resource('/stock-disassembly', StockDisAssemblyServerSideController::class)->except(['destroy']);
        Route::resource('/stock-disassembly-print', StockDisAssemblyPrintController::class)->except(['destroy']);

        // proses tagihan supplier
        Route::get('/tagihan-supplier/rm', [TagihanSupplierServerSideController::class, 'rmTagihanSupplier']);
        Route::resource('/tagihan-supplier', TagihanSupplierServerSideController::class)->except(['destroy']);
        Route::post('/tagihan-supplier/download-rpt', [TagihanSupplierServerSideController::class, 'downloadRpt']);
        Route::get('/tagihan-supplier-xlsx/{date_start}/{date_end}', function (string $date_start, string $date_end) {
            return Excel::download(new TagihanSupplierExport($date_start, $date_end), 'tagihan-supplier.xlsx');
        });

        // payment voucher
        Route::resource('/payment-voucher', PaymentVoucherServerSideController::class)->except(['destroy']);
        // payment voucher approval
        Route::resource('/payment-voucher-approval', PaymentVoucherApprovalServerSideController::class)->except(['create','store','edit','destroy']);

        // payment receipt
        Route::resource('/payment-receipt', PaymentReceiptServerSideController::class)->except(['destroy']);
        // Route::resource('/payment-receipt', PaymentReceiptServerSideControllerGJ::class)->except(['destroy']);

        // invoice
        Route::post('/invoice/rpt', [InvoiceServerSideController::class, 'rptInvoice']);
        Route::get('/invoice-xlsx/{start_date}/{end_date}', function (
            string $start_date,
            string $end_date) {
            return Excel::download(new InvoiceExport($start_date,$end_date), 'invoice.xlsx');
        });
        // Route::get('/invoice-print-to-xlsx/{inv_id}', function (
        //     string $inv_id) {
        //         info($inv_id);
        //     return Excel::download(new Invoice2Export($inv_id), 'invoice2.xlsx');
        // });

        Route::resource('/invoice/tax-inv', InvoiceTaxInvController::class)->except(['index','create','store','destroy']);
        Route::resource('/invoice', InvoiceServerSideController::class)->except(['destroy']);
        Route::put('/invoice-hefo/{id}', [InvoiceServerSideController::class, 'update_hefo']);
        Route::get('/invoice-print', InvoicePrintController::class);
        // invoice

        // invoice approval
        Route::resource('/invoice-approval', InvoiceApprovalController::class)->except(['create','store','edit','destroy']);

        // general journal
        Route::post('/general-journal/gj-dt', [GeneralJournalServerSideController::class, 'index']);
        Route::get('/general-journal/view-doc', [GeneralJournalServerSideController::class, 'view_doc']);
        Route::resource('/general-journal', GeneralJournalServerSideController::class)->except(['destroy']);
        // Route::resource('/general-journal-approval', GeneralJournalApprovalServerSideController::class)->except(['create','store','edit','destroy']);

        // lokal journal
        Route::post('/lokal-journal/lk-dt', [LokalJournalServerSideController::class, 'index']);
        Route::get('/lokal-journal/view-doc', [LokalJournalServerSideController::class, 'view_doc']);
        Route::resource('/lokal-journal', LokalJournalServerSideController::class)->except(['destroy']);
        // Route::resource('/lokal-journal-approval', LokalJournalApprovalServerSideController::class)->except(['create','store','edit','destroy']);

        // payment plan
        Route::resource('/payment-plan', PaymentPlanServerSideController::class)->except(['destroy']);
        Route::resource('/payment-plan-ro', PaymentPlanPerRCServerSideController::class)->except(['index','create','store','destroy']);

        // acceptance plan
        Route::resource('/acceptance-plan', AcceptancePlanServerSideController::class)->except(['destroy']);
        Route::resource('/acceptance-plan-inv', AcceptancePlanPerInvServerSideController::class)->except(['index','create','store','destroy']);

        // purchase inquiry
        Route::resource('/purchase-inquiry', PurchaseInquiryServerSideController::class)->except(['destroy']);
        Route::resource('/print-purchase-inquiry', PurchaseInquiryPrintController::class)->except(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // kwitansi
        Route::post('/kwitansi/rpt', [KwitansiServerSideController::class, 'rptKwitansi']);
        Route::get('/kwitansi-xlsx/{start_date}/{end_date}', function (
            string $start_date,
            string $end_date) {
            return Excel::download(new KwitansiExport($start_date,$end_date), 'kwitansi.xlsx');
        });

        Route::resource('/kwitansi', KwitansiServerSideController::class)->except(['destroy']);
        Route::put('/kwitansi-hefo/{id}', [KwitansiServerSideController::class, 'update_hefo']);
        Route::get('/kwitansi-print', KwitansiPrintController::class);
        // kwitansi

        // kwitansi approval
        Route::resource('/kwitansi-approval', KwitansiApprovalController::class)->except(['create','store','edit','destroy']);

        // stock-adjustment
        Route::resource('/stock-adjustment', StockAdjustmentServerSideController::class)->except(['destroy']);
        Route::resource('/stock-adjustment-print', StockAdjustmentPrintController::class)->except(['destroy']);
    }
);

Route::group(
    [
        'prefix' => ENV('REPORT_FOLDER_NAME'),
        'middleware' => ['forceHttps', 'auth', 'valdUser']
    ],
    function () {
        $date_xls=date_create(now());

        // master inventory
        Route::resource('/master-inventory', ReportMasterInventoryController::class)->except(['show','edit','update','destroy']);
        Route::get('/master-inventory-xlsx/{branch_id}/{brand_id}/{oh_is_zero}', function (string $branch_id,string $brand_id,string $oh_is_zero) use($date_xls) {
            return Excel::download(new MasterInventoryExport($branch_id,$brand_id,$oh_is_zero),'master-inventory-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // inventory over stock / under stock
        Route::resource('/inventory-over-stock-under-stock', ReportInventoryOverStockUnderStockController::class)->except(['show','edit','update','destroy']);
        Route::get('/inventory-over-stock-under-stock-xlsx/{branch_id}', function (string $branch_id) use($date_xls) {
            return Excel::download(new InventoryOverStockUnderStockExport($branch_id), 'inventory-over-stock-under-stock-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // summary stock per gudang per merk
        Route::resource('/summary-stock-per-gudang-per-merk', ReportSummaryStockPerGudangPerMerkController::class)->except(['show','edit','update','destroy']);
        Route::get('/summary-stock-per-gudang-per-merk-xlsx/{branch_id}/{brand_id}/{date}', function (string $branch_id,string $brand_id,string $date) use($date_xls) {
            return Excel::download(new SummaryStockPerGudangPerMerkExport($branch_id,$brand_id,$date), 'summary-stock-per-gudang-per-merk-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // pergerakan barang
        Route::resource('/pergerakan-barang', ReportMovementOfPartsController::class)->except(['show','edit','update','destroy']);
        Route::get('/pergerakan-barang-xlsx/{branch_id}/{date_start}/{date_end}', function (string $branch_id,string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportMovementOfPartsExport($branch_id,$date_start,$date_end), 'pergerakan-barang-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // perubahan cost rata-rata
        Route::resource('/perubahan-cost-rata-rata', ReportChangeInAvgCostController::class)->except(['show','edit','update','destroy']);
        Route::get('/perubahan-cost-rata-rata-xlsx/{date_start}/{date_end}', function (string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportChangeInAvgCostExport($date_start,$date_end), 'perubahan-cost-rata-rata-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // outstanding purchase order
        Route::resource('/outstanding-purchase-order-per-pn', ReportOutstandingPurchaseOrderController::class)->except(['show','edit','update','destroy']);
        Route::get('/outstanding-purchase-order-per-pn-xlsx/{branch_id}/{year_id}', function (string $branch_id,string $year_id) use($date_xls) {
            return Excel::download(new OutstandingPurchaseOrderExport($branch_id,$year_id), 'outstanding-purchase-order-per-pn-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // stock inventory accuration per branch
        Route::resource('/stock-inventory-accuration-per-branch', ReportStockInventoryAccPerBranchController::class)->except(['show','edit','update','destroy']);
        Route::get('/stock-inventory-accuration-per-branch-xlsx/{branch_id}/{year_id}', function (string $branch_id,string $year_id) use($date_xls) {
            return Excel::download(new StockInventoryAccPerBranchExport($branch_id,$year_id), 'stock-inventory-accuration-per-branch-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // sales per branch per customer
        Route::resource('/sales-per-branch-per-customer', ReportSalesPerBranchPerCustomerController::class)->except(['show','edit','update','destroy']);
        Route::get('/sales-per-branch-per-customer-xlsx/{branch_id}/{date_start}/{date_end}/{lokal_input}/{customer_id}', function (
            string $branch_id,
            string $date_start,
            string $date_end,
            string $lokal_input,
            string $customer_id) use($date_xls) {
            return Excel::download(new SalesPerBranchPerCustomerExport($branch_id,$date_start,$date_end,$lokal_input,$customer_id),
                'sales-per-branch-per-customer-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // customer payment status
        Route::resource('/customer-payment-status', CustomerPaymentStatusController::class)->except(['show','edit','update','destroy']);
        Route::get('/customer-payment-status-xlsx/{branch_id}/{date_start}/{date_end}/{lokal_input}', function (
            string $branch_id,
            string $date_start,
            string $date_end,
            string $lokal_input) use($date_xls) {
            return Excel::download(new CustomerPaymentStatusExport($branch_id, $date_start, $date_end, $lokal_input),
                'customer-payment-status-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // overdue receivables per branch
        Route::resource('/overdue-receivables-per-branch', ReportOverdueReceivablesPerBranchController::class)->except(['show','edit','update','destroy']);
        Route::get('/overdue-receivables-per-branch-xlsx/{branch_id}/{lokal_input}', function (
            string $branch_id,
            string $lokal_input) use($date_xls) {
            return Excel::download(new OverdueReceivablesPerBranchExport($branch_id,$lokal_input),
                'overdue-receivables-per-branch-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // summary analisa piutang per cabang
        Route::resource('/analyze-receivables-summary-per-branch', ReportAlzReceivablesSummPerBranchController::class)->except(['show','edit','update','destroy']);
        Route::get('/analyze-receivables-summary-per-branch-xlsx/{branch_id}', function (
            string $branch_id) use($date_xls) {
            return Excel::download(new AlzReceivablesSummPerBranchExport($branch_id),
                'analyze-receivables-summary-per-branch-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // sales per faktur per sales order
        Route::resource('/sales-per-faktur-per-sales-order', ReportSalesPerFakturPerSalesOrderController::class)->except(['show','edit','update','destroy']);
        Route::get('/sales-per-faktur-per-sales-order-xlsx/{date_start}/{date_end}/{lokal_input}', function (
            string $date_start,
            string $date_end,
            string $lokal_input) use($date_xls) {
            return Excel::download(new ReportSalesPerFakturPerSalesOrderExport($date_start,$date_end,$lokal_input),
                'sales-per-faktur-per sales-order-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // penjualan per customer per tahun - pending
        Route::resource('/penjualan-per-customer-per-tahun', ReportPenjualanPerCustomerPerTahunController::class)->except(['show','edit','update','destroy']);

        // penjualan per customer per parts no (fk & np)
        Route::resource('/penjualan-per-customer-per-parts-no', ReportPenjualanPerCustomerPerPartsNoController::class)->except(['show','edit','update','destroy']);
        Route::get('/penjualan-per-customer-per-parts-no-xlsx/{customer_id}/{date_start}/{date_end}/{lokal_input}', function (
            string $customer_id,
            string $date_start,
            string $date_end,
            string $lokal_input) use($date_xls) {
            return Excel::download(new ReportPenjualanPerCustomerPerPartsNoExport($customer_id,$date_start,$date_end,$lokal_input),
                'penjualan-per-customer-per-parts-no-fk-np-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // penjualan per customer per parts no (so & sj)
        Route::resource('/penjualan-per-customer-per-parts-no-so-sj', ReportPenjualanPerCustomerPerPartsNoSoSjController::class)->except(['show','edit','update','destroy']);
        Route::get('/penjualan-per-customer-per-parts-no-so-sj-xlsx/{customer_id}/{date_start}/{date_end}/{lokal_input}', function (
            string $customer_id,
            string $date_start,
            string $date_end,
            string $lokal_input) use($date_xls) {
            return Excel::download(new ReportPenjualanPerCustomerPerPartsNoSoSjExport($customer_id,$date_start,$date_end,$lokal_input),
                'penjualan-per-customer-per-parts-no-so-sj-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // summary sales per branch per salesman
        Route::resource('/summary-sales-per-branch-per-salesman', ReportSummarySalesPerBranchPerSalesmanController::class)->except(['show','edit','update','destroy']);
        Route::get('/summary-sales-per-branch-per-salesman-xlsx/{date_start}/{date_end}/{lokal_input}', function (
            string $date_start,
            string $date_end,
            string $lokal_input) use($date_xls) {
            return Excel::download(new ReportSummarySalesPerBranchPerSalesmanExport($date_start,$date_end,$lokal_input),
                'summary-sales-per-branch-per-salesman-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // summary penjualan per branch per brand
        Route::resource('/summary-penjualan-per-branch-per-brand', ReportSummaryPenjualanPerBranchPerBrandController::class)->except(['show','edit','update','destroy']);
        Route::get('/summary-penjualan-per-branch-per-brand-xlsx/{date_start}/{date_end}/{lokal_input}', function (
            string $date_start,
            string $date_end,
            string $lokal_input) use($date_xls) {
            return Excel::download(new ReportSummarySalesPerBranchPerBrandExport($date_start,$date_end,$lokal_input),
                'summary-penjualan-per-branch-per-brand-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // sales actual vs target per branch
        Route::resource('/sales-actual-vs-target-per-branch', ReportSalesActualVsTargetPerBranchController::class)->except(['show','edit','update','destroy']);
        Route::get('/sales-actual-vs-target-per-branch-xlsx/{period_year}', function (string $period_year) use($date_xls) {
            return Excel::download(new ReportSalesActualVsTargetPerBranchExport($period_year),
                'sales-actual-vs-target-per-branch-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // sales target per branch
        Route::resource('/sales-target-per-branch', ReportSalesTargetPerBranchController::class)->except(['show','edit','update','destroy']);
        Route::get('/sales-target-per-branch-xlsx/{period_year}', function (string $period_year) use($date_xls) {
            return Excel::download(new ReportSalesTargetPerBranchExport($period_year),
                'sales-target-per-branch-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // sales target customer per branch
        Route::resource('/sales-target-customer-per-branch', ReportSalesTargetCustomerPerBranchController::class)->except(['show','edit','update','destroy']);
        Route::get('/sales-target-customer-per-branch-xlsx/{period_year}', function (string $period_year) use($date_xls) {
            return Excel::download(new ReportSalesTargetCustomerPerBranchExport($period_year),
                'sales-target-customer-per-branch-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // sales per customer per year
        Route::resource('/sales-per-cust-per-year', ReportSalesPerCustomerPerYearController::class)->except(['show','edit','update','destroy']);
        Route::get('/sales-per-cust-per-year-xlsx/{period_year}/{lokal_input}', function (string $period_year,string $lokal_input) use($date_xls) {
            return Excel::download(new ReportSalesPerCustomerPerYearExport($period_year,$lokal_input),
                'sales-per-cust-per-year-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // sales per customer detail faktur
        Route::resource('/penjualan-per-customer-detail-faktur', ReportPenjualanPerCustomerDetailFakturController::class)->except(['show','edit','update','destroy']);
        Route::get('/penjualan-per-customer-detail-faktur-xlsx/{cust_id}/{date_start}/{date_end}', function (string $cust_id,string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportPenjualanPerCustomerDetailFakturExport($cust_id,$date_start,$date_end),
                'penjualan-per-customer-detail-faktur-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // retur penjualan detail
        Route::resource('/retur-penjualan-detail', ReportReturPenjualanDetailController::class)->except(['show','edit','update','destroy']);
        Route::get('/retur-penjualan-detail-xlsx/{lokal_input}/{date_start}/{date_end}', function (string $lokal_input,string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportReturPenjualanDetailExport($lokal_input,$date_start,$date_end),
                'retur-penjualan-detail-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // summary retur penjualan
        Route::resource('/summary-retur-penjualan', ReportSummaryReturPenjualanController::class)->except(['show','edit','update','destroy']);
        Route::get('/summary-retur-penjualan-xlsx/{lokal_input}/{date_start}/{date_end}', function (string $lokal_input,string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportSummaryReturPenjualanExport($lokal_input,$date_start,$date_end),
                'summary-retur-penjualan-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // purchase retur
        Route::resource('/purchase-retur-rpt', ReportPurchaseReturController::class)->except(['show','edit','update','destroy']);
        Route::get('/purchase-retur-rpt-xlsx/{date_start}/{date_end}', function (string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportPurchaseReturExport($date_start,$date_end),
                'purchase-retur-rpt-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // purchase per supplier per branch
        Route::resource('/purchase-per-supplier-per-cabang', ReportPurchasePerSupplierPerBranchController::class)->except(['show','edit','update','destroy']);
        Route::get('/purchase-per-supplier-per-cabang-xlsx/{branch_id}/{date_start}/{date_end}/{supplier_id}', 
            function (string $branch_id,string $date_start,string $date_end,string $supplier_id) use($date_xls) {
            return Excel::download(new ReportPurchasePerSupplierPerBranchExport($branch_id,$date_start,$date_end,$supplier_id),
                'purchase-per-supplier-per-cabang-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // purchase per supplier per year
        Route::resource('/purchase-per-supplier-per-year', ReportPurchasePerSupplierPerYearController::class)->except(['show','edit','update','destroy']);
        Route::get('/purchase-per-supplier-per-year-xlsx/{period_year}', function (string $period_year) use($date_xls) {
            return Excel::download(new ReportPurchasePerSupplierPerYearExport($period_year),
                'purchase-per-supplier-per-year-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // purchase per supplier per parts-no
        Route::resource('/purchase-per-supplier-per-parts-no', ReportPurchasePerSupplierPerPartsNoController::class)->except(['show','edit','update','destroy']);
        Route::get('/purchase-per-supplier-per-parts-no-xlsx/{date_start}/{date_end}', function (string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportPurchasePerSupplierPerPartsNoExport($date_start,$date_end),
                'purchase-per-supplier-per-parts-no-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // purchase summary per supplier
        Route::resource('/purchase-summary-per-supplier', ReportPurchaseSummaryPerSupplierController::class)->except(['show','edit','update','destroy']);
        Route::get('/purchase-summary-per-supplier-xlsx/{branch_id}/{date_start}/{date_end}', function (string $branch_id,string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportPurchaseSummaryPerSupplierExport($branch_id,$date_start,$date_end),
                'purchase-summary-per-supplier-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // purchase summary per branch per brand
        Route::resource('/purchase-summary-per-branch-per-brand', ReportPurchaseSummaryPerBranchPerBrandController::class)->except(['show','edit','update','destroy']);
        Route::get('/purchase-summary-per-branch-per-brand-xlsx/{date_start}/{date_end}', function (string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportPurchaseSummaryPerBranchPerBrandExport($date_start,$date_end),
                'purchase-summary-per-branch-per-brand-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // outstanding purchase order
        Route::resource('/outstanding-purchase-order-ps', ReportOutstandingPurchaseOrderPSController::class)->except(['show','edit','update','destroy']);
        Route::get('/outstanding-purchase-order-ps-xlsx/{date_start}/{date_end}', function (string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportOutstandingPurchaseOrderPSExport($date_start,$date_end),
                'outstanding-purchase-order-ps-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // debt overdue per branch
        Route::resource('/debt-overdue-per-branch', ReportDebtOverduePerBranchController::class)->except(['show','edit','update','destroy']);
        Route::get('/debt-overdue-per-branch-xlsx/{branch_id}', function (string $branch_id) use($date_xls) {
            return Excel::download(new ReportDebtOverduePerBranchExport($branch_id),
                'debt-overdue-per-branch-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // analyze debt summary per branch
        Route::resource('/analyze-debt-summary-per-branch', ReportAnalyzeDebtSummaryPerBranchController::class)->except(['show','edit','update','destroy']);
        Route::get('/analyze-debt-summary-per-branch-xlsx/{branch_id}', function (string $branch_id) use($date_xls) {
            return Excel::download(new ReportAnalyzeDebtSummaryPerBranchExport($branch_id),
                'analyze-debt-summary-per-branch-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // report finance transaction per account
        Route::resource('/rpt-finance-transaction-per-account', ReportFinanceTransactionPerAccountController::class)->except(['show','edit','update','destroy']);
        Route::get('/rpt-finance-transaction-per-account-xlsx/{coa_id}/{date_start}/{date_end}', function (string $coa_id,string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportFinanceTransactionPerAccountExport($coa_id,$date_start,$date_end),
                'rpt-finance-transaction-per-account-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // report finance journal
        Route::resource('/rpt-finance-journal', ReportFinanceJournalController::class)->except(['show','edit','update','destroy']);
        Route::get('/rpt-finance-journal-xlsx/{journal_no}', function (string $journal_no) use($date_xls) {
            return Excel::download(new ReportFinanceJournalExport($journal_no),
                'rpt-finance-journal-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // report finance transaction journal
        Route::resource('/rpt-finance-transaction-journal', ReportFinanceTransactionJournalController::class)->except(['show','edit','update','destroy']);
        Route::get('/rpt-finance-transaction-journal-xlsx/{lokal_input}/{branch_id}/{date_start}/{date_end}',
            function (string $lokal_input,string $branch_id,string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportFinanceTransactionJournalExport($lokal_input,$branch_id,$date_start,$date_end),
                'rpt-finance-transaction-journal-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // report finance general ledger
        Route::resource('/rpt-finance-general-ledger', ReportFinanceGeneralLedgerController::class)->except(['show','edit','update','destroy']);
        Route::get('/rpt-finance-general-ledger-xlsx/{coa_id}/{branch_id}/{date_start}/{date_end}',
            function (string $coa_id,string $branch_id,string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportFinanceGeneralLedgerExport($coa_id,$branch_id,$date_start,$date_end),
                'rpt-finance-general-ledger-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // report finance operating expenses
        Route::resource('/rpt-finance-operating-expenses', ReportFinanceOperatingExpensesController::class)->except(['show','edit','update','destroy']);
        Route::get('/rpt-finance-operating-expenses-xlsx/{lokal_input}/{branch_id}/{month_id}/{year_id}',
            function (string $lokal_input,string $branch_id,string $month_id,string $year_id) use($date_xls) {
            return Excel::download(new ReportFinanceOperatingExpensesExport($lokal_input,$branch_id,$month_id,$year_id),
                'rpt-finance-operating-expenses-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // report finance income statement
        Route::resource('/rpt-finance-income-statement', ReportFinanceIncomeStatementController::class)->except(['show','edit','update','destroy']);
        Route::get('/rpt-finance-income-statement-xlsx/{lokal_input}/{branch_id}/{month_id}/{year_id}',
            function (string $lokal_input,string $branch_id,string $month_id,string $year_id) use($date_xls) {
            return Excel::download(new ReportFinanceIncomeStatementExport($lokal_input,$branch_id,$month_id,$year_id),
                'rpt-finance-income-statement-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // balance sheet
        Route::resource('/rpt-balance-sheet', ReportBalanceSheetController::class)->except(['show','edit','update','destroy']);
        Route::get('/rpt-balance-sheet-xlsx/{period_year}', function (string $period_year) use($date_xls) {
            return Excel::download(new ReportBalanceSheetExport($period_year),
                'balance-sheet-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // cash flow
        Route::resource('/rpt-cash-flow', ReportCashFlowController::class)->except(['show','edit','update','destroy']);
        Route::get('/rpt-cash-flow-xlsx/{period}/{bank_id}', function (string $period,string $bank_id) use($date_xls) {
            return Excel::download(new ReportCashFlowExport($period, $bank_id), 'cash-flow-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // cash flow - dbg
        Route::resource('/rpt-cash-flow-dbg', ReportCashFlowDbgController::class)->except(['show','edit','update','destroy']);
        Route::get('/rpt-cash-flow-xlsx-dbg/{period}/{bank_id}', function (string $period,string $bank_id) use($date_xls) {
            return Excel::download(new ReportCashFlowDbgExport($period, $bank_id), 'cash-flow-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // supplier payment status (1)
        Route::resource('/purchase-supplier-payment-status', ReportPurchaseSupplierPaymentStatusController::class)->except(['show','edit','update','destroy']);
        Route::get('/purchase-supplier-payment-status-xlsx/{branch_id}/{date_start}/{date_end}', function (string $branch_id,string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportPurchaseSupplierPaymentStatusExport($branch_id,$date_start,$date_end),
                'purchase-supplier-payment-status-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // supplier payment status (2)
        Route::resource('/purchase-supplier-payment-status-02', ReportPurchaseSupplierPaymentStatus02Controller::class)->except(['show','edit','update','destroy']);
        Route::get('/purchase-supplier-payment-status-02-xlsx/{branch_id}/{date_start}/{date_end}', function (string $branch_id,string $date_start,string $date_end) use($date_xls) {
            return Excel::download(new ReportPurchaseSupplierPaymentStatus02Export($branch_id,$date_start,$date_end),
                'purchase-supplier-payment-status-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // inventory per merk per part no
        Route::resource('/inventory-per-merk-per-part-no', ReportInventoryPerMerkPerPartNoController::class)->except(['show','edit','update','destroy']);
        Route::get('/inventory-per-merk-per-part-no-xlsx/{journal_date}/{part_no}/{part_name}/{brand_id}/{branch_id}', function (
            string $journal_date,
            string $part_no,
            string $part_name,
            string $brand_id,
            string $branch_id) {
            return Excel::download(new InventoryPerMerkPerPartNoExport($journal_date,$part_no,$part_name,$brand_id,$branch_id), 'inventory-per-merk-per-part-no.xlsx');
        });

        // inventory per gudang per part no
        Route::resource('/inventory-per-gudang-per-part-no', ReportInventoryPerGudangPerPartNoController::class)->except(['show','edit','update','destroy']);
        Route::get('/inventory-per-gudang-per-part-no-xlsx/{journal_date}/{part_no}/{part_name}/{brand_id}/{branch_id}', function (
            string $journal_date,
            string $part_no,
            string $part_name,
            string $brand_id,
            string $branch_id) {
            return Excel::download(new InventoryPerGudangPerPartNoExport($journal_date,$part_no,$part_name,$brand_id,$branch_id), 'inventory-per-gudang-per-part-no.xlsx');
        });

        // summary stock per merk per gudang
        Route::resource('/summary-stock-per-merk-per-gudang', ReportSummaryStockPerMerkPerGudangController::class)->except(['show','edit','update','destroy']);
        Route::get('/summary-stock-per-merk-per-gudang-xlsx/{date_start}/{date_end}', function (
            string $date_start,
            string $date_end) {
            return Excel::download(new SummaryStockPerMerkPerGudangExport($date_start,$date_end), 'summary-stock-per-merk-per-gudang.xlsx');
        });

        // kartu hutang
        Route::resource('/rpt-finance-kartu-hutang', ReportKartuHutang::class)->except(['show','edit','update','destroy']);
        Route::get('/rpt-finance-kartu-hutang-xlsx/{supplier_id}/{date_start}/{date_end}/{branch_id}', 
            function (string $supplier_id, string $date_start, string $date_end, string $branch_id) use($date_xls) {
            return Excel::download(new ReportFinanceKartuHutangExport($supplier_id, $date_start, $date_end, $branch_id),
                'rpt-finance-kartu-hutang-'.date_format($date_xls,"YmdHis").'.xlsx');
        });

        // kartu piutang
        Route::resource('/rpt-finance-kartu-piutang', ReportKartuPiutang::class)->except(['show','edit','update','destroy']);
        Route::get('/rpt-finance-kartu-piutang-xlsx/{customer_id}/{date_start}/{date_end}', 
            function (string $customer_id, string $date_start, string $date_end) use($date_xls) {
            return Excel::download(new ReportFinanceKartuPiutangExport($customer_id, $date_start, $date_end),
                'rpt-finance-kartu-piutang-'.date_format($date_xls,"YmdHis").'.xlsx');
        });
    }
);

Route::group(
    [
        'prefix' => 'admin',
        'middleware' => ['forceHttps', 'auth', 'valdUser']
    ],
    function () {
        // country
        Route::get('/country/country-export-xlsx', function () {
            return Excel::download(new CountryExport, 'country.xlsx');
        });
        Route::post('/country/country-import', CountryImportController::class);
        Route::resource('/country', CountryController::class)->except(['destroy']);

        // province
        Route::get('/province/province-export-xlsx', function () {
            return Excel::download(new ProvinceExport, 'province.xlsx');
        });
        Route::post('/province/province-import', ProvinceImportController::class);
        Route::resource('/province', ProvinceController::class)->except(['destroy']);

        // city
        Route::get('/city/city-export-xlsx', function () {
            return Excel::download(new CityExport, 'city.xlsx');
        });
        Route::post('/city/city-import', CityImportController::class);
        Route::resource('/city', CityController::class)->except(['destroy']);

        // district
        Route::get('/district/district-export-xlsx', function () {
            return Excel::download(new DistrictExport, 'district.xlsx');
        });
        Route::post('/district/find-district', [DistrictController::class, 'index']);
        Route::get('/district/find-district', [DistrictController::class, 'index']);
        Route::post('/district/district-import', DistrictImportController::class);
        Route::resource('/district', DistrictController::class)->except(['destroy']);

        // sub district
        Route::get('/subdistrict/sub-district-export-xlsx', function () {
            return Excel::download(new SubDistrictExport, 'sub-district.xlsx');
        });
        Route::post('/subdistrict/find-sub-district', [SubDistrictController::class, 'index']);
        Route::get('/subdistrict/find-sub-district', [SubDistrictController::class, 'index']);
        Route::post('/subdistrict/sub-district-import', SubDistrictImportController::class);
        Route::resource('/subdistrict', SubDistrictController::class)->except(['destroy']);

        // brand - global
        Route::resource('/brand', BrandController::class)->except(['destroy']);
        // brand type
        Route::resource('/brand-type', BrandTypeController::class)->except(['destroy']);
        // currency - global
        Route::resource('/currency', CurrencyController::class)->except(['destroy']);
        // gender - global
        Route::resource('/gender', GenderController::class)->except(['destroy']);
        // entity-type - global
        Route::resource('/entity-type', EntityTypeController::class)->except(['destroy']);
        // supplier-type - global
        Route::resource('/supplier-type', SupplierTypeController::class)->except(['destroy']);
        // Part-type - global
        Route::resource('/part-type', PartTypeController::class)->except(['destroy']);
        // Part-category - global
        Route::resource('/part-category', PartCategoryController::class)->except(['destroy']);
        // weight-type - global
        Route::resource('/weight-type', WeightTypeController::class)->except(['destroy']);
        // delivery-type - global
        Route::resource('/delivery-type', DeliveryTypeController::class)->except(['destroy']);
        // quantity-type - global
        Route::resource('/quantity-type', QuantityTypeController::class)->except(['destroy']);
        // employee-section - global
        Route::resource('/employee-section', EmployeeSectionController::class)->except(['destroy']);
        // courier-type - global
        Route::resource('/courier-type', CourierTypeController::class)->except(['destroy']);
        // payment-reference - global
        Route::resource('/payment-ref', PaymentReferenceController::class)->except(['destroy']);

        // master branch
        Route::get('/branch/branch-export-xlsx', function () {
            return Excel::download(new BranchExport, 'branch.xlsx');
        });
        Route::post('/branch/branch-import', BranchImportController::class);
        Route::resource('/branch', BranchController::class)->except(['destroy']);

        // master salesman
        Route::get('/salesman/salesman-export-xlsx', function () {
            return Excel::download(new SalesmanExport, 'salesman.xlsx');
        });
        Route::post('/salesman/salesman-import', SalesmanImportController::class);
        Route::resource('/salesman', SalesmanController::class)->except([
            'show', 'destroy'
        ]);

        // master customer
        Route::get('/customer/customer-export-xlsx', function () {
            return Excel::download(new CustomerExport, 'customer.xlsx');
        });
        Route::post('/customer/customer-import', CustomerImportController::class);
        Route::resource('/customer', CustomerController::class)->except(['destroy']);

        // master customer shipment address
        Route::get('/customer-shipment-address/customer-shipment-address-export-xlsx', function () {
            return Excel::download(new CustomerShipmentExport, 'customer-shipment-address.xlsx');
        });
        Route::post('/customer-shipment-address/customer-shipment-address-import', CustomerShipmentImportController::class);
        Route::resource('/customer-shipment-address', CustomerShipmentController::class)->except(['show', 'destroy']);

        // master supplier
        Route::get('/supplier/supplier-export-xlsx', function () {
            return Excel::download(new SupplierExport, 'supplier.xlsx');
        });
        Route::post('/supplier/supplier-import', SupplierImportController::class);
        Route::resource('/supplier', SupplierController::class)->except(['destroy']);

        // master part
        Route::resource('/part', PartController::class)->except(['destroy']);

        // master courier
        Route::get('/courier/courier-export-xlsx', function () {
            return Excel::download(new CourierExport, 'courier.xlsx');
        });
        Route::post('/courier/courier-import', CourierImportController::class);
        Route::resource('/courier', CourierController::class)->except(['destroy']);

        // master company
        Route::get('/company/company-export-xlsx', function () {
            return Excel::download(new CompanyExport, 'company.xlsx');
        });
        Route::post('/company/company-import', CompanyImportController::class);
        Route::resource('/company', CompanyController::class)->except(['destroy']);

        // master - vat
        Route::resource('/vat', VatController::class)->except(['destroy']);

        // master - coa
        Route::resource('/coa', CoaController::class)->except(['destroy']);

        // user management
        Route::resource('/menu', MenuAccessController::class)->except([
            'create', 'store', 'show', 'destroy'
        ]);
        Route::resource('/user-access', UserAccessController::class)->except([
            'create', 'store', 'edit', 'destroy'
        ]);
        Route::resource('/user-management', UserManagementController::class)->except(['destroy']);

        // master - memo limit
        Route::resource('/memo-limit', MemoLimitController::class)->except(['destroy']);

        // tax-invoice
        Route::resource('/tax-invoice', TaxInvoiceController::class)->except(['destroy']);
        Route::post('/tax-invoice-cancel', [TaxInvoiceController::class, 'delFPno']);
        Route::post('/tax-invoice-search', [TaxInvoiceController::class, 'fpSearch']);

        // master - branch target
        Route::resource('/branch-target', BranchTargetController::class)->except(['destroy']);

        // master - salesman target
        Route::resource('/salesman-target', SalesmanTargetController::class)->except(['destroy']);

        // master - automatic journal
        Route::resource('/automatic-journal', AutomaticJournalController::class)->except(['create','store','destroy']);
    }
);

Route::group(
    [
        'prefix' => 'manual',
        'middleware' => ['forceHttps']
    ],
    function () {
        Route::resource('/manual-customer-import', ManualCustomerImportController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/manual-supplier-import', ManualSupplierImportController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
    }
);

Route::group(
    [
        'prefix' => 'op',
        'middleware' => ['forceHttps']
    ],
    function () {
        Route::resource('/synccountry', SyncCountryController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/syncprovince', SyncProvinceController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/synccity', SyncCityController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/syncsubdistrict', SyncSubDistrictController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
    }
);

Route::group(
    [
        'prefix' => 'oth',
        'middleware' => ['forceHttps']
    ],
    function () {
        Route::get('/migrate', function () {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            return 'MIGRATE DONE (code: '.$exitCode.')';
            // return $this->sendResponse('DONE (code: '.$exitCode.')');
        });
        Route::get('/cache-clear', function () {
            $exitCode = Artisan::call('cache:clear', []);
            return 'CACHE CLEAR DONE (code: '.$exitCode.')';
        });
        Route::get('/config-clear', function () {
            $exitCode = Artisan::call('config:clear', []);
            return 'CONFIG CLEAR DONE (code: '.$exitCode.')';
        });
        Route::get('/schedule-clear', function () {
            $exitCode = Artisan::call('schedule:clear-cache', []);
            return 'CLEAR SCHEDULE CACHE DONE (code: '.$exitCode.')';
        });
    }
);

Route::group(
    [
        'prefix' => 'dbg',
        'middleware' => ['forceHttps']
    ],
    function () {
        // Route::get('/part/json', [TestDatatableController::class, 'data'])->name('part.data');
        // Route::get('/part/{param?}', [TestDatatableController::class, 'index'])->name('part.index');
        // Route::post('/part-post', [TestDatatableController::class, 'store'])->name('part_post.store');

        // Route::get('/stock-master/json', [StockMasterDbgController::class, 'datas'])->name('stockmaster.data');
        Route::get('/stock-master-e/{param?}', [StockMasterDbgController::class, 'index'])->name('stockmasterdbg.index');
        Route::post('/stock-master-post-e', [StockMasterDbgController::class, 'store'])->name('stockmasterdbg.store');

        Route::resource('/gen-faktur', GenFakturController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/upd-journal-date', UpdJournalDateController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);

        Route::resource('/oh-fix', UpdOHController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);

        Route::resource('/gj-fix', UpdGJController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);

        Route::resource('/auth', AuthController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/remember', RememberController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/import-sub-district', ImportSubDistrictController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/menu', MenuController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/pdf', PdfController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/where', WhereController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::get('/doc', DocumentController::class);

        Route::resource('/doc_html', CreateWordController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);

        Route::get('/qty/upd-qty-per-branch', [UpdateQtyController::class, 'index_upd']);
        Route::resource('/qty', UpdateQtyController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);

        Route::resource('/stock-inventory', StockInventoryAccurationPerBranchController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/b-b', BeginningBalancePerMonthDbgController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);

        Route::get('/dt-now', function () {
            echo now().'<br/>';
            // $date = now();
            // echo base_path('public/assets/fonts/');

            // date_add($date, date_interval_create_from_date_string("-1 months"));
            // echo date_format($date,"Y-m-d").'<br/>';
        });

        Route::resource('/gen-json', JsonController::class)->except([
            'create', 'show', 'edit', 'update', 'destroy'
        ]);
        Route::resource('/quotation-dbg', QuotationServerSideDbgController::class)->except(['destroy']);
        Route::resource('/upd-avg', UpdAvgSOSJController::class)->except(['destroy']);
        Route::resource('/rpt-analyze-debt-summ', RptAnalizeDebtSummController::class)->except(['destroy']);

        // cash flow
        // $date_xls=date_create(now());
        // Route::resource('/gen-rpt-cash-flows', GenRptCashFlowController::class)->except(['destroy']);
        // Route::resource('/rpt-cash-flow-2026', ReportCashFlow2026Controller::class)->except(['show','edit','update','destroy']);
        // Route::get('/rpt-cash-flow-2026-xlsx/{period}/{bank_id}', function (string $period,string $bank_id) use($date_xls) {
        //     return Excel::download(new ReportCashFlow2026Export($period, $bank_id), 'cash-flow-'.date_format($date_xls,"YmdHis").'.xlsx');
        // });
        // cash flow

        Route::get('/2tables', function () {
            // return view('dbg.2tables');
            // echo base_path('public/assets/images/logo_UID.png');
            echo base_path('../koi-inventory/assets/images/logo_UID.png');
        });

        Route::get('/print-2-dot-matrix', function () {
            return view('dbg.print-to-dot-matrix');
        });

        Route::get('/scan-area/{fakturs}', function (string $fakturs) {
            return Excel::download(new ScanExport($fakturs), 'scan-area.xlsx');
        });
    }
);
