<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class CorreioController extends Controller
{
    public function getCep(Request $request)
    {
        $cep = $request->route('zipcode');

        $client = new Client();

        $response = $client->post('https://apps.correios.com.br/SigepMasterJPA/AtendeClienteService/AtendeCliente?wsdl', [
            'http_errors' => false,
            'body' => trim(
                '<x:Envelope
                    xmlns:x="http://schemas.xmlsoap.org/soap/envelope/"
                    xmlns:cli="http://cliente.bean.master.sigep.bsb.correios.com.br/">
                    <x:Header/>
                    <x:Body>
                        <cli:consultaCEP>
                            <cep>' . $cep . '</cep>
                        </cli:consultaCEP>
                    </x:Body>
                </x:Envelope>'
            ),
            'headers' => [
                'Content-Type' => 'text/xml; charset=utf-8'
            ]
        ]);

        $xml = $this->response->getBody()->getContents();

        $parse = simplexml_load_string(str_replace(['soap:', 'ns2:'], null, $xml));

        return json_encode($parse->Body->consultaCEPResponse->return);

    }

    public function getFrete(Request $request)
    {
        $client = new Client();

        $response = $client->post(
            'http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx?wsdl',
            [
                'http_errors' => false,
                'body' => trim('<x:Envelope
                            xmlns:x="http://schemas.xmlsoap.org/soap/envelope/"
                            xmlns:tem="http://tempuri.org/">
                            <x:Header/>
                            <x:Body>
                                <tem:CalcPrecoPrazo>
                                    <tem:nCdEmpresa></tem:nCdEmpresa>
                                    <tem:sDsSenha></tem:sDsSenha>
                                    <tem:nCdServico>4014</tem:nCdServico>
                                    <tem:sCepOrigem>29800000</tem:sCepOrigem>
                                    <tem:sCepDestino>29129338</tem:sCepDestino>
                                    <tem:nVlPeso>0,500</tem:nVlPeso>
                                    <tem:nCdFormato>1</tem:nCdFormato>
                                    <tem:nVlComprimento>24</tem:nVlComprimento>
                                    <tem:nVlAltura>24</tem:nVlAltura>
                                    <tem:nVlLargura>24</tem:nVlLargura>
                                    <tem:nVlDiametro>24</tem:nVlDiametro>
                                    <tem:sCdMaoPropria>N</tem:sCdMaoPropria>
                                    <tem:nVlValorDeclarado>0</tem:nVlValorDeclarado>
                                    <tem:sCdAvisoRecebimento>N</tem:sCdAvisoRecebimento>
                                </tem:CalcPrecoPrazo>
                            </x:Body>
                        </x:Envelope>'),
                'headers' => [
                    'Content-Type' => 'text/xml; charset=utf-8'
                ]
            ]
        );

        $xml = $response->getBody()->getContents();

        $parse = simplexml_load_string(str_replace(['soap:'], null, $xml));

        return json_encode($parse->Body->CalcPrecoPrazoResponse->CalcPrecoPrazoResult->Servicos->cServico);
    }
}
