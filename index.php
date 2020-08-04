<?php 
session_start();
require_once("vendor/autoload.php");
require_once("vendor/functions.php");

//funciona como import
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;

$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$products = Product::listAll();

	$page = new Page();

	$page->setTpl("index", [
		"products"=>Product::checkList($products)
	]);

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

$app->get("/admin/forgot", function(){

	$page = new PageAdmin([
		"header"=> false,
		"footer"=> false

	]);

	$page->setTpl("forgot");

});

$app->post("/admin/forgot", function(){

$user = User::getForgot($_POST["email"]);

header("Location: /admin/forgot/sent");
exit; 

});

$app->get("/admin/forgot/sent", function(){
	$page = new PageAdmin([
		"header"=> false,
		"footer"=> false

	]);

	$page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);
	$page = new PageAdmin([
		"header"=> false,
		"footer"=> false

	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]

	));

});

$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setFogotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);
	//campo password vindo forgot-reset.html
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12 //contidade de processamento. Quanto maior mais protegido, mas usa mais recurso do servidor
	]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=> false,
		"footer"=> false

	]);

	$page->setTpl("forgot-reset-success");

});

$app->get("/admin/categories", function(){

	User::verifyLogin(); //chama método estático da classe User. Verifica se usuario está logado. Evita acessar direto URL.

	$categories = Category::listAll();

	$page = new PageAdmin();
	$page->setTpl("categories", [
		"categories"=>$categories //pagina de categorias recebe array
	]);

});

$app->get("/admin/categories/create", function(){
	User::verifyLogin(); //chama método estático da classe User
	$page = new PageAdmin();
	$page->setTpl("categories-create");

});

$app->post("/admin/categories/create", function(){

	User::verifyLogin(); //chama método estático da classe User
	$category = new Category();
	$category->setData($_POST);
	$category->save();
	header("Location: /admin/categories");
	exit; 
	

});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin(); //chama método estático da classe User

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories");
	exit;

});

$app->get("/admin/categories/:idcategory", function($idcategory){
	User::verifyLogin(); //chama método estático da classe User
	$category = new Category();
	$category->get((int)$idcategory); //necessario converte para inteiro por tudo que vem pela URL é texto

	$page = new PageAdmin();
	$page->setTpl("categories-update", [
		"category"=>$category->getValues()
	]);	

});

$app->post("/admin/categories/:idcategory", function($idcategory){
	User::verifyLogin(); //chama método estático da classe User
	$category = new Category();
	$category->get((int)$idcategory); //necessario converte para inteiro por tudo que vem pela URL é texto
	$category->setData($_POST);
	$category->save();
	header("Location: /admin/categories");
	exit;

});

$app->get("/categories/:idcategory", function($idcategory){
	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;
	$category = new Category();
	$category->get((int)$idcategory); //necessario converte para inteiro por tudo que vem pela URL é texto
	
	$pagination = $category->getProductsPage($page);
	$pages = [];
	for ($i=1; $i <=$pagination["pages"] ; $i++) { 
		array_push($pages, [
			"link"=>"/categories/".$category->getidcategory()."?page=".$i,
			"page"=>$i
		]);
	}
	$page = new Page();
	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>$pagination["data"],
		"pages"=>$pages
	]);
});

$app->get("/admin/products", function(){
	User::verifyLogin(); //chama método estático da classe User
	$products = Product::listAll();
	$page = new PageAdmin();
	$page->setTpl("products", [
		"products"=>$products
	]);	

});

$app->get("/admin/products/create", function(){
	User::verifyLogin(); //chama método estático da classe User
	$page = new PageAdmin();
	$page->setTpl("products-create");

});

$app->post("/admin/products/create", function(){
	User::verifyLogin(); //chama método estático da classe User
	$product = new Product();
	$product->setData($_POST);
	$product->save();
	header("Location: /admin/products");
	exit;
});


$app->get("/admin/products/:idproduct", function($idproduct){
	User::verifyLogin(); //chama método estático da classe User
	$product = new Product();
	$product->get((int)$idproduct);
	$page = new PageAdmin();
	$page->setTpl("products-update", [
		"product"=>$product->getValues()

	]);

});

$app->post("/admin/products/:idproduct", function($idproduct){
	User::verifyLogin(); //chama método estático da classe User
	$product = new Product();
	$product->get((int)$idproduct);
	$product->setData($_POST);
	$product->save();
	if ($_FILES["file"]["name"] != "")
		$product->setPhoto($_FILES["file"]);
	header("Location: /admin/products");
	exit;

});

$app->get("/admin/products/:idproduct/delete", function($idproduct){
	User::verifyLogin(); //chama método estático da classe User
	$product = new Product();
	$product->get((int)$idproduct);
	$product->delete();
	header("Location: /admin/products");
	exit;
});

