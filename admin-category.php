<?php //arquivo de configuraçao
	use \tsh\PageAdmin;
	use \tsh\Model\User;
	use \tsh\Model\Category;
	use \tsh\Model\Product;

	//const LINHAPORPAGINA = 10;

	$app->get("/admin/categories", function()
	{
		User::verifyLogin();

		$search = (isset($_GET['search'])) ? $_GET['search'] : "";
		$page = (isset($_GET['page'])) ? $_GET['page'] : 1;

		$pagination = Category::listPage($page, LINHAPORPAGINA, $search);

		$pages = [];

		for ($x = 0; $x < $pagination['pages']; $x++)
		{
			array_push($pages, [
				'href'=>'/admin/categories?'.http_build_query([
					'page'=>$x+1,
					'search'=>$search
				]),
				'text'=>$x+1
			]);
		}

		//* $categories = category::listAll();

	    $page = new PageAdmin();
	    $page->setTpl("categories", [
	    	"categories"=>$pagination['data'],
	    	"search"=>$search,
	    	"pages"=>$pages
	    ]);
	});
	
	$app->get("/admin/categories/create", function()
	{
		User::verifyLogin();
	    $page = new PageAdmin();
	    $page->setTpl("categories-create");
	});

	$app->post("/admin/categories/create", function()
	{
		User::verifyLogin();
	    $category = new Category();
	    $category->setData($_POST);
	    $category->save();
	    header('Location: /admin/categories');
	    exit;
	});

	$app->get("/admin/categories/delete/:iduser", function($idcategory)
	{
		User::verifyLogin();
		$category = new Category();
		$category->get((int)$idcategory);
		$category->delete();
	    header('Location: /admin/categories');
	    exit;
	});

	$app->get("/admin/categories/update/:idcategory", function($idcategory)
	{
		//var_dump($idcategory);
		User::verifyLogin();
		$category = new Category();
		$category->get((int)$idcategory);
	    $page = new PageAdmin();
	    $page->setTpl("categories-update",
	      ['category'=>$category->getData()]
		);
	});

	$app->post("/admin/categories/update/:idcategory", function($idcategory)
	{
		User::verifyLogin();
		$category = new Category();
		$category->get((int)$idcategory);
		$category->setData($_POST);
		$category->save();
	    header('Location: /admin/categories');
	    exit;
	});

	$app->get("/admin/categories/:idcategory/products", function($idcategory){
		User::verifyLogin();
		$category = new Category();
		$category->get((int)$idcategory);
		//var_dump($category->getProduct()); exit;
		$page = new PageAdmin();
		$page->setTpl("categories-products", [
			'category'=>$category->getData(),
			'productsRelated'=>$category->getProduct(),
			'productsNotRelated'=>$category->getProduct(false)
		]);
	});

	$app->get("/admin/categories/:idcategory/products/:idproduct/add", 
		function($idcategory, $idproduct){
			User::verifyLogin();
			$category = new Category();
			$category->get((int)$idcategory);
			$product = new Product();
			$product->get((int)$idproduct);
			$category->addProduct($product);
		    header('Location: /admin/categories/'.$idcategory.'/products');
		    exit;
	});

	$app->get("/admin/categories/:idcategory/products/:idproduct/remove", 
		function($idcategory, $idproduct){
			User::verifyLogin();
			$category = new Category();
			$category->get((int)$idcategory);
			$product = new Product();
			$product->get((int)$idproduct);
			$category->removeProduct($product);
		    header('Location: /admin/categories/'.$idcategory.'/products');
		    exit;
	});
?>