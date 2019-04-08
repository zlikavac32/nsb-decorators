<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

interface CommandWithDefaultValueAsString extends Command
{

    public function foo(string $const = 'foo'): void;
}
