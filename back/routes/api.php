<?php

use App\Http\Controllers\Api\AdministradorController;
use App\Http\Controllers\Api\AluguelController;
use App\Http\Controllers\Api\AtendenteController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BairroController;
use App\Http\Controllers\Api\CepController;
use App\Http\Controllers\Api\CidadeController;
use App\Http\Controllers\Api\CnhController;
use App\Http\Controllers\Api\ContratoFrotaController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\FilialEnderecoController;
use App\Http\Controllers\Api\FilialController;
use App\Http\Controllers\Api\FuncionarioController;
use App\Http\Controllers\Api\GerenteComercialController;
use App\Http\Controllers\Api\LogradouroController;
use App\Http\Controllers\Api\LoteController;
use App\Http\Controllers\Api\MontadoraEnderecoController;
use App\Http\Controllers\Api\MontadoraController;
use App\Http\Controllers\Api\OficinaEnderecoController;
use App\Http\Controllers\Api\OficinaController;
use App\Http\Controllers\Api\PessoaFisicaController;
use App\Http\Controllers\Api\RelatorioController;
use App\Http\Controllers\Api\ServicoController;
use App\Http\Controllers\Api\UsuarioEnderecoController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\VeiculoController;
use App\Http\Controllers\Api\VendaController;
use App\Support\Roles;
use Illuminate\Support\Facades\Route;

// Autenticação (JWT + login Google) — ver back/CLAUDE.md, seção "Autenticação"
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/refresh', [AuthController::class, 'refresh']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::get('auth/google/redirect', [AuthController::class, 'googleRedirect']);
Route::get('auth/google/callback', [AuthController::class, 'googleCallback']);

// Cadastro público (sign-up). Precisa ficar fora do jwt.auth: é o único jeito
// de um cliente novo conseguir uma conta antes de ter qualquer token.
Route::post('usuarios', [UsuarioController::class, 'store']);

Route::middleware('jwt.auth')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);

    // Escrita restrita ao Administrador: gestão de frota/estrutura da locadora.
    Route::middleware('role:'.Roles::ADMINISTRADOR)->group(function () {
        Route::apiResource('filiais', FilialController::class)->except(['index', 'show']);
        Route::apiResource('montadoras', MontadoraController::class)->except(['index', 'show']);
        Route::apiResource('oficinas', OficinaController::class)->except(['index', 'show']);
        Route::apiResource('funcionarios', FuncionarioController::class)->except(['index', 'show']);
        Route::apiResource('gerentes-comerciais', GerenteComercialController::class)->except(['index', 'show']);
        Route::apiResource('administradores', AdministradorController::class)->except(['index', 'show']);
        Route::apiResource('atendentes', AtendenteController::class)->except(['index', 'show']);
        Route::apiResource('lotes', LoteController::class)->except(['index', 'show']);
        Route::apiResource('veiculos', VeiculoController::class)->except(['index', 'show']);
        Route::apiResource('servicos', ServicoController::class)->except(['index', 'show']);
    });

    // Escrita restrita a Gerente Comercial (ou Administrador): comercial/contratos/vendas.
    Route::middleware('role:'.Roles::GERENTE_COMERCIAL.','.Roles::ADMINISTRADOR)->group(function () {
        Route::patch('contratos-frota/{id}/encerrar', [ContratoFrotaController::class, 'encerrar']);
        Route::apiResource('contratos-frota', ContratoFrotaController::class)->except(['index', 'show']);
        Route::apiResource('vendas', VendaController::class)->except(['index', 'show']);
    });

    // Escrita restrita a Atendente (ou Administrador): entrega/devolução de veículo.
    Route::middleware('role:'.Roles::ATENDENTE.','.Roles::ADMINISTRADOR)->group(function () {
        Route::patch('alugueis/{id}/devolver', [AluguelController::class, 'devolver']);
        Route::apiResource('alugueis', AluguelController::class)->except(['index', 'show']);
    });

    // Leitura liberada para qualquer perfil autenticado nos recursos acima.
    Route::apiResource('filiais', FilialController::class)->only(['index', 'show']);
    Route::apiResource('montadoras', MontadoraController::class)->only(['index', 'show']);
    Route::apiResource('oficinas', OficinaController::class)->only(['index', 'show']);
    Route::apiResource('funcionarios', FuncionarioController::class)->only(['index', 'show']);
    Route::apiResource('gerentes-comerciais', GerenteComercialController::class)->only(['index', 'show']);
    Route::apiResource('administradores', AdministradorController::class)->only(['index', 'show']);
    Route::apiResource('atendentes', AtendenteController::class)->only(['index', 'show']);
    Route::apiResource('lotes', LoteController::class)->only(['index', 'show']);
    Route::apiResource('veiculos', VeiculoController::class)->only(['index', 'show']);
    Route::apiResource('servicos', ServicoController::class)->only(['index', 'show']);
    Route::apiResource('contratos-frota', ContratoFrotaController::class)->only(['index', 'show']);
    Route::apiResource('vendas', VendaController::class)->only(['index', 'show']);
    Route::apiResource('alugueis', AluguelController::class)->only(['index', 'show']);

    // Sem restrição extra de role: qualquer usuário autenticado (cadastro do
    // próprio perfil, endereços, telefones, dados de referência e relatórios).
    Route::apiResource('usuarios', UsuarioController::class)->except(['store']);
    Route::apiResource('cnhs', CnhController::class);
    Route::apiResource('pessoas-fisicas', PessoaFisicaController::class);
    Route::apiResource('empresas', EmpresaController::class);
    Route::apiResource('estados', EstadoController::class);
    Route::apiResource('cidades', CidadeController::class);
    Route::apiResource('bairros', BairroController::class);
    Route::apiResource('logradouros', LogradouroController::class);
    Route::apiResource('ceps', CepController::class);
    Route::apiResource('usuarios-enderecos', UsuarioEnderecoController::class);
    Route::apiResource('oficinas-enderecos', OficinaEnderecoController::class);
    Route::apiResource('montadoras-enderecos', MontadoraEnderecoController::class);
    Route::apiResource('filiais-enderecos', FilialEnderecoController::class);

    // Relatórios somente-leitura baseados em views do banco (docs/bdnormalizado/views.sql)
    Route::get('relatorios/frota-disponivel', [RelatorioController::class, 'frotaDisponivel']);
    Route::get('relatorios/ocupacao-frota', [RelatorioController::class, 'ocupacaoFrota']);
    Route::get('relatorios/contratos-frota', [RelatorioController::class, 'contratoFrotaResumo']);
    Route::get('relatorios/veiculos-manutencao', [RelatorioController::class, 'veiculosManutencao']);
});
