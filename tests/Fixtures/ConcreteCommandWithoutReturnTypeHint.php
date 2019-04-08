<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

class ConcreteCommandWithoutReturnTypeHint implements CommandWithoutReturnTypeHint
{

    public function run(): void
    {

    }

    public function foo()
    {

    }
}
