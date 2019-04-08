<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

interface CommandWithMultipleArguments extends Command
{

    public function foo(int $foo, string $bar, Command $command): void;
}
