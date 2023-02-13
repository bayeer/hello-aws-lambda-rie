<?php

declare(strict_types=1);

namespace Bayeer;

class App
{
    public function hello(array $payload): array
    {
        echo '[*] function invocated...', PHP_EOL;

        var_dump($payload);
        $data = json_decode($payload['body'], true);

        return ['foo' => 1, 'field' => $data['field']];
    }
}
