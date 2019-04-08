<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

interface CommandWithVariadicArgument extends Command
{

    public function foo(string ...$foo): void;
}
