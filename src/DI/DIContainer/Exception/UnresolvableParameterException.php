<?php
namespace DIServer\DI\DIContainer\Exception;

/**
 * Description of UnresolvableException
 *
 * @author Back
 */
class UnresolvableParameterException extends DIContainerException
{
    public function __construct(\ReflectionParameter $parameter)
    {
	$msg = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";
	parent::__construct($msg);
    }
}
