<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

class ConcreteCommandWithArgumentAsReference implements CommandWithArgumentAsReference
{

    public function run(): void
    {

    }

    public function foo(string &$foo): void
    {

    }
}
