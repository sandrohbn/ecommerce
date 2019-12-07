<?php //arquivo de configuraçao
	use \tsh\Page;
	use \tsh\Model\Product;
	use \tsh\Model\Category;
	use \tsh\Model\Cart;
	use \tsh\Model\Address;
	use \tsh\Model\User;
	use \tsh\Model\Order;
	use \tsh\Model\OrderStatus;

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
	const MSGRGNGSITE011 = "Preencha a senha atual";
	const MSGRGNGSITE012 = "Preencha a nova senha";
	const MSGRGNGSITE013 = "Preencha a confirmação da nova senha";
	const MSGRGNGSITE014 = "A nova senha deve ser diferente da atual";
	const MSGRGNGSITE015 = "A nova senha deve ser igual a confirmação de senha";
	const MSGRGNGSITE016 = "A senha atual está inválida";

	const MSGSUCCSITE001 = "Dados salvos com sucesso!";
	const MSGSUCCSITE002 = "Senha alterada com sucesso!";

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
		if (!$address->getdesnumber()) $address->setdesnumber('');
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

		$cart = Cart::getFromSession();
		$cart->getCalculateTotal();

		$order = new Order();
		$order->setData([
			'idcart'=>$cart->getidcart(),
			'idaddress'=>$address->getidaddress(),
			'iduser'=>$user->getiduser(),
			'idstatus'=>OrderStatus::EM_ABERTO,
			'vltotal'=>$cart->getvltotal()
		]);

		$order->save();

		//Antes
		//header("Location: /order/".$order->getidorder());
		//Com Pagseguro
		header("Location: /order/".$order->getidorder()."/pagseguro");
		exit;
	});

	$app->get("/order/:idorder/pagseguro", function($idorder)
	{
		User::verifyLogin(false);

		$order = new Order();
		$order->get((int)$idorder);
		//*var_dump($order->getData());exit;

		$cart = $order->getCart();
		
		$page = new Page([
			'header'=>false,
			'footer'=>false
		]);
		$page->setTpl("payment-pagseguro", [
			'order'=>$order->getData(),
			'cart'=>$cart->getData(),
			'products'=>$cart->getProducts(),			
			'phone'=>[
				'areaCode'=>substr($order->getnrphone(), 0, 2),
				'number'=>substr($order->getnrphone(), 2, strlen($order->getnrphone()))
			]
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

	$app->get("/order/:idorder", function($idorder)
	{
		User::verifyLogin(false);

		$order = new Order();
		$order->get((int)$idorder);

		//*var_dump($order->getData());exit;
		
		$page = new Page();
		$page->setTpl("payment", [
			'order'=>$order->getData()
		]);
	});	

	$app->get("/boleto/:idorder", function($idorder)
	{
		User::verifyLogin(false);

		$order = new Order();
		$order->get((int)$idorder);

		// DADOS DO BOLETO PARA O SEU CLIENTE
		$dias_de_prazo_para_pagamento = 10;
		$taxa_boleto = 5.00;
		$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 

		//*d var_dump($order->getvltotal());exit;

		$valor_cobrado = $order->getvltotal(); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
		//*t $valor_cobrado = str_replace(".", "", $valor_cobrado);
		//*t $valor_cobrado = str_replace(",", ".", $valor_cobrado);
		$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

		$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
		$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
		$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
		$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
		$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
		$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

		// DADOS DO SEU CLIENTE
		$dadosboleto["sacado"] = $order->getdesperson();
		//$dadosboleto["endereco1"] = "Av. Paulista, 500";
		$dadosboleto["endereco1"] = $order->getdesaddress().", ".$order->getdesnumber()." - ".$order->getdesdistrict();
		//$dadosboleto["endereco2"] = "Cidade - Estado -  CEP: 00000-000";
		$dadosboleto["endereco2"] = $order->getdescity()." - ".$order->getdesstate()." - ".$order->getdescountry()." - CEP:".$order->getdeszipcode();

		// INFORMACOES PARA O CLIENTE
		$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
		$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
		$dadosboleto["demonstrativo3"] = "";
		$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
		$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
		$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
		$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

		// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
		$dadosboleto["quantidade"] = "";
		$dadosboleto["valor_unitario"] = "";
		$dadosboleto["aceite"] = "";		
		$dadosboleto["especie"] = "R$";
		$dadosboleto["especie_doc"] = "";


		// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
		// DADOS DA SUA CONTA - ITAÚ
		$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
		$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
		$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

		// DADOS PERSONALIZADOS - ITAÚ
		$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

		// SEUS DADOS
		$dadosboleto["identificacao"] = "Hcode Treinamentos";
		$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
		$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
		$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
		$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

		// NÃO ALTERAR!
		$path = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."boletophp".DIRECTORY_SEPARATOR."include".DIRECTORY_SEPARATOR;

		require_once($path."funcoes_itau.php");
		require_once($path."layout_itau.php");
	});

	$app->get("/profile/orders", function()
	{
		User::verifyLogin(false);

		$user = User::getFromSession();
		
		$page = new Page();
		$page->setTpl("profile-orders", [
			'orders'=>$user->getOrders()
		]);
	});

	$app->get("/profile/orders/:idorder", function($idorder)
	{
		User::verifyLogin(false);

		$order = new Order();
		$order->get((int)$idorder);
		
		$cart = new Cart();
		$cart->get((int)$order->getidcart());
		$cart->getCalculateTotal();

		$page = new Page();
		$page->setTpl("profile-orders-detail", [
			'order'=>$order->getData(),
			'cart'=>$cart->getData(),
			'products'=>$cart->getProducts()
		]);
	});

	$app->get("/profile/change-password", function()
	{
		User::verifyLogin(false);

		$page = new Page();
		$page->setTpl("profile-change-password", [
			'changePassError'=>getMsgError(),
			'changePassSuccess'=>getMsgSuccess()
		]);
	});

	$app->post("/profile/change-password", function()
	{
		User::verifyLogin(false);
		$user = User::getFromSession();
	
		$msgRgNgSite = NULL;

		if (!isset($_POST['current_pass']) || $_POST['current_pass'] === '') {
			$msgRgNgSite = MSGRGNGSITE011;
		} else 
		if (!isset($_POST['new_pass']) || $_POST['new_pass'] === '') {
			$msgRgNgSite = MSGRGNGSITE012;
		} else
		if (!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] === '') {
			$msgRgNgSite = MSGRGNGSITE013;
		} else
		if ($_POST['current_pass'] === $_POST['new_pass']) {
			$msgRgNgSite = MSGRGNGSITE014;
		} else 
		if ($_POST['new_pass_confirm'] !== $_POST['new_pass']) {
			$msgRgNgSite = MSGRGNGSITE015;
		} else
		if (!password_verify($_POST['current_pass'], $user->getdespassword()))
		{
			$msgRgNgSite = MSGRGNGSITE016;
		}

		if ($msgRgNgSite !== NULL) {
			setMsgError($msgRgNgSite);
			header("Location: /profile/change-password");
			exit;
		}

		$user->setdespassword($_POST['new_pass']);
		$user->update();

		setMsgSuccess(MSGSUCCSITE002);

		header("Location: /profile/change-password");
		exit;
	});
?>