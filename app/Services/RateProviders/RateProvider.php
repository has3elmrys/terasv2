<?php

namespace App\Services\RateProviders;

interface RateProvider
{
    public function name(): string;

    /**
     * @param  string[]  $codes
     * @return array<string,string> kode currency => rate_to_pivot (1 unit = berapa USD), plus key '_date' => tanggal kurs
     */
    public function fetch(array $codes): array;
}
