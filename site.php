<?php //arquivo de configuraçao
	use \tsh\Page;
	use \tsh\Model\Product;
	use \tsh\Model\Category;
	use \tsh\Model\Cart;
	use \tsh\Model\Address;
	use \tsh\Model\User;

	const MSGRGNGSITE001 = "Preencha o seu nome";
	const MSGRGNGSITE002 = "Preencha o seu e-mail";
	const MSGRGNGSITE003 = "Preencha a senha";
	const MSGRGNGSITE004 = "Este endereço de e-mail já está sendo usado por outro usuário";

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

	$app->get("/cart", function(){
		$cart = Cart::getFromSession();
		$page = new Page();
		$page->setTpl("cart", [
			'cart'=>$cart->getData(),
			'products'=>$cart->getProducts(),
			'error'=>getMsgError()
		]);
	});

	$app->get("/cart/:idproduct/add", function($idproduct){
		$product = new Product();
		$product->get((int)$idproduct);

		$cart = Cart::getFromSession();
		$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

		for ($i = 0; $i < $qtd; $i++) {
			$cart->addProduct($product);
		}

		header("Location: /cart");
		exit;		
	});

	$app->get("/cart/:idproduct/minus", function($idproduct){
		$product = new Product();
		$product->get((int)$idproduct);

		$cart = Cart::getFromSession();
		$cart->removeProduct($product);

		header("Location: /cart");
		exit;		
	});

	$app->get("/cart/:idproduct/minus", function($idproduct){
		$product = new Product();
		$product->get((int)$idproduct);

		$cart = Cart::getFromSession();
		$cart->removeProduct($product, true);

		header("Location: /cart");
		exit;		
	});

	$app->get("/cart/:idproduct/remove", function($idproduct){
		$product = new Product();
		$product->get((int)$idproduct);

		$cart = Cart::getFromSession();
		$cart->removeProduct($product, true);

		header("Location: /cart");
		exit;		
	});

	$app->post("/cart/freight", function(){
		$cart = Cart::getFromSession();
		$cart->setfreight($_POST['zipcode']);
		
		header("Location: /cart");
		exit;		
	});

	$app->get("/checkout", function()
	{
		User::verifyLogin(false);

		$cart = Cart::getFromSession();
		$address = new Address();

		$page = new Page();
		$page->setTpl("checkout", [
			'cart'=>$cart->getData(),
			'address'=>$address->getData()
		]);
	});

	$app->get("/login", function()
	{
		$page = new Page();
		$page->setTpl("login", [
			'error'=>getMsgError(),
			'errorRegister'=>User::getErrorRegister(),
			'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
		]);
	});	

	$app->post("/login", function()
	{
		try {
			User::login($_POST['login'], $_POST['password']);
		} catch(Exception $e) {
			setMsgError($e->getMessage());
		}
		header("Location: /checkout");
		exit;
	});

	$app->get("/logout", function()
	{
		User::logout();
		header("Location: /login");
		exit;
	});

	$app->post("/register", function()
	{
		$_SESSION['registerValues'] = $_POST;

		$msgRgNgSite = NULL;

		if (!isset($_POST['name']) || $_POST['name'] == '') {
			$msgRgNgSite = MSGRGNGSITE001;
		} else 
		if (!isset($_POST['email']) || $_POST['email'] == '') {
			$msgRgNgSite = MSGRGNGSITE002;
		} else
		if (!isset($_POST['password']) || $_POST['password'] == '') {
			$msgRgNgSite = MSGRGNGSITE003;
		} else
		if (User::checkLoginExist($_POST['email']) === true) {
			$msgRgNgSite = MSGRGNGSITE004;
		}

		if ($msgRgNgSite != NULL) {
			User::setErrorRegister($msgRgNgSite);
			header("Location: /login");
			exit;
		}

		$user = new User();
		$user->setData([
			'inadmin'=>0,
			'deslogin'=>$_POST['email'],
			'desperson'=>$_POST['name'],
			'desemail'=>$_POST['email'],
			'despassword'=>$_POST['password'],
			'nrphone'=>$_POST['phone']
		]);
		$user->save();

		User::login($_POST['email'], $_POST['password']);

		header('Location: /checkout');
		exit;
	});	
?>