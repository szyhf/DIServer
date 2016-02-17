<?php
namespace DIServer\Helpers
{
	//if(function_exists('Lang'))
	//{
	//	function Lang($key)
	//	{
	//		$ary = include __DIR__.'/../Lang/CHN.php';
	//		return $ary[$key];
	//	}
	//}

	class Lang implements \ArrayAccess
	{
		protected $langArray = [];

		/**
		 * Whether a offset exists
		 *
		 * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
		 *
		 * @param mixed $offset <p>
		 *                      An offset to check for.
		 *                      </p>
		 *
		 * @return boolean true on success or false on failure.
		 * </p>
		 * <p>
		 * The return value will be casted to boolean if non-boolean was returned.
		 * @since 5.0.0
		 */
		public function offsetExists($offset)
		{
			return isset($this->langArray[$offset]);
		}

		/**
		 * Offset to retrieve
		 *
		 * @link  http://php.net/manual/en/arrayaccess.offsetget.php
		 *
		 * @param mixed $offset <p>
		 *                      The offset to retrieve.
		 *                      </p>
		 *
		 * @return mixed Can return all value types.
		 * @since 5.0.0
		 */
		public function offsetGet($offset)
		{
			// TODO: Implement offsetGet() method.
			return $this->langArray[$offset];
		}

		/**
		 * Offset to set
		 *
		 * @link  http://php.net/manual/en/arrayaccess.offsetset.php
		 *
		 * @param mixed $offset <p>
		 *                      The offset to assign the value to.
		 *                      </p>
		 * @param mixed $value  <p>
		 *                      The value to set.
		 *                      </p>
		 *
		 * @return void
		 * @since 5.0.0
		 */
		public function offsetSet($offset, $value)
		{
			// TODO: Implement offsetSet() method.
			$this->langArray[$offset] = $value;
		}

		/**
		 * Offset to unset
		 *
		 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
		 *
		 * @param mixed $offset <p>
		 *                      The offset to unset.
		 *                      </p>
		 *
		 * @return void
		 * @since 5.0.0
		 */
		public function offsetUnset($offset)
		{
			// TODO: Implement offsetUnset() method.
			unset($this->langArray[$offset]);
		}

		public function __callStatic($name, $arguments)
		{
			// TODO: Implement __callStatic() method.
			switch($name)
			{
				//case ''
			}
		}
	}
}