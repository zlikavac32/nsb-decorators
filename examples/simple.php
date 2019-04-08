<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Examples;

use Zlikavac32\NSBDecorators\Proxy;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/common.php';

$debugCommandProxyClass = Proxy::createFQNForProxyClass(DebugCommand::class, FoOCommand::class, 'command');

$command = new $debugCommandProxyClass(new FooCommand());

echo $command->help(), "\n";
$command->run($input, $output);
