<?php //arquivo de configuraçao
	use \tsh\Page;
	use \tsh\Model\Product;
	use \tsh\Model\Category;

	$app->get("/", function() {
		$prd = Product::listAll();
	    $page = new Page();
	    $page->setTpl("index",
	    	['products'=>Product::checkList($prd)]
		);
	});

	$app->get("/category/:idcategory", function($idcategory){
		$category = new Category();
		$category->get((int)$idcategory);
		$page = new Page();
		$page->setTpl("category", [
			'category'=>$category->getData(),
			'product'=>Product::checkList($category->getProduct())
		]);
	});
?>