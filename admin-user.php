<?php //arquivo de configuraçao
	use \tsh\PageAdmin;
	use \tsh\Model\User;

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
?>