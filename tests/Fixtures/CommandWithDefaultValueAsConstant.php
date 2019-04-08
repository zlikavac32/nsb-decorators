<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

const FOO_CONSTANT = 123;

interface CommandWithDefaultValueAsConstant extends Command
{

    public function foo(int $const = FOO_CONSTANT): void;
}
