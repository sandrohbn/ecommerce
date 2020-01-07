<?php 
	namespace tsh;

	use Rain\Tpl;

	class Page
	{
		private $tpl;
		private $option = [];
		private $default = [
			"header"=>true,
			"footer"=>true,
			"data"=>[]
		];

		public function __construct($opt = array(), $tpl_dir = DIRECTORY_SEPARATOR . "view". DIRECTORY_SEPARATOR)
		{
			$this->default["data"]["session"] = $_SESSION;

			$this->option = array_merge($this->default, $opt);

			$config = array(
				"tpl_dir"       => str_replace("/", DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']) . $tpl_dir,
				"cache_dir"     => str_replace("/", DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']) . 
					DIRECTORY_SEPARATOR . "view-cache" . DIRECTORY_SEPARATOR,
				"debug"         => false // set to false to improve the speed
			);

			Tpl::configure($config);

			$this->tpl = new Tpl;

			$this->setData($this->option["data"]);

			if ($this->option["header"] === true) 
			{
				$this->tpl->draw("header");
			}
		}

		private function setData($data = array())
		{
			/*var_dump($data);*/
			foreach ($data as $key => $value)
			{
				/*var_dump($value);*/
				$this->tpl->assign($key, $value);
			}
		}

		public function setTpl($name, $data = array(), $returnHTML = false)
		{
			$this->setData($data);

			return $this->tpl->draw($name, $returnHTML);
		}

		public function __destruct()
		{
			if ($this->option["footer"] === true) {
				$this->tpl->draw("footer");
			}
		}
	}
?>