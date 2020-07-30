<?php 

function post($key)
{
	return str_replace("'", "", $_POST[$key]);
}
function get($key)
{
	return str_replace("'", "", $_GET[$key]);
}

function formatPrice($vlprice)
{
	if (!$vlprice > 0) $vlprice = 0;
	return number_format($vlprice, 2, ",", ".");
}

 ?>