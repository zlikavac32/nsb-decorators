<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators\Tests\Unit;

use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Zlikavac32\NSBDecorators\Proxy;
use Zlikavac32\NSBDecorators\Tests\Fixtures\AbstractCommand;
use Zlikavac32\NSBDecorators\Tests\Fixtures\Command;
use Zlikavac32\NSBDecorators\Tests\Fixtures\ConcreteCommand;
use Zlikavac32\NSBDecorators\Tests\Fixtures\ConcreteCommandWithArgumentAsReference;
use Zlikavac32\NSBDecorators\Tests\Fixtures\ConcreteCommandWithArgumentWithoutTypeHint;
use Zlikavac32\NSBDecorators\Tests\Fixtures\ConcreteCommandWithDefaultValueAsConstant;
use Zlikavac32\NSBDecorators\Tests\Fixtures\ConcreteCommandWithDefaultValueAsString;
use Zlikavac32\NSBDecorators\Tests\Fixtures\ConcreteCommandWithMultipleArguments;
use Zlikavac32\NSBDecorators\Tests\Fixtures\ConcreteCommandWithNullableArgument;
use Zlikavac32\NSBDecorators\Tests\Fixtures\ConcreteCommandWithNullableReturn;
use Zlikavac32\NSBDecorators\Tests\Fixtures\ConcreteCommandWithoutReturnTypeHint;
use Zlikavac32\NSBDecorators\Tests\Fixtures\ConcreteCommandWithTwoInterfaces;
use Zlikavac32\NSBDecorators\Tests\Fixtures\ConcreteCommandWithVariadicArgument;
use Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommand;
use Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommandWithSubjectAsReference;
use Zlikavac32\NSBDecorators\Tests\Fixtures\FinalCommand;

class ProxyTest extends TestCase
{

    /**
     * @test
     */
    public function decorator_fqn_must_not_be_empty_when_creating_proxy_class(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Decorator FQN can not be empty');

        Proxy::createFQNForProxyClass('', 'a', 'b');
    }

    /**
     * @test
     */
    public function subject_fqn_must_not_be_empty_when_creating_proxy_class(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Subject FQN can not be empty');

        Proxy::createFQNForProxyClass('a', '', 'b');
    }

