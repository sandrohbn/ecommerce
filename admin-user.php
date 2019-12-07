<?php //arquivo de configuraçao
	use \tsh\PageAdmin;
	use \tsh\Model\User;

	const LINHAPORPAGINA = 10;

	$app->get("/admin/users/:iduser/password", function($iduser) {
		User::verifyLogin();
		//var_dump($iduser);
		$user = new User();
		$user->get((int)$iduser);
	    $page = new PageAdmin();
	    $page->setTpl("users-password", [
	    	'user'=>$user->getData(),
			'msgSuccess'=>getMsgSuccess(),
			'msgError'=>getMsgError()
	    ]);
	});

	$app->post("/admin/users/:iduser/password", function($iduser) {
		User::verifyLogin();

		$msgRgNgAdm = NULL;

		if (!isset($_POST['despassword']) || $_POST['despassword'] === '') {
			$msgRgNgAdm = MSGRGNGADM012;
		} else
		if (!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm'] === '') {
			$msgRgNgAdm = MSGRGNGADM013;
		} else
		if ($_POST['despassword'] !== $_POST['despassword-confirm']) {
			$msgRgNgAdm = MSGRGNGADM015;
		}

		if ($msgRgNgAdm !== NULL) {
			setMsgError($msgRgNgAdm);
			header("Location: /admin/users/$iduser/password");
			exit;
		}

		$user = new User();
		$user->get((int)$iduser);
		$user->setPassword(User::getPasswordHash($_POST['despassword']));

		setMsgSuccess(MSGSUCCADM002);

		header("Location: /admin/users/$iduser/password");
		exit;
	});

	$app->get("/admin/users", function() 
	{
		User::verifyLogin();

		$search = (isset($_GET['search'])) ? $_GET['search'] : "";
		$page = (isset($_GET['page'])) ? $_GET['page'] : 1;

		$pagination = User::listPage($page, LINHAPORPAGINA, $search);

		$pages = [];

		for ($x = 0; $x < $pagination['pages']; $x++)
		{
			array_push($pages, [
				'href'=>'/admin/users?'.http_build_query([
					'page'=>$x+1,
					'search'=>$search
				]),
				'text'=>$x+1
			]);
		}

	    $page = new PageAdmin();
	    $page->setTpl("users", array(
	    	"user"=>$pagination['data'],
	    	"search"=>$search,
	    	"pages"=>$pages
	    ));
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
	$app->get("/admin/users/:iduser/update", function($iduser) {
		User::verifyLogin();
		//var_dump($iduser);
		$user = new User();
		$user->get((int)$iduser);
	    $page = new PageAdmin();
	    $page->setTpl("users-update", array("user"=>$user->getData()));
	});

	//Efetiva gravação do usuario (via post espera receber dados para grava no bd)
	$app->post("/admin/users/:iduser/update", function($iduser) {
		User::verifyLogin();
		$user = new User();
		$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
		$user->get((int)$iduser);
		$user->setData($_POST);
		$user->update();
		header("Location: /admin/users");
		exit;
	});
	
	$app->get("/admin/users/:iduser/delete", function($iduser)
	{
		User::verifyLogin();
		$user = new User();
		$user->get((int)$iduser);
		$user->delete();
		header("Location: /admin/users");
		exit;
	});
?>