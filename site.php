<?php //arquivo de configuraçao
	use \tsh\Page;
	use \tsh\Model\Product;

	$app->get("/", function() {
		$prd = Product::listAll();
	    $page = new Page();
	    $page->setTpl("index",
	    	['products'=>Product::checkList($prd)]
		);
	});
?>