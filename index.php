<?php 
session_start();
require_once("vendor/autoload.php");
require_once("vendor/functions.php");

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

$app->get('/admin', function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});


$app->get('/admin/login', function() {
    
	$page = new PageAdmin([
		"header"=> false,
		"footer"=> false

	]);

	$page->setTpl("login");

});

$app->post('/admin/login', function(){
	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;

});

$app->get('/admin/logout', function(){
	User::logout();
	header("Location: /admin/login");
	exit;

});

$app->get('/admin/users', function(){
	User::verifyLogin(); //chama método estático da classe User
	$users = User::listAll();
	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users" => $users
	));

});

$app->get('/admin/users/create', function(){
	User::verifyLogin(); //chama método estático da classe User
	$page = new PageAdmin();
	$page->setTpl("users-create");

});

$app->get('/admin/users/:iduser/delete', function($iduser){//deletar usuario
	User::verifyLogin(); //chama método estático da classe User. Como tem o delete este dever vir acima e evitar que o Slim nao execute.

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
    exit; 

});


$app->get('/admin/users/:iduser', function($iduser){//passado iduser na rota
	User::verifyLogin();
 
   $user = new User();
 
   $user->get((int)$iduser);
 
   $page = new PageAdmin();
 
   $page ->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));

});

$app->post("/admin/users/create", function () {

 	User::verifyLogin();
 
    $user = new User();
    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
    $user->setData($_POST);
    $user->save(); 
 
    header("Location: /admin/users");
    exit; 

});


$app->post('/admin/users/:iduser', function($iduser){//atualizar usuario via post
	User::verifyLogin(); //chama método estático da classe User

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
    exit; 

});



$app->run();

 ?>