<?php
declare(strict_types=1);

namespace Soliant\SimpleFM\Repository\Builder\Proxy;

use ReflectionClass;
use ReflectionMethod;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

final class ProxyBuilder implements ProxyBuilderInterface
{
    const PROXY_NAMESPACE = 'SimpleFMProxy';

    /**
     * @var string|null
     */
    private $proxyFolder;

    public function __construct(string $proxyFolder = null)
    {
        $this->proxyFolder = $proxyFolder;
    }

    public function createProxy(string $entityInterfaceName, callable $initializer, $relationId) : ProxyInterface
    {
        $proxyClassName = str_replace('\\', '_', $entityInterfaceName) . 'Proxy';
        $fullyQualifiedClassName = '\\' . self::PROXY_NAMESPACE . '\\' . $proxyClassName;

        if (class_exists($fullyQualifiedClassName)) {
            return new $fullyQualifiedClassName($initializer, $relationId);
        }

        require_once $this->buildProxyClass($entityInterfaceName, self::PROXY_NAMESPACE, $proxyClassName);
        return new $fullyQualifiedClassName($initializer, $relationId);
    }

    private function buildProxyClass(
        string $entityInterfaceName,
        string $proxyNamespace,
        string $proxyClassName
    ) : string {
        $reflectionClass = new ReflectionClass($entityInterfaceName);

        if (!$reflectionClass->isInterface()) {
            // @todo throw exception
        }

        $classGenerator = new ClassGenerator();
        $classGenerator->setNamespaceName($proxyNamespace);
        $classGenerator->setName($proxyClassName);
        $classGenerator->setImplementedInterfaces([
            $entityInterfaceName,
            ProxyInterface::class,
        ]);

        $classGenerator->addProperty('initializer', null, PropertyGenerator::FLAG_PRIVATE);
        $classGenerator->addProperty('relationId', null, PropertyGenerator::FLAG_PRIVATE);
        $classGenerator->addProperty('realEntity', null, PropertyGenerator::FLAG_PRIVATE);

        $constructorGenerator = new MethodGenerator('__construct', [
            ['name' => 'initializer', 'type' => 'callable'],
            ['name' => 'relationId'],
        ]);
        $constructorGenerator->setBody('
            $this->initializer = $initializer;
            $this->relationId = $relationId;
        ');
        $classGenerator->addMethodFromGenerator($constructorGenerator);

        $getRelationIdGenerator = new MethodGenerator('__getRelationId');
        $getRelationIdGenerator->setBody('
            return $this->relationId;
        ');
        $classGenerator->addMethodFromGenerator($getRelationIdGenerator);

        $getRealEntityGenerator = new MethodGenerator('__getRealEntity');
        $getRealEntityGenerator->setBody('
            if (null === $this->realEntity) {
                $this->realEntity = ($this->initializer)();
                \Assert\Assertion::isInstanceOf($this->realEntity, \\' . $entityInterfaceName . '::class);
            };

            return $this->relationId;
        ');
        $classGenerator->addMethodFromGenerator($getRealEntityGenerator);

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            $parameters = [];
            $parameterGenerators = [];
            $returnType = $reflectionMethod->getReturnType();

            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $parameterGenerator = new ParameterGenerator(
                    $reflectionParameter->getName(),
                    $reflectionParameter->getType(),
                    $reflectionParameter->isDefaultValueAvailable() ? $reflectionParameter->getDefaultValue() : null
                );
                $parameterGenerator->setVariadic($reflectionParameter->isVariadic());

                $parameterGenerators[] = $parameterGenerator;

                if ($reflectionParameter->isVariadic()) {
                    $parameters[] = '...$' . $reflectionParameter->getName();
                } else {
                    $parameters[] = '$' . $reflectionParameter->getName();
                }
            }

            $methodGenerator = new MethodGenerator();
            $methodGenerator->setName($reflectionMethod->getName());
            $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PUBLIC);
            $methodGenerator->setParameters($parameterGenerators);
            $methodGenerator->setReturnType($returnType);

            $body = '
                if (null === $this->realEntity) {
                    $this->realEntity = ($this->initializer)();
                    \Assert\Assertion::isInstanceOf($this->realEntity, \\' . $entityInterfaceName . '::class);
                };
            ';

            if ('void' !== $returnType) {
                $body .= 'return ';
            }

            $body .= '$this->realEntity->' . $reflectionMethod->getName() . '(' . implode(', ', $parameters) . ');';

            $methodGenerator->setBody($body);
            $classGenerator->addMethodFromGenerator($methodGenerator);
        }

        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($classGenerator);

        $filename = (
            null === $this->proxyFolder
            ? tempnam(sys_get_temp_dir(), $proxyClassName)
            : sprintf('%s/%s.php', $this->proxyFolder, $proxyClassName)
        );

        $fileGenerator->setFilename($filename);
        $fileGenerator->write();

        return $filename;
    }
}
