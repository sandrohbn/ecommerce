<?php 
	namespace tsh;
	use Rain\Tpl;

	class Page{
		private $tpl;
		private $option = [];
		private $default = [
			"data"=>[]
		];

		public function __construct($opt = array()){

			$this->option = array_merge($this->default, $opt);
			$config = array(
				"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/view/",
				"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/view-cache/",
				"debug"         => false // set to false to improve the speed
			);

			Tpl::configure( $config );

			$this->tpl = new Tpl;
			$this->setData($this->option["data"]);
			$this->tpl->draw("header");
		}

		private function setData($data = array())
		{
			foreach ($data as $key => $value)
			{
				$this->tpl->assign($key, $value);
			}
		}

		public function setTpl($nome, $data = array(), $returnHTML = false)
		{
			$this->setData();
			return $this->tpl->draw($nome, $returnHTML);
		}

		public function __destruct()
		{
			$this->tpl->draw("footer");
		}
	}
?>