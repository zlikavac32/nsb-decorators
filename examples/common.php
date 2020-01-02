<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Examples;

use Zlikavac32\NSBDecorators\Proxy;

interface Input
{

}

interface Output
{

    public function writeln(string $line);
}

class DebugOutput implements Output
{

    private Output $output;

    public function __construct(Output $output)
    {
        $this->output = $output;
    }

    public function writeln(string $line)
    {
        $this->output->writeln('DEBUG: ' . $line);
    }
}

/**
 * Base interface for our commands. Only one methods exists, since
 * that's the only thing our command has to do.
 */
interface Command
{

    public function run(Input $input, Output $output): int;
}

/**
 * Additional functionality to our commands can be achieved with new
 * interfaces.
 */
interface CommandWithHelp extends Command
{

    public function help(): string;
}

/**
 * Decorator that uses decorates output in order to use debug output.
 */
class DebugCommand implements Command
{

    private Command $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function run(Input $input, Output $output): int
    {
        return $this->command->run($input, new DebugOutput($output));
    }
}

/**
 * Concrete command implementation that just outputs Foo.
 */
class FoOCommand implements CommandWithHelp
{

    public function run(Input $input, Output $output): int
    {
        $output->writeln('Foo');

        return 0;
    }

    public function help(): string
    {
        return 'Command just writes Foo';
    }
}

$input = new class implements Input
{

};
$output = new class implements Output
{

    public function writeln(string $line)
    {
        echo $line, "\n";
    }
};

spl_autoload_register(Proxy::class . '::loadFQN');
