<?php

declare(strict_types=1);

namespace Bayeer;

class App
{
    public function sum(array $payload): array
    {
        $data = json_decode($payload['body'], true);

        $a = (int)$data['a'];
        $b = (int)$data['b'];

        return [
            'input' => $data,
            'output' => $a + $b
        ];
    }
}