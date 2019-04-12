<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

class ConcreteCommandWithDefaultValueAsClassConstant implements CommandWithDefaultValueAsClassConstant
{

    public function run(): void
    {

    }

    public function foo(int $const = CommandWithDefaultValueAsClassConstant::FOO_CONSTANT): void
    {

    }
}
