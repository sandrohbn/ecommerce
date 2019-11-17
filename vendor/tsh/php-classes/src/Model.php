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
					return (isset($this->value[$fieldName]) ? $this->value[$fieldName] : NULL);
					break;
				case "set":
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

		//public function getData($key = null)
		public function getData()
		{
			//*? avalir se pode inicializar tudo ''
			//if (!is_null($key)) {
				return isset($this->value) ? $this->value : '';
			//} else {
			//	return $this->value;
			//}			    
		}
	}
?>