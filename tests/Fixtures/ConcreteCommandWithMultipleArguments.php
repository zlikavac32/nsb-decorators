<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

class ConcreteCommandWithMultipleArguments implements CommandWithMultipleArguments
{

    public function run(): void
    {

    }

    public function foo(int $foo, string $bar, Command $command): void
    {
    }
}
