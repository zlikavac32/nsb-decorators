<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

class ConcreteCommandWithArgumentWithoutTypeHint implements CommandWithArgumentWithoutTypeHint
{

    public function run(): void
    {

    }

    public function foo($foo): void
    {

    }
}
