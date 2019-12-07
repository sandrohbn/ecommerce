<?php
	use \tsh\PageAdmin;
	use \tsh\Model\User;
	use \tsh\Model\Order;
	use \tsh\Model\OrderStatus;

	const MSGRGNGADM001 = "Informe o status atual";
	const MSGSUCCADM001 = "Status atualizado";

	$app->get("/admin/orders/:idorder/status", function($idorder){
		User::verifyLogin();
		$order = new Order();
		$order->get((int)$idorder);

		$page = new PageAdmin();
		$page->setTpl(
			"order-status",
			['order'=>$order->getData(),
			 'status'=>OrderStatus::listAll(),
			 'msgSuccess'=>getMsgSuccess(),
			 'msgError'=>getMsgError()]
		);
	});

	$app->post("/admin/orders/:idorder/status", function($idorder){
		User::verifyLogin();

		$msgRgNgAdm = NULL;

		if (!isset($_POST['idstatus']) || !((int)$_POST['idstatus'] > 0) ) {
			$msgRgNgAdm = MSGRGNGADM001;
		}

		//*var_dump($address);exit;
		if ($msgRgNgAdm != NULL) {
			//*var_dump($msgRgNgAdm);exit;
			setMsgError($msgRgNgAdm);
			header('Location: /admin/orders/'.$idorder.'/status');
			exit;
		}		

		$order = new Order();
		$order->get((int)$idorder);
		$order->setidstatus((int)$_POST['idstatus']);
		$order->save();

		setMsgSuccess(MSGSUCCADM001);

		header('Location: /admin/orders/'.$idorder.'/status');
		exit;		
	});

	$app->get("/admin/orders/:idorder/delete", function($idorder){
		User::verifyLogin();
		$order = new Order();
		$order->get((int)$idorder);
		$order->delete();
		header("Location: /admin/orders");
		exit;
	});

	$app->get("/admin/orders/:idorder", function($idorder){
		User::verifyLogin();
		$order = new Order();
		$order->get((int)$idorder);

		$cart = $order->getCart();
		
		$page = new PageAdmin();
		$page->setTpl(
			"order",
			['order'=>$order->getData(),
			 'cart'=>$cart->getData(),
			 'products'=>$cart->getProducts()]
		);
	});

	$app->get("/admin/orders", function(){
		User::verifyLogin();

		$search = (isset($_GET['search'])) ? $_GET['search'] : "";
		$page = (isset($_GET['page'])) ? $_GET['page'] : 1;

		$pagination = Order::listPage($page, LINHAPORPAGINA, $search);

		$pages = [];

		for ($x = 0; $x < $pagination['pages']; $x++)
		{
			array_push($pages, [
				'href'=>'/admin/orders?'.http_build_query([
					'page'=>$x+1,
					'search'=>$search
				]),
				'text'=>$x+1
			]);
		}

		$page = new PageAdmin();
		$page->setTpl("orders", [
	    	"orders"=>$pagination['data'],
	    	"search"=>$search,
	    	"pages"=>$pages
		]);
	});	
?>