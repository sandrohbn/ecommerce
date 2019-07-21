<?php
	namespace tsh;

	class Model
	{
		private $value = [];

		public function __call($name, $arg)
		{
			$method = substr($name, 0, 3);
			$fieldName = substr($name, 3, strlen($name));
			
			/*var_dump($method, $fieldName);
			exit;*/

			switch ($method) {
				case "get":
					return $this->value[$fieldName];
					break;
				case "set":
					/*var_dump($arg[0]);
					exit;*/
					$this->value[$fieldName] = $arg[0];
					break;
				default:
					# code...
					break;
			}
		}

		public function setData($data = array())
		{
			foreach ($data as $key => $value) {
				$this->{"set".$key}($value); //{} entre chaves conteudo dinamico
			}
		}

		public function getData()
		{
			return $this->value;
		}
	}
?>