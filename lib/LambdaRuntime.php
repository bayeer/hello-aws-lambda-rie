<?php

declare(strict_types=1);

namespace Bayeer\SimpleLambda;

use Exception;

class LambdaRuntime
{
    public function __construct(private string $apiUrl) {}

    public static function loop(string $className): void
    {
        $app = new $className();

        $runtime = new static($_ENV['AWS_LAMBDA_RUNTIME_API']);

        // This is the request processing loop. Barring unrecoverable failure, this loop runs until the environment shuts down.
        do {
            // Ask the runtime API for a request to handle.
            $request = $runtime->getLambdaNextRequest();

            $handlerFunction = $_ENV['AWS_LAMBDA_FUNCTION_NAME'];
            $response = call_user_func([$app, $handlerFunction], $request['payload']);

            // Submit the response back to the runtime API.
            $runtime->sendLambdaResponse($request['invocationId'], $response);
        } while (true);
    }

    /**
     * Get the next response from Lambda.
     *
     * @return array
     */
    public function getLambdaNextRequest(): array
    {
        static $handler;

        if (is_null($handler)) {
            $handler = curl_init("http://{$this->apiUrl}/2018-06-01/runtime/invocation/next");

            curl_setopt($handler, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($handler, CURLOPT_FAILONERROR, true);
        }

        // Retrieve the Lambda invocation ID...
        $invocationId = '';

        curl_setopt($handler, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$invocationId) {
            if (! preg_match('/:\s*/', $header)) {
                return strlen($header);
            }

            [$name, $value] = preg_split('/:\s*/', $header, 2);

            if (strtolower($name) === 'lambda-runtime-aws-request-id') {
                $invocationId = trim($value);
            }

            return strlen($header);
        });

        // Retrieve the Lambda invocation event body...
        $body = '';

        curl_setopt($handler, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use (&$body) {
            $body .= $chunk;

            return strlen($chunk);
        });

        curl_exec($handler);

        if (curl_error($handler)) {
            throw new Exception('Failed to retrieve the next Lambda invocation: '.curl_error($handler));
        }

        if ($invocationId === '') {
            throw new Exception('Failed to parse the Lambda invocation ID.');
        }

        if ($body === '') {
            throw new Exception('The Lambda runtime API returned an empty response.');
        }

        return [
            'invocationId' => $invocationId, 
            'payload' => json_decode($body, true)
        ];
    }

    /**
     * Send the response data to Lambda.
     *
     * @param  string  $invocationId
     * @param  mixed  $data
     * @return void
     */
    public function sendLambdaResponse($invocationId, $data): void
    {
        $this->lambdaRequest(
            "http://{$this->apiUrl}/2018-06-01/runtime/invocation/{$invocationId}/response", $data
        );
    }

    /**
     * Send the error response data to Lambda.
     *
     * @param  string  $invocationId
     * @param  mixed  $data
     * @return void
     */
    public function sendLambdaError($invocationId, $data): void
    {
        $this->lambdaRequest(
            "http://{$this->apiUrl}/2018-06-01/runtime/invocation/{$invocationId}/error", $data
        );
    }

    /**
     * Send the given data to the given URL as JSON.
     *
     * @param  string  $url
     * @param  mixed  $data
     * @return void
     */
    protected function lambdaRequest($url, $data): void
    {
        $json = json_encode($data);

        if ($json === false) {
            throw new Exception('Error encoding runtime JSON response: '.json_last_error_msg());
        }

        $handler = curl_init($url);

        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_POSTFIELDS, $json);

        curl_setopt($handler, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: '.strlen($json),
        ]);

        curl_exec($handler);

        if (curl_error($handler)) {
            $errorMessage = curl_error($handler);

            throw new Exception('Error calling the runtime API: '.$errorMessage);
        }

        curl_setopt($handler, CURLOPT_HEADERFUNCTION, null);
        curl_setopt($handler, CURLOPT_READFUNCTION, null);
        curl_setopt($handler, CURLOPT_WRITEFUNCTION, null);
        curl_setopt($handler, CURLOPT_PROGRESSFUNCTION, null);

        curl_reset($handler);

        curl_close($handler);
    }
}
