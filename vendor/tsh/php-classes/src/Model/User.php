<?php 
	namespace tsh\Model;

	use \tsh\DB\Sql;
	use \tsh\Model;

	class User extends Model
	{
		const SESSION = "User";

		private $msgErro = "Usuário ou senha inválidos";

		public static function login($login, $password)
		{
			$sql = new Sql();
			$result = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(":LOGIN"=>$login));

			if (count($result) === 0)
			{
				throw new \Exception($msgErro);
			}

			$data = $result[0];

			if (password_verify($password, $data["despassword"]) === true)
			{
				$user = new User();

				/*de campo a campo
				$user->setiduser($data["iduser"]);
				*/
				$user->setData($data);

				/*var_dump($user);
				exit;*/

				$_SESSION[User::SESSION] = $user->getData();

				return $user;
			}
			else
			{
				throw new \Exception($msgErro);
			}
		}

		public static function verifyLogin($inadmin = true)
		{
			if (!isset($_SESSION[User::SESSION]) //existe a seção
				||
				!$_SESSION[User::SESSION] //
				||
				!(int)$_SESSION[User::SESSION]["iduser"] > 0
				||
				(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin) 
			{
				header("Location: /admin/login");
				exit;
			}
		}

		public static function logout()
		{
			$_SESSION[User::SESSION] = NULL;
		}
	}
?>