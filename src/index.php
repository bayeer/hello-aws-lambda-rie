<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Bayeer\App;
use Bayeer\LambdaRuntime;

Bayeer\LambdaRuntime::loop(new Bayeer\App());
