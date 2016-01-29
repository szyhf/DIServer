<?php

namespace DIServer\Event;


class BehaviorNotCallableException extends EventException
{
	public function __construct(string $tag, mixed $behavior)
	{
		$objects = var_export($behavior, true);
		$msgs = "Add behavior to tag[$tag] failed, it is not callable." . PHP_EOL;
		$msgs .= "Behavior exports as :" . PHP_EOL;
		foreach($objects as $object)
		{
			$msgs .= $object . "\n";
		}
		$msgs = rtrim($msgs, "\n");
		parent::__construct($msgs);
	}
}