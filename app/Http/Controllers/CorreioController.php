<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\Facades\Correio;
use App\Http\Requests\GetFrete as GetFreteRequest;

class CorreioController extends Controller
{
    public function getCep(Request $request)
    {
        $cep = $request->route('zipcode');

        return Correio::provider('sigep')->consultaCep($cep);
    }

    public function getFrete(GetFreteRequest $request)
    {
        return Correio::provider('calc_preco_prazo')->calcPrecoPrazo($request->all());
    }
}
