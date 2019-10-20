<?php 
	namespace tsh;

	class PageAdmin extends Page
	{
		public function __construct($opt = array(), $tpl_dir = "/view/admin/")
		{
			parent::__construct($opt, $tpl_dir);
		}
	}
?>