$app->get("/admin/categories/:idcategory/products", function($idcategory){
	User::verifyLogin(); //chama método estático da classe User
	$category = new Category();
	$category->get((int)$idcategory); //necessario converte para inteiro por tudo que vem pela URL é texto
	$page = new PageAdmin();
	$page->setTpl("categories-products",[
		"category"=>$category->getValues(),
		"productsRelated"=>$category->getProducts(),
		"productsNotRelated"=>$category->getProducts(false)
	]);

});

$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){
	User::verifyLogin(); //chama método estático da classe User
	$category = new Category();
	$category->get((int)$idcategory);
	$product = new Product();
	$product->get((int)$idproduct);
	$category->addProduct($product);
	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){
	User::verifyLogin(); //chama método estático da classe User
	$category = new Category();
	$category->get((int)$idcategory);
	$product = new Product();
	$product->get((int)$idproduct);
	$category->removeProduct($product);
	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

$app->get("/products/:desurl", function($desurl){
	$product = new Product();
	$product->getFromURL($desurl);
	$page = new Page();
	$page->setTpl("product-detail", [
		"product"=>$product->getValues(),
		"categories"=>$product->getCategories()
	]);

});

$app->get("/cart", function(){
	$cart = Cart::getFromSession();
	$page = new Page();
	$page->setTpl("cart", [
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts(),
		"error"=>Cart::getMsgError()
	]);

});

$app->get("/cart/:idproduct/add", function($idproduct){
	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession(); //recuperar carrinho da sessao ou novo
	//tratamento para adicionar no carrinho a qtd informada na pagina de detalhes do produto
	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i = 0; $i < $qtd; $i++) {
		
		$cart->addProduct($product);

	} 

	header("Location: /cart"); //redireciona para pagina do carrinho
	exit;
});

$app->get("/cart/:idproduct/minus", function($idproduct){//rota para remover apenas 1 produto
	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession(); //recuperar carrinho da sessao ou novo 
	$cart->removeProduct($product);
	header("Location: /cart"); //redireciona para pagina do carrinho
	exit;
});

$app->get("/cart/:idproduct/remove", function($idproduct){//rota para remover toda quantidade do mesmo produto
	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession(); //recuperar carrinho da sessao ou novo 
	$cart->removeProduct($product, true);
	header("Location: /cart"); //redireciona para pagina do carrinho
	exit;
});

$app->post("/cart/freight", function(){
	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;
});

$app->get("/checkout", function(){
	User::verifyLogin(false);
	$cart = Cart::getFromSession();
	$address = new Address();
	$page = new Page();
	$page->setTpl("checkout", [
		"cart"=>$cart->getValues(),
		"address"=>$address->getValues()
	]);
});

$app->get("/login", function(){
	
	$page = new Page();
	$page->setTpl("login", [
		"error"=>User::getError(),
		"errorRegister"=>User::getErrorRegister(),
		"registerValues"=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);
});

$app->post("/login", function(){
	try{
	User::login($_POST['login'], $_POST['password']);
	}
	catch(Exception $ex){
		User::setError($ex->getMessage());
	}

	header("Location: /checkout");
	exit;
});

$app->get("/logout", function(){
	User::logout();
	header("Location: /login");
	exit;
});

$app->post("/register", function(){

	$_SESSION['registerValues'] = $_POST;

	if (!isset($_POST['name']) || $_POST['name'] == '') {

		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;

	}

	if (!isset($_POST['email']) || $_POST['email'] == '') {

		User::setErrorRegister("Preencha o seu e-mail.");
		header("Location: /login");
		exit;

	}

	if (!isset($_POST['password']) || $_POST['password'] == '') {

		User::setErrorRegister("Preencha a senha.");
		header("Location: /login");
		exit;

	}

	if (User::checkLoginExist($_POST['email']) === true) {

		User::setErrorRegister("Este endereço de e-mail já está sendo usado por outro usuário.");
		header("Location: /login");
		exit;

	}

	$user = new User();
	$user->setData([
		"inadmin"=>0, //forçar 0 pois usuario nao é administrador
		"deslogin"=>$_POST['email'],
		"desperson"=>$_POST['name'],
		"desemail"=>$_POST['email'],
		"despassword"=>$_POST['password'],
		"nrphone"=>$_POST['phone']

	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header('Location: /checkout');
	exit;

});

$app->get("/forgot", function() {

	$page = new Page();

	$page->setTpl("forgot");	

});

$app->post("/forgot", function(){

	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");
	exit;

});

$app->get("/forgot/sent", function(){

	$page = new Page();

	$page->setTpl("forgot-sent");	

});


$app->get("/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post("/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);	

	User::setFogotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = User::getPasswordHash($_POST["password"]);

	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-success");

});

$app->run();



?>