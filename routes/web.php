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

Route::get('/', function () {
    return view('pedido');
})->name('pedido');

Route::post('/pagamento', function () {
    return view('pagamento');
})->name('pagamento');


Route::post('/', function () {
    return abort(404);
})->name('pedido');
Route::get('/pagamento', function () {
    return abort(404);
})->name('pagamento');


Route::get('/test', function () {
    $store = new Rede\Store('PV', 'TOKEN', Rede\Environment::production());

    // Configuração da loja em modo sandbox
    // $store = new \Rede\Store('PV', 'TOKEN', \Rede\Environment::sandbox());

    // Transação que será autorizada
    $transaction = (new Rede\Transaction(20.99, 'pedido' . time()))->creditCard(
        '5448280000000007',
        '235',
        '12',
        '2020',
        'John Snow'
    );

    // Autoriza a transação
    $transaction = (new Rede\eRede($store))->create($transaction);

    if ($transaction->getReturnCode() == '00') {
        printf("Transação autorizada com sucesso; tid=%s\n", $transaction->getTid());
    }
});
