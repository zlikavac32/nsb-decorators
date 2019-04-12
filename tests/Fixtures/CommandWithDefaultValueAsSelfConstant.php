<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

interface CommandWithDefaultValueAsSelfConstant extends Command
{

    const FOO_CONSTANT = 123;

    public function foo(int $const = self::FOO_CONSTANT): void;
}
