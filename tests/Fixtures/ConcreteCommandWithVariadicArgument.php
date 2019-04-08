<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

class ConcreteCommandWithVariadicArgument implements CommandWithVariadicArgument
{

    public function run(): void
    {

    }

    public function foo(string ...$foo): void
    {

    }
}
