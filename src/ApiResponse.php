<?php

declare(strict_types=1);

namespace Bayeer;

class ApiResponse
{
    static $headers = [
        "Content-Type" => "application/json",
        "Access-Control-Allow-Origin" => "*",
        "Access-Control-Allow-Headers" => "Content-Type",
        "Access-Control-Allow-Methods"  => "OPTIONS,POST"
    ];

    public static function json($body): string
    {
        return json_encode([
            'statusCode' => 200,
            'headers' => static::$headers,
            'body' => $body
        ]);
    }
}
