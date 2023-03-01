<?php

declare(strict_types=1);

require_once $_ENV['LAMBDA_TASK_ROOT'] . '/vendor/autoload.php';

Bayeer\SimpleLambda\LambdaRuntime::loop(Bayeer\App::class);
