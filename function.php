<?php //arquivo de configuraçao
	use \tsh\Model\User;

	CONST SESSION_ERROR = "Error";

	function formatPrice(float $vlprice)
	{
		return number_format(($vlprice=''?0:$vlprice), 2, ",", ".");
	}

	function checkLogin($inadmin = true)
	{
		return User::checkLogin($inadmin);
	}

	function getUserName()
	{
		$user = User::getFromSession();
		//*var_dump($user);exit;
		return $user->getdesperson();
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