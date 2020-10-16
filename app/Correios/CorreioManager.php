<?php

namespace App\Correios;

use App\Correios\Providers\Sigep;
use Illuminate\Support\Collection;
use App\Correios\Providers\CalcPrecoPrazo;

class CorreioManager 
{
    protected $availableProviders = [
        Sigep::class,
        CalcPrecoPrazo::class
    ];

    protected function getAvailableProviders(): Collection
    {
        return collect($this->availableProviders)->mapWithKeys(function ($class){
            $instance = app($class);
            return [$instance->getName() => $instance];
        });
    }

    public function provider($name)
    {
        return $this->getAvailableProviders()
            ->get($name);
    }
}