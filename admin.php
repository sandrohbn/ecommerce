<?php //arquivo de configuraçao
	use \tsh\PageAdmin;
	use \tsh\Model\User;

	const MSGRGNGADM011 = "Preencha a senha atual";
	const MSGRGNGADM012 = "Preencha a nova senha";
	const MSGRGNGADM013 = "Preencha a confirmação da nova senha";
	const MSGRGNGADM014 = "A nova senha deve ser diferente da atual";
	const MSGRGNGADM015 = "A nova senha deve ser igual a confirmação de senha";
	const MSGRGNGADM016 = "A senha atual está inválida";
	
	const MSGSUCCADM002 = "Senha alterada com sucesso!";

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