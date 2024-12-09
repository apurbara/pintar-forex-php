<?php

//use App\Http\Controllers\CompanyBC\InCompany\CustomerController;
//use App\Http\Controllers\CompanyBC\InCompany\PerformanceSummaryController;


use Company\Application\Controllers\CustomerController;
use Company\Application\Controllers\PerformanceSummaryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Manager\Application\Controllers\PerformanceSummaryController as PerformanceSummaryController2;
use Sales\Application\Controllers\FactFindingMetricAchievementController;
use Sales\Application\Controllers\GreetingMetricAchievementController;

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
Route::get('/manager/view-all-company-metric-summary', [PerformanceSummaryController2::class, 'viewAllCompanyMetricSummary']);
Route::get('/manager/view-all-sales-rank-summary', [PerformanceSummaryController2::class, 'viewAllSalesRankSummary']);
Route::get('/manager/view-all-sales-performance-metric-summary', [PerformanceSummaryController2::class, 'viewAllSalesPerformanceMetricSummary']);
//
//Route::get('/view-all-common-sales-metric-summary', [CommonSalesMetricSummaryController::class, 'viewAllCommonSalesMetricSummary']);
Route::get('/view-all-greeting-metric-achievement', [GreetingMetricAchievementController::class, 'viewAllGreetingMetricAchievement']);
Route::get('/view-all-fact-finding-metric-achievement', [FactFindingMetricAchievementController::class, 'viewAllFactFindingMetricAchievement']);
