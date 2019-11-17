<?php //arquivo de configuraçao
	use \tsh\Model\User;

	const SESSION_ERROR = "Error";
	const SESSION_SUCCESS = "Success";

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
		//*var_dump($msg);exit;
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
	
	//public static 
	function setMsgSuccess($msg)
	{
		$_SESSION[SESSION_SUCCESS] = $msg;
	}

	//public static 
	function getMsgSuccess()
	{
		$msg = (isset($_SESSION[SESSION_SUCCESS]) ? $_SESSION[SESSION_SUCCESS] : NULL);
		clearMsgSuccess();
		return $msg;
	}

	//public static 
	function clearMsgSuccess()
	{
		$_SESSION[SESSION_SUCCESS] = NULL;
	}
?>