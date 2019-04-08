<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Examples;

use Zlikavac32\NSBDecorators\Proxy;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/common.php';

class TimedCommand implements Command
{

    /**
     * @var Command
     */
    private $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function run(Input $input, Output $output): int
    {
        $time = microtime(true);

        try {
            return $this->command->run($input, $output);
        } finally {
            $output->writeln(sprintf('Run in %lf seconds', microtime(true) - $time));
        }
    }
}

$debugCommandProxyClass = Proxy::createFQNForProxyClass(DebugCommand::class, FoOCommand::class, 'command');
$timedCommandProxyClass = Proxy::createFQNForProxyClass(TimedCommand::class, FoOCommand::class, 'command');

$command = new $timedCommandProxyClass(new $debugCommandProxyClass(new FooCommand()));

echo $command->help(), "\n";
$command->run($input, $output);
