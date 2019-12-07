<?php //arquivo de configuraçao
	use \tsh\PageAdmin;
	use \tsh\Model\User;
	use \tsh\Model\Product;

	$app->get("/admin/products", function(){
		User::verifyLogin();

		$search = (isset($_GET['search'])) ? $_GET['search'] : "";
		$page = (isset($_GET['page'])) ? $_GET['page'] : 1;

		$pagination = Product::listPage($page, LINHAPORPAGINA, $search);

		$pages = [];

		for ($x = 0; $x < $pagination['pages']; $x++)
		{
			array_push($pages, [
				'href'=>'/admin/products?'.http_build_query([
					'page'=>$x+1,
					'search'=>$search
				]),
				'text'=>$x+1
			]);
		}

		//* $product = Product::listAll();
		$page = new PageAdmin();
		$page->setTpl("products", [
	    	"products"=>$pagination['data'],
	    	"search"=>$search,
	    	"pages"=>$pages
		]);
	});

	$app->get("/admin/products/create", function(){
		User::verifyLogin();
		$page = new PageAdmin();
		$page->setTpl("products-create");
	});	
 
	$app->post("/admin/products/create", function(){
		User::verifyLogin();
		//var_dump($_POST);
		$product = new Product();
		$product->setData($_POST);
		//var_dump($product);
		$product->save();
		header("Location: /admin/products");
		exit;
	});

	$app->get("/admin/products/:idproduct", function($idproduct){
		User::verifyLogin();
		$product = new Product();
		$product->get((int)$idproduct);
		$page = new PageAdmin();
		$page->setTpl("products-update",
			['product'=>$product->getData()]
		);
	});	

	$app->post("/admin/products/:idproduct", function($idproduct){
		User::verifyLogin();
		$product = new Product();
		$product->get((int)$idproduct);
		$product->setData($_POST); //recebe os campos da classe
		$product->save();
		//var_dump($_FILES); exit;
		$product->setPhoto($_FILES["file"]); //recebe nome de arquivo
		header("Location: /admin/products");
		exit;
	});

	$app->get("/admin/products/delete/:idproduct", function($idproduct){
		User::verifyLogin();
		$product = new Product();
		$product->get((int)$idproduct);
		$product->delete();
		header("Location: /admin/products");
		exit;
	});
?>