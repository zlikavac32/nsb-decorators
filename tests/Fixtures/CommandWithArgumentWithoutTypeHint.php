<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

interface CommandWithArgumentWithoutTypeHint extends Command
{

    public function foo($foo): void;
}
