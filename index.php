<?php 

require_once("vendor/autoload.php");

use \Hcode\Page;

$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	//$sql = new \Hcode\DB\Sql();

	//$results = $sql->select("SELECT * FROM tb_users");

	//echo json_encode($results);

	//echo "ok"; 

	$page = new Page();

	$page->setTpl("index");

});

$app->run();

 ?>