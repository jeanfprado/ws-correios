<?php 

namespace App\Correios\Providers;

use GuzzleHttp\Client;

abstract class Provider
{
    /**
     * @var GuzzleHttp\Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

}