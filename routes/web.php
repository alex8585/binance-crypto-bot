<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('add_vars')->group(function () {
    Route::get('/', 'StatisticsController@index')->name('circles');
    Route::get('/statistics/{id}', 'StatisticsController@statistics')->name('statistics');
    Route::get('/details/{symbol}', 'StatisticsController@details')->name('statistics_details');

    Route::get('/options/', 'OptionsController@index')->name('options_index');
    Route::put('/options/', 'OptionsController@update')->name('options_update');

    Route::get('/orders/', 'OrdersController@index')->name('orders_index');
    Route::get('/getorders/', 'OrdersController@getorders')->name('getorders');
    Route::post('/sellorders', 'OrdersController@sellorders')->name('orders_sellorders');

    Route::get('/balances/', 'BalancesController@index')->name('balances_index');

    Route::get('/orderbook/', 'OrderBookController@index')->name('orderbook_index');
    Route::get('/orderbook/{id}', 'OrderBookController@details')->name('orderbook_details');

    Route::get('/tvstatistics/', 'TvStatisticsController@index')->name('tv_statistics_index');
    Route::get('/tvstatistics/{id}', 'TvStatisticsController@statistics')->name('tv_statistics');
    Route::get('/tvstatistics-details/{id}', 'TvStatisticsController@details')->name('tv_statistics_details');

    Route::get('/balances-history', 'BalancesHistoryController@index')->name('balances_history');

    Route::get('/top-candidates', 'TopCandidatesController@index')->name('top_candidates_index');
});


Auth::routes([
    'login' => true,
    'logout' => true,
    'confirm' => false,
    'forgot' => false,
    'register' => false,
    'reset' => false,
    'verification' => false,
]);
