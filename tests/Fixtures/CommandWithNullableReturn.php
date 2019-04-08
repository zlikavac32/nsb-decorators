<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

interface CommandWithNullableReturn extends Command
{

    public function foo(): ?int;
}
