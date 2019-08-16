<?php //arquivo de configuraçao
	use \tsh\PageAdmin;
	use \tsh\Model\User;

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
?>