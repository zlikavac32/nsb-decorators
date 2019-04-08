<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

interface CommandWithoutReturnTypeHint extends Command
{

    public function foo();
}