    /**
     * @test
     */
    public function argument_name_must_not_be_empty_when_creating_proxy_class(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Argument name can not be empty');

        Proxy::createFQNForProxyClass('a', 'b', '');
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function nothing_happens_when_non_proxy_class_is_loaded(): void
    {
        $fqn = 'Foo\\Bar';

        Proxy::loadFQN($fqn);

        self::assertFalse(class_exists($fqn));
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function error_is_thrown_when_attempt_to_load_invalid_proxy_class_is_made(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Zlikavac32\\NSBDecorators\\Proxy\\Foo does not appear to be valid proxy FQN');

        Proxy::loadFQN(Proxy::class . '\\Foo');
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function proxy_class_should_be_loaded(): void
    {
        $className = Proxy::createFQNForProxyClass(
            DecoratorCommand::class,
            ConcreteCommand::class,
            'command'
        );

        self::assertFalse(class_exists($className));

        Proxy::loadFQN($className);

        self::assertTrue(class_exists($className));
    }

    /**
     * @test
     */
    public function error_is_thrown_when_decorator_class_is_final(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Zlikavac32\NSBDecorators\Tests\Fixtures\FinalCommand must not be final');

        Proxy::createClassProxy(
            'Foo',
            new ReflectionClass(FinalCommand::class),
            new ReflectionClass(ConcreteCommand::class),
            'invalud-but-ok-for-the-test-purpose'
        );
    }

    /**
     * @test
     */
    public function error_is_thrown_when_decorator_class_is_abstract(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Zlikavac32\NSBDecorators\Tests\Fixtures\AbstractCommand must not be abstract');

        Proxy::createClassProxy(
            'Foo',
            new ReflectionClass(AbstractCommand::class),
            new ReflectionClass(ConcreteCommand::class),
            'invalud-but-ok-for-the-test-purpose'
        );
    }

    /**
     * @test
     */
    public function error_is_thrown_when_decorator_class_is_anonymous(): void
    {
        $class = get_class(
            new class implements Command
            {

                public function run(): void
                {

                }
            }
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($class . ' must not be anonymous');

        Proxy::createClassProxy(
            'Foo',
            new ReflectionClass($class),
            new ReflectionClass(ConcreteCommand::class),
            'invalud-but-ok-for-the-test-purpose'
        );
    }

    /**
     * @test
     */
    public function error_is_thrown_when_subject_class_is_abstract(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Zlikavac32\NSBDecorators\Tests\Fixtures\AbstractCommand must not be abstract');

        Proxy::createClassProxy(
            'Foo',
            new ReflectionClass(DecoratorCommand::class),
            new ReflectionClass(AbstractCommand::class),
            'invalud-but-ok-for-the-test-purpose'
        );
    }

    /**
     * @test
     */
    public function error_is_thrown_when_subject_class_is_anonymous(): void
    {
        $class = get_class(
            new class implements Command
            {

                public function run(): void
                {

                }
            }
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($class . ' must not be anonymous');

        Proxy::createClassProxy(
            'Foo',
            new ReflectionClass(DecoratorCommand::class),
            new ReflectionClass($class),
            'invalud-but-ok-for-the-test-purpose'
        );
    }

    /**
     * @test
     */
    public function error_is_thrown_when_argument_does_not_exist(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Parameter non-existing-argument missing in the parameter list');

        Proxy::createClassProxy(
            'Foo',
            new ReflectionClass(DecoratorCommand::class),
            new ReflectionClass(ConcreteCommand::class),
            'non-existing-argument'
        );
    }

    /**
     * @test
     */
    public function proxy_with_multiple_interfaces_is_generated(): void
    {
        $expectedCode = <<<'PHP'
namespace  {
    class Foo extends \Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommand implements \Zlikavac32\NSBDecorators\Tests\Fixtures\CommandWithHelp, \Zlikavac32\NSBDecorators\Tests\Fixtures\CommandWithDescription
    {
        private $command;
        
        public function __construct(\Zlikavac32\NSBDecorators\Tests\Fixtures\Command $command)
        {
            parent::__construct($command);
            
            $this->command = $command;
        }
        
        public function help(): string
{
    return $this->command->help();
}

public function description(): string
{
    return $this->command->description();
}
    }
}
PHP;

        self::assertSame(
            $expectedCode,
            Proxy::createClassProxy(
                'Foo',
                new ReflectionClass(DecoratorCommand::class),
                new ReflectionClass(ConcreteCommandWithTwoInterfaces::class),
                'command'
            )
        );
    }

    /**
     * @test
     */
    public function proxy_with_namespace_is_generated(): void
    {
        $expectedCode = <<<'PHP'
namespace Foo\Bar {
    class Baz extends \Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommand
    {
        private $command;
        
        public function __construct(\Zlikavac32\NSBDecorators\Tests\Fixtures\Command $command)
        {
            parent::__construct($command);
            
            $this->command = $command;
        }
        
        
    }
}
PHP;

        self::assertSame(
            $expectedCode,
            Proxy::createClassProxy(
                'Foo\\Bar\\Baz',
                new ReflectionClass(DecoratorCommand::class),
                new ReflectionClass(ConcreteCommand::class),
                'command'
            )
        );
    }

    /**
     * @test
     */
    public function proxy_subject_can_be_injected_by_reference(): void
    {
        $expectedCode = <<<'PHP'
namespace  {
    class Foo extends \Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommandWithSubjectAsReference
    {
        private $command;
        
        public function __construct(\Zlikavac32\NSBDecorators\Tests\Fixtures\Command &$command)
        {
            parent::__construct($command);
            
            $this->command = &$command;
        }
        
        
    }
}
PHP;

        self::assertSame(
            $expectedCode,
            Proxy::createClassProxy(
                'Foo',
                new ReflectionClass(DecoratorCommandWithSubjectAsReference::class),
                new ReflectionClass(ConcreteCommand::class),
                'command'
            )
        );
    }

    /**
     * @test
     */
    public function method_argument_can_have_default_value_as_constant(): void
    {
        $expectedCode = <<<'PHP'
namespace  {
    class Foo extends \Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommand implements \Zlikavac32\NSBDecorators\Tests\Fixtures\CommandWithDefaultValueAsConstant
    {
        private $command;
        
        public function __construct(\Zlikavac32\NSBDecorators\Tests\Fixtures\Command $command)
        {
            parent::__construct($command);
            
            $this->command = $command;
        }
        
        public function foo(int $const = \Zlikavac32\NSBDecorators\Tests\Fixtures\FOO_CONSTANT): void
{
    $this->command->foo($const);
}
    }
}
PHP;

        self::assertSame(
            $expectedCode,
            Proxy::createClassProxy(
                'Foo',
                new ReflectionClass(DecoratorCommand::class),
                new ReflectionClass(ConcreteCommandWithDefaultValueAsConstant::class),
                'command'
            )
        );
    }

    /**
     * @test
     */
    public function method_argument_can_have_default_value_as_mixed_value(): void
    {
        $expectedCode = <<<'PHP'
namespace  {
    class Foo extends \Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommand implements \Zlikavac32\NSBDecorators\Tests\Fixtures\CommandWithDefaultValueAsString
    {
        private $command;
        
        public function __construct(\Zlikavac32\NSBDecorators\Tests\Fixtures\Command $command)
        {
            parent::__construct($command);
            
            $this->command = $command;
        }
        
        public function foo(string $const = 'foo'): void
{
    $this->command->foo($const);
}
    }
}
PHP;

        self::assertSame(
            $expectedCode,
            Proxy::createClassProxy(
                'Foo',
                new ReflectionClass(DecoratorCommand::class),
                new ReflectionClass(ConcreteCommandWithDefaultValueAsString::class),
                'command'
            )
        );
    }

    /**
     * @test
     */
    public function method_can_have_multiple_arguments(): void
    {
        $expectedCode = <<<'PHP'
namespace  {
    class Foo extends \Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommand implements \Zlikavac32\NSBDecorators\Tests\Fixtures\CommandWithMultipleArguments
    {
        private $command;
        
        public function __construct(\Zlikavac32\NSBDecorators\Tests\Fixtures\Command $command)
        {
            parent::__construct($command);
            
            $this->command = $command;
        }
        
        public function foo(int $foo, string $bar, \Zlikavac32\NSBDecorators\Tests\Fixtures\Command $command): void
{
    $this->command->foo($foo, $bar, $command);
}
    }
}
PHP;

        self::assertSame(
            $expectedCode,
            Proxy::createClassProxy(
                'Foo',
                new ReflectionClass(DecoratorCommand::class),
                new ReflectionClass(ConcreteCommandWithMultipleArguments::class),
                'command'
            )
        );
    }

    /**
     * @test
     */
    public function method_argument_can_be_nullable(): void
    {
        $expectedCode = <<<'PHP'
namespace  {
    class Foo extends \Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommand implements \Zlikavac32\NSBDecorators\Tests\Fixtures\CommandWithNullableArgument
    {
        private $command;
        
        public function __construct(\Zlikavac32\NSBDecorators\Tests\Fixtures\Command $command)
        {
            parent::__construct($command);
            
            $this->command = $command;
        }
        
        public function foo(?string $foo): void
{
    $this->command->foo($foo);
}
    }
}
PHP;

        self::assertSame(
            $expectedCode,
            Proxy::createClassProxy(
                'Foo',
                new ReflectionClass(DecoratorCommand::class),
                new ReflectionClass(ConcreteCommandWithNullableArgument::class),
                'command'
            )
        );
    }

    /**
     * @test
     */
    public function method_return_can_be_nullable(): void
    {
        $expectedCode = <<<'PHP'
namespace  {
    class Foo extends \Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommand implements \Zlikavac32\NSBDecorators\Tests\Fixtures\CommandWithNullableReturn
    {
        private $command;
        
        public function __construct(\Zlikavac32\NSBDecorators\Tests\Fixtures\Command $command)
        {
            parent::__construct($command);
            
            $this->command = $command;
        }
        
        public function foo(): ?int
{
    return $this->command->foo();
}
    }
}
PHP;

        self::assertSame(
            $expectedCode,
            Proxy::createClassProxy(
                'Foo',
                new ReflectionClass(DecoratorCommand::class),
                new ReflectionClass(ConcreteCommandWithNullableReturn::class),
                'command'
            )
        );
    }

    /**
     * @test
     */
    public function method_argument_can_be_a_reference(): void
    {
        $expectedCode = <<<'PHP'
namespace  {
    class Foo extends \Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommand implements \Zlikavac32\NSBDecorators\Tests\Fixtures\CommandWithArgumentAsReference
    {
        private $command;
        
        public function __construct(\Zlikavac32\NSBDecorators\Tests\Fixtures\Command $command)
        {
            parent::__construct($command);
            
            $this->command = $command;
        }
        
        public function foo(string &$foo): void
{
    $this->command->foo($foo);
}
    }
}
PHP;

        self::assertSame(
            $expectedCode,
            Proxy::createClassProxy(
                'Foo',
                new ReflectionClass(DecoratorCommand::class),
                new ReflectionClass(ConcreteCommandWithArgumentAsReference::class),
                'command'
            )
        );
    }

    /**
     * @test
     */
    public function method_argument_can_be_variadic(): void
    {
        $expectedCode = <<<'PHP'
namespace  {
    class Foo extends \Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommand implements \Zlikavac32\NSBDecorators\Tests\Fixtures\CommandWithVariadicArgument
    {
        private $command;
        
        public function __construct(\Zlikavac32\NSBDecorators\Tests\Fixtures\Command $command)
        {
            parent::__construct($command);
            
            $this->command = $command;
        }
        
        public function foo(string ...$foo): void
{
    $this->command->foo(...$foo);
}
    }
}
PHP;

        self::assertSame(
            $expectedCode,
            Proxy::createClassProxy(
                'Foo',
                new ReflectionClass(DecoratorCommand::class),
                new ReflectionClass(ConcreteCommandWithVariadicArgument::class),
                'command'
            )
        );
    }

    /**
     * @test
     */
    public function method_argument_can_be_without_type_hint(): void
    {
        $expectedCode = <<<'PHP'
namespace  {
    class Foo extends \Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommand implements \Zlikavac32\NSBDecorators\Tests\Fixtures\CommandWithArgumentWithoutTypeHint
    {
        private $command;
        
        public function __construct(\Zlikavac32\NSBDecorators\Tests\Fixtures\Command $command)
        {
            parent::__construct($command);
            
            $this->command = $command;
        }
        
        public function foo( $foo): void
{
    $this->command->foo($foo);
}
    }
}
PHP;

        self::assertSame(
            $expectedCode,
            Proxy::createClassProxy(
                'Foo',
                new ReflectionClass(DecoratorCommand::class),
                new ReflectionClass(ConcreteCommandWithArgumentWithoutTypeHint::class),
                'command'
            )
        );
    }

    /**
     * @test
     */
    public function method_return_can_be_without_type_hint(): void
    {
        $expectedCode = <<<'PHP'
namespace  {
    class Foo extends \Zlikavac32\NSBDecorators\Tests\Fixtures\DecoratorCommand implements \Zlikavac32\NSBDecorators\Tests\Fixtures\CommandWithoutReturnTypeHint
    {
        private $command;
        
        public function __construct(\Zlikavac32\NSBDecorators\Tests\Fixtures\Command $command)
        {
            parent::__construct($command);
            
            $this->command = $command;
        }
        
        public function foo()
{
    return $this->command->foo();
}
    }
}
PHP;

        self::assertSame(
            $expectedCode,
            Proxy::createClassProxy(
                'Foo',
                new ReflectionClass(DecoratorCommand::class),
                new ReflectionClass(ConcreteCommandWithoutReturnTypeHint::class),
                'command'
            )
        );
    }
}
