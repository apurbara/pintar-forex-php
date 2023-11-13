<?php

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

Route::post('/company', function (Request $request) {
    return require __DIR__ .  '../../app/Http/GraphQL/CompanyBC/graphql.php';
});
Route::post('/sales', function (Request $request) {
    return require __DIR__ .  '../../app/Http/GraphQL/SalesBC/graphql.php';
});
Route::post('/manager', function (Request $request) {
    return require __DIR__ .  '../../app/Http/GraphQL/ManagerBC/graphql.php';
});
Route::post('/user', function (Request $request) {
    return require __DIR__ .  '../../app/Http/GraphQL/UserBC/graphql.php';
});
