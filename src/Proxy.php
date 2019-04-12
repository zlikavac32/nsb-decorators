<?php

declare(strict_types=1);

namespace Zlikavac32\NSBDecorators;

use Ds\Map;
use Ds\Sequence;
use Ds\Set;
use Ds\Vector;
use LogicException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionType;

class Proxy
{

    private const CLASS_FINAL = 0x1;
    private const CLASS_ABSTRACT = 0x2;
    private const CLASS_ANONYMOUS = 0x4;

    private const TEMPLATE_CLASS = <<<'PHP'
namespace <ns> {
    class <proxy-fqn> extends <decorator-fqn><implements>
    {
        private $<argument>;
        
        public function __construct(<args-def>)
        {
            parent::__construct(<args-pass>);
            
            $this-><argument> = <argument-ref>;
        }
        
        <methods>
    }
}
PHP;

    private const TEMPLATE_METHOD = <<<'PHP'
public function <ref-or-not><method-name>(<args-def>)<ret-type>
{
    <return-or-not>$this-><argument>-><method-name>(<args-pass>);
}
PHP;

    /**
     * Generates FQN for the decorator proxy. Name encodes input arguments in itself.
     */
    public static function createFQNForProxyClass(
        string $decoratorFqn,
        string $subjectFqn,
        string $argumentName
    ): string {
        self::assertNotEmpty($decoratorFqn, 'Decorator FQN');
        self::assertNotEmpty($subjectFqn, 'Subject FQN');
        self::assertNotEmpty($argumentName, 'Argument name');

        return sprintf(
            '%s\\Generated_%s_%s_%s',
            Proxy::class,
            bin2hex($decoratorFqn),
            bin2hex($subjectFqn),
            bin2hex($argumentName)
        );
    }

    private static function assertNotEmpty(string $value, string $message): void
    {
        if (empty($value)) {
            throw new LogicException(sprintf('%s can not be empty', $message));
        }
    }

    /**
     * If given FQN represents proxy, code for that class is generated and then evaled.
     *
     * Multiple calls to this method with same argument will result in an error.
     *
     * This method is intended to be used as autoloader.
     *
     * @throws \ReflectionException
     */
    public static function loadFQN(string $fqn): void
    {
        if (substr($fqn, 0, strlen(Proxy::class)) !== Proxy::class) {
            return;
        }

        $parts = explode('_', self::parseFQNToNsAndClassName($fqn)[1]);

        if (count($parts) !== 4) {
            throw new LogicException(sprintf('%s does not appear to be valid proxy FQN', $fqn));
        }

        $decoratorFqn = hex2bin($parts[1]);
        $subjectFqn = hex2bin($parts[2]);
        $argumentName = hex2bin($parts[3]);

        $code = self::createClassProxy(
            $fqn,
            new ReflectionClass($decoratorFqn),
            new ReflectionClass($subjectFqn),
            $argumentName
        );

        eval($code);
    }

    /**
     * Creates proxy class code that can be stored somewhere or evaluated.
     */
    public static function createClassProxy(
        string $proxyFqn,
        ReflectionClass $decorator,
        ReflectionClass $subject,
        string $argumentName
    ): string {
        self::assertClassIsNot($decorator, self::CLASS_FINAL | self::CLASS_ABSTRACT | self::CLASS_ANONYMOUS);
        self::assertClassIsNot($subject, self::CLASS_ABSTRACT | self::CLASS_ANONYMOUS);

        /** @var ReflectionClass[]|Sequence $interfacesToImplement */
        $interfacesToImplement = new Vector(
            array_udiff(
                $subject->getInterfaces(),
                $decorator->getInterfaces(),
                function (ReflectionClass $first, ReflectionClass $second): int {
                    return strcmp($first->getName(), $second->getName());
                }
            )
        );

        [$proxyNS, $proxyClassName] = self::parseFQNToNsAndClassName($proxyFqn);

        $constructorArgs = new Vector(
            $decorator->getConstructor()
                ->getParameters()
        );

        $classTemplateVariables = [
            '<ns>'            => $proxyNS,
            '<proxy-fqn>'     => $proxyClassName,
            '<decorator-fqn>' => '\\' . $decorator->getName(),
            '<implements>'    => $interfacesToImplement->count() > 0 ? (' implements ' . $interfacesToImplement->map(
                    function (ReflectionClass $reflectionClass): string {
                        return '\\' . $reflectionClass->getName();
                    }
                )
                    ->join(', ')) : '',
            '<argument>'      => $argumentName,
            '<argument-ref>'  => (self::findNamedArgument($constructorArgs, $argumentName)
                    ->isPassedByReference() ? '&' : '') . '$' . $argumentName,
            '<args-def>'      => $constructorArgs->map(
                function (ReflectionParameter $parameter): string {
                    return self::remapArgumentsDeclare($parameter);
                }
            )
                ->join(', '),
            '<args-pass>'     => $constructorArgs->map(
                function (ReflectionParameter $parameter): string {
                    return self::remapArgumentsPass($parameter);
                }
            )
                ->join(', '),
            '<methods>'       => self::createMethods($decorator, $interfacesToImplement, $argumentName)
                ->join("\n\n"),
        ];

        return str_replace(
            array_keys($classTemplateVariables),
            array_values($classTemplateVariables),
            self::TEMPLATE_CLASS
        );
    }

