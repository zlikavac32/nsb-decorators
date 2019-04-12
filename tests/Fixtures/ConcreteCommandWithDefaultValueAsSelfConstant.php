<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

class ConcreteCommandWithDefaultValueAsSelfConstant implements CommandWithDefaultValueAsSelfConstant
{

    public function run(): void
    {

    }

    public function foo(int $const = self::FOO_CONSTANT): void
    {

    }
}
