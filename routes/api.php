<?php

use App\Http\Controllers\CompanyBC\InCompany\CustomerController;
use App\Http\Controllers\CompanyBC\InCompany\PerformanceSummaryController;
use App\Http\Controllers\SalesBC\BySales\CommonSalesMetricSummaryController;
use App\Http\Middleware\RegisterSalesRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider and all of them will
  | be assigned to the "api" middleware group. Make something great!
  |
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/export-customer-to-csv', [CustomerController::class, 'exportCustomerToCsv']);
Route::post('/import-customer-from-csv', [CustomerController::class, 'importCustomerFromCsv']);
//
Route::get('/view-all-company-metric-summary', [PerformanceSummaryController::class, 'viewAllCompanyMetricSummary']);
Route::get('/view-all-sales-rank-summary', [PerformanceSummaryController::class, 'viewAllSalesRankSummary']);
Route::get('/view-all-sales-performance-metric-summary', [PerformanceSummaryController::class, 'viewAllSalesPerformanceMetricSummary']);
//
Route::get('/view-all-common-sales-metric-summary', [CommonSalesMetricSummaryController::class, 'viewAllCommonSalesMetricSummary']);
