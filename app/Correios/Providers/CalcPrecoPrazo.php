<?php

namespace App\Correios\Providers;

use App\Contracts\CorreioProvider as CorreioProviderContract;

class CalcPrecoPrazo extends Provider implements CorreioProviderContract
{
    protected $response = [];

    protected $parseXML = [];

    protected $services = [
        'SEDEX' => '4014',
        'PAC' => '4510'
    ];

    public function getName(): string
    {
        return 'calc_preco_prazo';
    }

    public function calcPrecoPrazo(array $attributes)
    {
        $this->buildXMLCalcPrecoPrazo($attributes)
            ->parseXMLResponse();

        return $this->calcPrecoPrazoResponse();
    }

    protected function buildXMLCalcPrecoPrazo(array $attributes)
    {
        collect($this->services)->each(function ($code, $service) use ($attributes){
            $this->response[] = $this->client->post(
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
                                        <tem:nCdServico>'.$code.'</tem:nCdServico>
                                        <tem:sCepOrigem>'.$attributes['cep_origem'].'</tem:sCepOrigem>
                                        <tem:sCepDestino>'.$attributes['cep_destino'].'</tem:sCepDestino>
                                        <tem:nVlPeso>'.$attributes['peso'].'</tem:nVlPeso>
                                        <tem:nCdFormato>1</tem:nCdFormato>
                                        <tem:nVlComprimento>'.$attributes['comprimento'].'</tem:nVlComprimento>
                                        <tem:nVlAltura>'.$attributes['altura'].'</tem:nVlAltura>
                                        <tem:nVlLargura>'.$attributes['largura'].'</tem:nVlLargura>
                                        <tem:nVlDiametro>0</tem:nVlDiametro>
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
        });

        return $this;
    }

    protected function calcPrecoPrazoResponse()
    {
        return collect($this->parseXML)->map(function($response){
            $values = $response['CalcPrecoPrazoResponse']['CalcPrecoPrazoResult']['Servicos']['cServico'];

            return [
                'tipo' => array_search($values['Codigo'], $this->services),
                'valor'=> $values['Valor'],
                'prazo_entrega' => $values['PrazoEntrega'],
            ];
        });
    }

    protected function parseXMLResponse()
    {
        foreach($this->response as $response){
            $xml = $response->getBody()->getContents();

            $parse = simplexml_load_string(
                str_replace(['soap:'], null, $xml)
            );

            $this->parseXML[] = json_decode(json_encode($parse->Body), true);
        }

        return $this;
    }
}
