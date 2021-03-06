<?php

namespace Rosem\Container\Definition;

use ReflectionClass;
use SplFixedArray;
use Rosem\Container\ExtractorTrait;

trait ReflectedBuildTrait
{
    use ExtractorTrait;

    /**
     * @var \Rosem\Container\AbstractContainer
     */
    protected $container;

    /**
     * @var \Reflector
     */
    protected $reflector;

    /**
     * @var SplFixedArray
     */
    protected $stack = [];

    /**
     * Get stack of classes and parameters for automatic building
     *
     * @param iterable|\ReflectionParameter[] $params
     *
     * @return SplFixedArray $stack
     */
    private function getStack(iterable $params) : SplFixedArray
    {
        $index = -1;
        $length = count($params);
        $stack = new SplFixedArray($length);

        while ($length) {
            $stack[++$index] = $params[--$length]->getClass() ?: $params[$length];
        }

        return $stack;
    }

    /**
     * Build and inject all dependencies with parameters
     *
     * @param iterable $stack
     * @param array    $args
     *
     * @return array $building
     */
    private function build(iterable $stack, array $args = []) : array
    {
        $stackLength = count($stack);
        $building = [];

        while ($stackLength) {
            $item = $stack[--$stackLength];

            if ($item instanceof ReflectionClass && ! is_object(reset($args))) {
                $building[] = $this->container->invoke(
                    $item->name,
                    $this->container->has($item->name) && $this->container->isShared($item->name)
                        ? $args
                        : $this->extractFirst($args)
                );
            } elseif ($args) {
                $building[] = $this->extractFirst($args);
            }
        }

        return $building;
    }
}
