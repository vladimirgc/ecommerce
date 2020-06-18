<?php 

namespace Hcode;

class PageAdmin extends Page {

	public function __construct($opts = array(), $tpl_dir = "/views/admin/")
	{

		parent::__construct($opts, $tpl_dir); //chama construtor da classe base no caso a base.php

	}

}

?>