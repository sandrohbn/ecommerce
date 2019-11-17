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
	const MSGRGNGSITE005 = "Preencha o endereço";
	const MSGRGNGSITE006 = "Preencha a cidade";
	const MSGRGNGSITE007 = "Preencha o estado";
	const MSGRGNGSITE008 = "Preencha o país";
	const MSGRGNGSITE009 = "Preencha o CEP";
	const MSGRGNGSITE010 = "Preencha o bairro";

	const MSGSUCCSITE001 = "Dados salvos com sucesso!";

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

		$address = new Address();

		$cart = Cart::getFromSession();

		//*? isset verifica se a variável é definida.
		if (isset($_GET['zipcode'])) {
			$_GET['zipcode'] = $cart->getdeszipcode();
		}
		//*/
		if (isset($_GET['zipcode']))
		{
			//*	var_dump('aqui');exit;
			$address->loadFromCEP($_GET['zipcode']);//os nomes que viram do webservice serão diferente do banco de dados, por isso é necessário fazer converção dos nomes 

			$cart->setdeszipcode($_GET['zipcode']);
			$cart->save();
			$cart->getCalculateTotal();
		}

		//inicializa com '' os campos cujo o cep informado não retorne valor
		if (!$address->getdesaddress()) $address->setdesaddress('');
		if (!$address->getdescomplement()) $address->setdescomplement('');
		if (!$address->getdesdistrict()) $address->setdesdistrict('');
		if (!$address->getdescity()) $address->setdescity('');
		if (!$address->getdesstate()) $address->setdesstate('');
		if (!$address->getdescountry()) $address->setdescountry('');
		if (!$address->getdeszipcode()) $address->setdeszipcode('');

		$page = new Page();
		$page->setTpl("checkout", [
			'cart'=>$cart->getData(),
			'address'=>$address->getData(),
			'products'=>$cart->getProducts(),
			'error'=>Address::getMsgError()
		]);
	});

	$app->post("/checkout", function()
	{
		User::verifyLogin(false); //false pq não esta na administração

		//*e Criar metodo com foreach para validação de formularios
		$msgRgNgSite = NULL;
		if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {
			$msgRgNgSite = MSGRGNGSITE005;
		} else 
		if (!isset($_POST['descity']) || $_POST['descity'] === '') {
			$msgRgNgSite = MSGRGNGSITE006;
		} else
		if (!isset($_POST['desstate']) || $_POST['desstate'] === '') {
			$msgRgNgSite = MSGRGNGSITE007;
		} else
		if (!isset($_POST['descountry']) || $_POST['descountry'] === '') {
			$msgRgNgSite = MSGRGNGSITE008;
		} else
		if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '') { //no bd zipcode = deszipcode
			$msgRgNgSite = MSGRGNGSITE009;
		} else
		if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') {
			$msgRgNgSite = MSGRGNGSITE010;
		}

		//*var_dump($address);exit;
		if ($msgRgNgSite != NULL) {
			//*var_dump($msgRgNgSite);exit;
			Address::setMsgError($msgRgNgSite);
			header('Location: /checkout');
			exit;
		}		

		$user = User::getFromSession();

		$address = new Address();

		$_POST['deszipcode'] = $_POST['zipcode'];
		$_POST['idperson'] = $user->getidperson();

		$address->setData($_POST); //recebe post do formulario, relacionando os "name"s e os sobrescrito anteriormente aos campos do objeto/bd
		
		$address->save();

		header("Location: /order");
		exit;
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
		if (User::checkLoginExists($_POST['email']) === true) {
			$msgRgNgSite = MSGRGNGSITE004;
		}
		if ($msgRgNgSite !== NULL) {
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

	$app->get("/forgot", function()
	{
	    $page = new Page();
	    $page->setTpl("forgot");
	});
	
	$app->post("/forgot", function()
	{
		$user = User::getForgot($_POST["email"], false);
		header("Location: /forgot/sent");
		exit;
	});

	$app->get("/forgot/sent", function()
	{
	    $page = new Page();
	    $page->setTpl("forgot-sent");
	});

	$app->get("/forgot/reset", function()
	{
		$user = User::validForgotDecrypt($_GET["code"]);
	    $page = new Page();
	    $page->setTpl("forgot-reset", array(
	    	"name"=>$user["desperson"],
	    	"code"=>$_GET["code"]
	    ));
	});

	$app->post("/forgot/reset", function()
	{
		$forgot = User::validForgotDecrypt($_POST["code"]);
		User::setForgotUsed($forgot["idrecovery"]);
		$user = new User();
		$user->get((int)$forgot["iduser"]);
		$password = password_hash(
			$_POST["password"],
			PASSWORD_DEFAULT,
			["cost"=>12]
		);
		$user->setPassword($password);
	    $page = new Page();
	    $page->setTpl("forgot-reset-success");		
	});

	$app->get("/profile", function(){
		User::verifyLogin(false);
		$user = User::getFromSession();
		$page = new Page();
		$page->setTpl("profile", [
			'user'=>$user->getData(),
			'profileMsg'=>getMsgSuccess(),
			'profileError'=>getMsgError()
		]);
	});

	$app->post("/profile", function()
	{
		User::verifyLogin(false);
		$user = User::getFromSession();

		$msgRgNgSite = NULL;
		if (!isset($_POST['desperson']) || $_POST['desperson'] == '') {
			$msgRgNgSite = MSGRGNGSITE001;
		} else 
		if (!isset($_POST['desemail']) || $_POST['desemail'] == '') {
			$msgRgNgSite = MSGRGNGSITE002;
		} else
		if ($_POST['desemail'] !== $user->getdesemail())
		{
			if (User::checkLoginExists($_POST['desemail']) === true) {
				$msgRgNgSite = MSGRGNGSITE004;
			}
		}
		if ($msgRgNgSite !== NULL) {
			setMsgError($msgRgNgSite);
			header("Location: /profile");
			exit;
		}

		//Caso inadmin e despassword tenha sido descoberto pelo usuario e tente
		//manipular esta informação usamos o comando abaixoo para sobreescrever
		//mantendo valor original que veio do banco de dados
		$_POST['inadmin'] = $user->getinadmin();
		$_POST['despassword'] = $user->getdespassword();
		$_POST['deslogin'] = $_POST['desemail']; //ño site login = email

		$user->setData($_POST);
		$user->update();

		setMsgSuccess(MSGSUCCSITE001);

		header('Location: /profile');
		exit;
	});
?>