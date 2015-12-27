<?php
namespace DIServer\Container;

/**
 * 在容器中使用自动构造实例时出现了构造循环引用
 *
 * @author Back
 */
class DependenceCycleException extends ContainerException
{
	public function __construct(array $depencesStack)
	{
		$msg = "";
		foreach($depencesStack as $dependence)
		{
			$msg .= $dependence . " -> ";
		}
		$msg .= current($depencesStack);
		parent::__construct("Auto build can't slove dependence cycle:\n" . $msg);
	}
}
