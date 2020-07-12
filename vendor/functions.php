<?php 

function post($key)
{
	return str_replace("'", "", $_POST[$key]);
}
function get($key)
{
	return str_replace("'", "", $_GET[$key]);
}

function formatPrice(float $vlprice)
{
	return number_format($vlprice, 2, ",", ".");
}

 ?>