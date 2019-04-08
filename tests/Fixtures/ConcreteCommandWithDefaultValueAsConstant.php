<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

class ConcreteCommandWithDefaultValueAsConstant implements CommandWithDefaultValueAsConstant
{

    public function run(): void
    {

    }

    public function foo(int $const = FOO_CONSTANT): void
    {

    }
}
