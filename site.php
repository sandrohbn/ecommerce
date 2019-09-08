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
		$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
		$category = new Category();
		$category->get((int)$idcategory);

		$pagination = $category->getProductPage($page);
		$pages = [];

		for ($i=1; $i <= $pagination['pages']; $i++)
		{
			array_push($pages, [
				'link'=>'/category/'.$category->getidcategory().'?page='.$i,
				'page'=>$i
			]);
		}

		$page = new Page();
		$page->setTpl("category", [
			'category'=>$category->getData(),
		  //todos produtos
		  //'product'=>Product::checkList($category->getProduct())
		  //produtos paginação
			'product'=>$pagination["data"],
			'pages'=>$pages
		]);
	});

	$app->get("/product/:desurl", function($desurl){
		$product = new Product();
		$product->getFromURL($desurl);

		$page = new Page();
		$page->setTpl("product-detail", [
			'product'=>$product->getData(),
			'category'=>$product->getCategory()
		]);
	});
?>