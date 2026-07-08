<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CotacaoRequest;
use App\Services\CotacaoService;

class PublicController extends Controller
{
    public function __construct(private readonly CotacaoService $cotacaoService)
    {
    }

    public function filiais()
    {
        return response()->json($this->cotacaoService->listarFiliais());
    }

    public function cotacoes(CotacaoRequest $request)
    {
        $cotacao = $this->cotacaoService->cotar($request->validated());

        if ($cotacao === null) {
            return response()->json(['message' => 'Filial não encontrada.'], 404);
        }

        return response()->json($cotacao);
    }
}
