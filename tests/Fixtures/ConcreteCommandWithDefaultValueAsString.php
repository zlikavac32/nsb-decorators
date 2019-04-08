<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

class ConcreteCommandWithDefaultValueAsString implements CommandWithDefaultValueAsString
{

    public function run(): void
    {

    }

    public function foo(string $const = 'foo'): void
    {

    }
}
