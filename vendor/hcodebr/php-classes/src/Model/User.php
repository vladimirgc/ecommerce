<?php 

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;

class User extends Model {

	const SESSION = "User";

	protected $fields = [
		"iduser", "idperson", "deslogin", "despassword", "inadmin", "dtergister"
	];

	public static function login($login, $password):User
	{

		$db = new Sql();

		$results = $db->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if (count($results) === 0) {
			throw new \Exception("Não foi possível fazer login.");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"])) {

			$user = new User();
			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues(); //colocando na sessao dados do objeto User como array

			return $user;

		} else {

			throw new \Exception("Não foi possível fazer login."); //Classe Exception é do nivel abaixo

		}

	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}

	public static function verifyLogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION]) //nao foi definida a Session
			|| 
			!$_SESSION[User::SESSION] // se for falsa
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 // o ID do usuario nao for maior que zero
			||
			(bool)$_SESSION[User::SESSION]["iduser"] !== $inadmin // usuario da administracao bool - converte para booleano
		) {
			
			header("Location: /admin/login");// redireciona para login
			exit;

		}

	}

}

 ?>