<?php //arquivo de configuraçao
	use \tsh\PageAdmin;
	use \tsh\Model\User;
	use \tsh\Model\Product;

	$app->get("/admin/products", function(){
		User::verifyLogin();
		$product = Product::listAll();
		$page = new PageAdmin();
		$page->setTpl("products",
			["products"=>$product]
		);
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
		$product->setData($_POST);
		$product->save();
		//var_dump($_FILES); exit;
		$product->setPhoto($_FILES["file"]);
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