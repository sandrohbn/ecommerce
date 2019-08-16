<?php //arquivo de configuraçao
	use \tsh\Page;

	$app->get("/", function() {
	    $page = new Page();
	    $page->setTpl("index");
	});
?>