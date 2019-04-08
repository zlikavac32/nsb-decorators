<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

class ConcreteCommandWithNullableReturn implements CommandWithNullableReturn
{

    public function run(): void
    {

    }

    public function foo(): ?int
    {
        return null;
    }
}
