# Not So Bad Decorators

On the fly service decorators.

## Table of contents

1. [Intro](#intro)
1. [Installation](#installation)
1. [Usage](#usage)
1. [How it works?](#how-it-works)
1. [Restrictions](#restrictions)
1. [Examples](#examples)

## Intro

It's better to start with an examples, so let's do so.

Let's assume we have an interface for our command. It can be run and that's it.

```php
interface Command
{

    public function run(Input $input, Output $output): int;
}
```

But, depending on the application implementation, commands can do much more. For example, command could have a help text associated with it.

```php
interface CommandWithHelp extends Command
{

    public function help(): string;
}
```

Since we orient towards interfaces, decorators are a common thing. One might provide a decorator that uses decorated debug output. That way, depending on how we construct our commands, we get different behaviour.

```php
class DebugCommand implements Command
{

    private $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function run(Input $input, Output $output): int
    {
        return $this->command->run($input, new DebugOutput($output));
    }
}
```

But now we have an issue. Because decorator does not necessarily implement all of the interfaces that a concrete command does, we might lose some functionality.

```php
class FoOCommand implements CommandWithHelp
{

    public function run(Input $input, Output $output): int
    {
        $output->writeln('Foo');

        return 0;
    }

    public function help(): string
    {
        return 'Command just writes Foo';
    }
}

$command = new DebugCommand(new FooCommand());

$command->help(); // does not work
```

One solution would be to merge all additional interfaces into the original one, but that now imposes a lot of methods to implement every time, although they are not needed. We'd be back at the beginning.

Other approach could be to manually define additional implementations for the same decorator, but that would introduce to many new classes.

A, perhaps, better way to do that is to do it programmatically. And that's what this library does. For a given decorator class and a subject class (class of the instance being decorated), code for the new proxy decorator class is created.

## Installation

Recommended installation is through Composer.

```
composer require zlikavac32/nsb-decorators
```

## Usage

To create code for the proxy decorator class, use `\Zlikavac32\NSBDecorators\Proxy::createClassProxy()`. It requires four arguments:

- `proxy FQN` - FQN of the class that will be generated
- `decorator` - `ReflectionClass` of the decorator class
- `subject` - `ReflectionClass` of the subject class, the one that is being decorated
- `argument name` - Name of the argument that accepts subject service, without `$`

That code can then be stored somewhere or evaluated.

It's also possible to use it with the autoloader. Use `\Zlikavac32\NSBDecorators\Proxy::createFQNForProxyClass()` to create proxy class name. It accepts three arguments, decorator class, subject class and argument name that accepts subject in the decorator class. Class name encodes all of those arguments in itself.

`\Zlikavac32\NSBDecorators\Proxy::loadFQN` can be registered as autoloader to create proxy services on the fly.

## How it works?

Interfaces from both classes are collected and difference is computed. Proxy class will extend decorator class and implement all of the interfaces that subject implements and decorator does not. Those methods will just proxy to the subject instance.

For the example above, something like this would be generated.

```php

namespace Zlikavac32\NSBDecorators\Proxy {

    class Generated_4465627567436f6d6d616e64_466f4f436f6d6d616e64_636f6d6d616e64 extends \DebugCommand implements \CommandWithHelp
    {

        private $command;

        public function __construct(\Command $command)
        {
            parent::__construct($command);

            $this->command = $command;
        }

        public function help(): string
        {
            return $this->command->help();
        }
    }
}
```

To generate multiple decorators, every decorator in the chain must be generated the same way (if it does not have required interfaces already).

## Restrictions

This library is intended for service decorators, that don't have fluent interface.

Static methods, abstract classes and anonymous classes are not supported. For obvious reasons, final classes are not supported as well.

Methods that relay on arguments not specified in the method declaration are ignored. Currently arguments are directly mapped. Perhaps in the future, `...func_get_args()` may be used.

## Examples

You can see more examples with code comments in [examples](/examples).
