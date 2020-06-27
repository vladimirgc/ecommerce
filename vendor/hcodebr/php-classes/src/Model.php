<?php 

namespace Hcode;

class Model {// Classe para otimizar get e set

	private $values = []; //todos dados 

	public function setData($data)
	{

		foreach ($data as $key => $value)
		{

			$this->{"set".$key}($value);//criar algo dinamico precisa vir entre chaves

		}

	}

	public function __call($name, $args)
	{

		$method = substr($name, 0, 3); // set ou get - 3 primeiros digitos do metodo
		$fieldName = substr($name, 3, strlen($name)); // a partir da 3 posicao do nome do metodo

		if (in_array($fieldName, $this->fields))
		{
			
			switch ($method)
			{

				case "get":
					return $this->values[$fieldName];
				break;

				case "set":
					$this->values[$fieldName] = $args[0];
				break;

			}

		}

	}

	public function getValues()
	{

		return $this->values;

	}

}

 ?>
