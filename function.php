<?php //arquivo de configuraçao
	CONST SESSION_ERROR = "Error";

	function formatPrice(float $vlprice)
	{
		return number_format($vlprice, 2, ",", ".");
	}

	//public static 
	function formatValueToDecimal($value):float
	{
		$value = str_replace('.', '', $value);
		return str_replace(',', '.', $value);
	}

	//public static 
	function setMsgError($msg)
	{
		$_SESSION[SESSION_ERROR] = $msg;
	}

	//public static 
	function getMsgError()
	{
		$msg = (isset($_SESSION[SESSION_ERROR]) ? $_SESSION[SESSION_ERROR] : NULL);
		clearMsgError();
		return $msg;
	}

	//public static 
	function clearMsgError()
	{
		$_SESSION[SESSION_ERROR] = NULL;
	}
?>