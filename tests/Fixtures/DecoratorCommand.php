<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Fixtures;

class DecoratorCommand implements Command
{

    /**
     * @var Command
     */
    private $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function run(): void
    {

    }
}