    private static function assertClassIsNot(ReflectionClass $reflectionClass, int $rules): void
    {
        if (($rules & self::CLASS_FINAL) && $reflectionClass->isFinal()) {
            throw new LogicException(sprintf('%s must not be final', $reflectionClass->getName()));
        }
        if (($rules & self::CLASS_ANONYMOUS) && $reflectionClass->isAnonymous()) {
            throw new LogicException(sprintf('%s must not be anonymous', $reflectionClass->getName()));
        }
        if (($rules & self::CLASS_ABSTRACT) && $reflectionClass->isAbstract()) {
            throw new LogicException(sprintf('%s must not be abstract', $reflectionClass->getName()));
        }
    }

    private static function parseFQNToNsAndClassName(string $fqn): array
    {
        $pos = strrpos($fqn, '\\');

        if (false === $pos) {
            return ['', $fqn];
        }

        return [substr($fqn, 0, $pos), substr($fqn, $pos + 1)];
    }

    /**
     * @param Sequence|ReflectionClass[] $interfacesToImplement
     */
    private static function createMethods(
        ReflectionClass $decorator,
        Sequence $interfacesToImplement,
        string $argumentName
    ): Sequence {
        $methodsInDecorator = new Set(
            array_map(
                function (ReflectionMethod $method): string {
                    return $method->getName();
                },
                $decorator->getMethods()
            )
        );

        /** @var ReflectionMethod[]|Map $methodsToProxy */
        $methodsToProxy = new Map();

        foreach ($interfacesToImplement as $interface) {
            foreach ($interface->getMethods() as $method) {
                if ($methodsInDecorator->contains($method->getName())) {
                    continue;
                }

                if ($method->isStatic()) {
                    throw new LogicException(
                        sprintf(
                            'Static methods are not supported (when implementing %s)',
                            $method->getDeclaringClass()
                                ->getName()
                        )
                    );
                }

                $methodsToProxy->put($method->getName(), $method);
            }
        }

        return $methodsToProxy->values()
            ->map(
                function (ReflectionMethod $method) use ($argumentName): string {
                    $methodArgs = new Vector($method->getParameters());

                    $methodTemplateVariables = [
                        '<method-name>'   => $method->getName(),
                        '<args-def>'      => $methodArgs->map(
                            function (ReflectionParameter $parameter): string {
                                return self::remapArgumentsDeclare($parameter);
                            }
                        )
                            ->join(', '),
                        '<args-pass>'     => $methodArgs->map(
                            function (ReflectionParameter $parameter): string {
                                return self::remapArgumentsPass($parameter);
                            }
                        )
                            ->join(', '),
                        '<argument>'      => $argumentName,
                        '<return-or-not>' => ($method->hasReturnType() && $method->getReturnType()
                                ->getName() === 'void') ? '' : 'return ',
                        '<ref-or-not>'    => $method->returnsReference() ? '&' : '',
                        '<ret-type>'      => $method->hasReturnType() ? (': ' . self::remapType(
                                $method->getReturnType()
                            )) : '',
                    ];

                    return str_replace(
                        array_keys($methodTemplateVariables),
                        array_values($methodTemplateVariables),
                        self::TEMPLATE_METHOD
                    );
                }
            );
    }

    /**
     * @param Vector|ReflectionParameter[] $reflectionParameters
     */
    private static function findNamedArgument(Vector $reflectionParameters, string $argumentName): ReflectionParameter
    {
        $argument = $reflectionParameters->filter(
            function (ReflectionParameter $parameter) use ($argumentName): bool {
                return $parameter->getName() === $argumentName;
            }
        );

        if (1 !== $argument->count()) {
            throw new LogicException(sprintf('Parameter %s missing in the parameter list', $argumentName));
        }

        return $argument->get(0);
    }

    private static function remapArgumentsDeclare(ReflectionParameter $parameter): string
    {
        $argumentDeclareAsString = self::remapType($parameter->getType()) . ' ';

        if ($parameter->isPassedByReference()) {
            $argumentDeclareAsString .= '&';
        }

        if ($parameter->isVariadic()) {
            $argumentDeclareAsString .= '...';
        }

        $argumentDeclareAsString .= '$' . $parameter->getName();

        if (!$parameter->isDefaultValueAvailable()) {
            return $argumentDeclareAsString;
        }

        $argumentDeclareAsString .= ' = ';

        if ($parameter->isDefaultValueConstant()) {
            $constantAsString = $parameter->getDefaultValueConstantName();

            if (self::shouldPrependBackslashToConstantString($constantAsString)) {
                $constantAsString = '\\' . $constantAsString;
            }

            return $argumentDeclareAsString . $constantAsString;
        }

        return $argumentDeclareAsString . var_export($parameter->getDefaultValue(), true);
    }

    private static function shouldPrependBackslashToConstantString(string $constantAsString): bool
    {
        return substr($constantAsString, 0, 6) !== 'self::';
    }

    private static function remapArgumentsPass(ReflectionParameter $parameter): string
    {
        $argumentPassAsString = '';

        if ($parameter->isVariadic()) {
            $argumentPassAsString .= '...' . $argumentPassAsString;
        }

        return $argumentPassAsString . '$' . $parameter->getName();
    }

    private static function remapType(?ReflectionType $returnType): string
    {
        if (null === $returnType) {
            return '';
        }

        $typeAsString = '';

        if ($returnType->allowsNull()) {
            $typeAsString .= '?';
        }

        if (!$returnType->isBuiltin()) {
            $typeAsString .= '\\';
        }

        return $typeAsString . $returnType->getName();
    }
}
