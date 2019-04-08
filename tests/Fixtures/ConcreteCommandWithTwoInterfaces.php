<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

class ConcreteCommandWithTwoInterfaces implements CommandWithHelp, CommandWithDescription
{

    public function run(): void
    {

    }

    public function help(): string
    {
        return 'help';
    }

    public function description(): string
    {
        return 'description';
    }
}
