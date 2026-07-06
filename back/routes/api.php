<?php

use App\Http\Controllers\Api\AdministradorController;
use App\Http\Controllers\Api\AluguelController;
use App\Http\Controllers\Api\AtendenteController;
use App\Http\Controllers\Api\ContratoFrotaController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\FilialController;
use App\Http\Controllers\Api\FuncionarioController;
use App\Http\Controllers\Api\GerenteComercialController;
use App\Http\Controllers\Api\LoteController;
use App\Http\Controllers\Api\MontadoraController;
use App\Http\Controllers\Api\OficinaController;
use App\Http\Controllers\Api\PessoaFisicaController;
use App\Http\Controllers\Api\ServicoController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\VeiculoController;
use App\Http\Controllers\Api\VendaController;
use Illuminate\Support\Facades\Route;

Route::apiResource('filiais', FilialController::class);
Route::apiResource('montadoras', MontadoraController::class);
Route::apiResource('oficinas', OficinaController::class);
Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('funcionarios', FuncionarioController::class);
Route::apiResource('gerentes-comerciais', GerenteComercialController::class);
Route::apiResource('administradores', AdministradorController::class);
Route::apiResource('atendentes', AtendenteController::class);
Route::apiResource('lotes', LoteController::class);
Route::apiResource('veiculos', VeiculoController::class);
Route::apiResource('pessoas-fisicas', PessoaFisicaController::class);
Route::apiResource('empresas', EmpresaController::class);
Route::apiResource('contratos-frota', ContratoFrotaController::class);
Route::apiResource('servicos', ServicoController::class);
Route::apiResource('alugueis', AluguelController::class);
Route::apiResource('vendas', VendaController::class);
