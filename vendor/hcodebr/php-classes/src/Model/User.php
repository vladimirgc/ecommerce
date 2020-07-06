<?php 

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;

class User extends Model {

	const SESSION = "User";
	protected $fields = [
		"iduser", "idperson", "desperson", "deslogin", "desemail", "nrphone", "despassword", "inadmin", "dtergister"
	]; //todos os CAMPOS usados no salvar e atualizar



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

		$_SESSION[User::SESSION] = NULL; //limppar sessao

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

	public static function listAll()
	{
		$sql = new Sql();
		return $sql-> select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING (idperson) ORDER BY b.desperson") ;

	}

	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);
		

	}

	public function get($iduser)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));

		$data = $results[0];

		$data["desperson"] = utf8_encode($data["desperson"]);


		$this->setData($data);

	}

	public function update()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);		

	}

	public function delete()
	{

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));

	}

}

 ?>