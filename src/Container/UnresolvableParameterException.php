<?php
namespace DIServer\Container;

/**
 * Description of UnresolvableException
 *
 * @author Back
 */
class UnresolvableParameterException extends ContainerException
{
    public function __construct(\ReflectionParameter $parameter)
    {
	$msg = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";
	parent::__construct($msg);
    }
}
