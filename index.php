<?php //arquivo de configuraçao
	session_start();

	require_once("vendor/autoload.php");

	use \Slim\Slim;
	use \tsh\Page;
	use \tsh\PageAdmin;
	use \tsh\Model\User;

	$app = new Slim();

	$app->config("debug", true);

	$app->get("/", function() {
	    $page = new Page();
	    $page->setTpl("index");
	});

	$app->get("/admin/", function() {
		User::verifyLogin();
	    $page = new PageAdmin();
	    $page->setTpl("index");
	});

	$app->get("/admin/login/", function() {
	    $page = new PageAdmin([
	    	"header"=>false, //login nao tem header/footer pq já é chamado na pagina admin
	    	"footer"=>false 
	    ]);
	    $page->setTpl("login"); //chama o template login  
	});

	$app->post("/admin/login/", function() {
		User::login($_POST["login"], $_POST["password"]);
		header("Location: /admin");
		exit;
	});

	$app->get("/admin/logout", function() {
		User::logout();
		header("Location: /admin/login/");
		exit;
	});

	//Tela lista todos usuarios 7:55
	$app->get("/admin/users", function() {
		User::verifyLogin();
		$user = User::listAll();
	    $page = new PageAdmin();
	    $page->setTpl("users", array("user"=>$user));
	});

	//Exibe tela cria usuario (usando get responde com html)
	$app->get("/admin/users/create", function() {
		User::verifyLogin();
	    $page = new PageAdmin();
	    $page->setTpl("users-create");
	});
	//Prepara gravação do usuario (via post espera receber dados para grava no bd)
	$app->post("/admin/users/create", function() {
		User::verifyLogin();
		//var_dump($_POST);
		$user = new User();

		$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

		$user->setData($_POST);
		//var_dump($user);
		$user->save();
		header("Location: /admin/users");
		exit;
	});

	//Exibe tela com usuario especifico
	$app->get("/admin/users/update/:iduser", function($iduser) {
		User::verifyLogin();
		//var_dump($iduser);

		$user = new User();
		$user->get((int)$iduser);

	    $page = new PageAdmin();
	    $page->setTpl("users-update", array("user"=>$user->getData()));
	});
	//Efetiva gravação do usuario (via post espera receber dados para grava no bd)
	$app->post("/admin/users/update/:iduser", function($iduser) {
		User::verifyLogin();
		$user = new User();

		$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

		$user->get((int)$iduser);
		$user->setData($_POST);

		$user->update();
		header("Location: /admin/users");
		exit;
	});

	$app->get("/admin/users/delete/:iduser", function($iduser)
	{
		User::verifyLogin();
		$user = new User();
		$user->get((int)$iduser);

		$user->delete();
		header("Location: /admin/users");
		exit;
	});

	$app->get("/admin/forgot", function()
	{
	    $page = new PageAdmin([
	    	"header"=>false,
	    	"footer"=>false
	    ]);
	    $page->setTpl("forgot");
	});
	$app->post("/admin/forgot", function()
	{
		$user = User::getForgot($_POST["email"]);
		header("Location: /admin/forgot/sent");
		exit;
	});
	$app->get("/admin/forgot/sent", function()
	{
	    $page = new PageAdmin([
	    	"header"=>false,
	    	"footer"=>false
	    ]);
	    $page->setTpl("forgot-sent");
	});

	$app->get("/admin/forgot/reset", function()
	{
		$user = User::validForgotDecrypt($_GET["code"]);
		
	    $page = new PageAdmin([
	    	"header"=>false,
	    	"footer"=>false
	    ]);

	    $page->setTpl("forgot-reset", array(
	    	"name"=>$user["desperson"],
	    	"code"=>$_GET["code"]
	    ));
	});
	$app->post("/admin/forgot/reset", function()
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

	    $page = new PageAdmin([
	    	"header"=>false,
	    	"footer"=>false
	    ]);

	    $page->setTpl("forgot-reset-success");		
	});

	$app->run();
 ?>