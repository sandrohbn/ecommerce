<?php 
	namespace tsh\Model;

	use \tsh\DB\Sql;
	use \tsh\Model;
	use \tsh\Mailer;

	class User extends Model
	{
		const SESSION = "User";
		//*? SECRET avaliar gravar bd ou ini.cfg criptografado (tam fixo 16,24, e outros)
		const SECRET = ":SECRET"; 		 //usada em "Esqueci a senha"
		const ALGORITMO = "AES-128-CBC"; //usada em "Esqueci a senha"
		const MSGEXPTUSER001 = "Usuário ou senha inválidos";
		const MSGEXPTUSER002 = "Não foi possivel recuperar a senha";
		const ERROR_REGISTER = "UserErrorRegister";

		public static function getFromSession()
		{
			$user = new User();

			if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0)
			{
				$user->setData($_SESSION[User::SESSION]);
			}
			return $user; //Se nao carregou vai retorna vazio ou nulo
		}		

		public static function login($login, $password)
		{
			$sql = new Sql();
				//"SELECT * FROM tb_users WHERE deslogin = :LOGIN"
			$rst = $sql->select("
				SELECT *
				  FROM tb_users usr
				       INNER JOIN tb_persons prs
				          ON prs.idperson = usr.idperson				
				 WHERE usr.deslogin = :LOGIN
				", 
				array(":LOGIN"=>$login)
			);
			if (count($rst) === 0)
			{
				throw new \Exception(User::MSGEXPTUSER001);
			}

			$data = $rst[0];

			if (password_verify($password, $data["despassword"]) === true)
			{
				$user = new User();

				$data['desperson'] = utf8_encode($data['desperson']);//tras do bd faz o encode

				/*de campo a campo
				$user->setiduser($data["iduser"]);
				*/
				$user->setData($data);

				$_SESSION[User::SESSION] = $user->getData();

				return $user;
			}
			else
			{
				throw new \Exception(User::MSGEXPTUSER001);
			}
		}

		public static function checkLogin($inadmin = true) //$inadmin true é rota EXCLUSIVA administração
		{
			if (!isset($_SESSION[User::SESSION]) || //seção !NAO! definida (NAO LOGADO) ou 
				!$_SESSION[User::SESSION] ||		//esta definida mas esta vazia
				!(int)$_SESSION[User::SESSION]["iduser"] > 0) //NAO ! é maoir zero
			{
				return false; //não esta logado
			} 
			elseif (($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) ||
				   //(é rota EXCLUSIVA e esta logado como administrador) ou  
		     	   ($inadmin === false)) { //NAO é rota EXCLUSIVA da administração (exemplo carrinho)
				return true;
			} else {
				return false; //NÃO esta logado
			}
		}

		public static function verifyLogin($inadmin = true)
		{		//*? ! redirecionar?
			if (!User::checkLogin($inadmin)) //nem sempre é necessário redirecionar para login
			{
				if ($inadmin) {
					header("Location: /admin/login");
				} else {
					header("Location: /login");
				}
				exit;
			}
		}

		public static function logout()
		{
			$_SESSION[User::SESSION] = NULL;
		}

		public static function listAll()
		{
			$slc = "
				SELECT usr.*, prs.*
				  FROM tb_users usr
				       INNER JOIN tb_persons prs
				          ON prs.idperson = usr.idperson
				 ORDER BY prs.desperson
			";
			/*
			$slc = "SELECT * FROM tb_users usr INNER JOIN tb_persons prs USING (idperson) ORDER BY prs.desperson";
			*/
			$sql = new Sql();
			return $sql->select($slc);
		}

		public function save()
		{
			$prc = "CALL sp_users_save(" . 
				   "  :desperson, :deslogin, :despassword," .
				   "  :desemail, :nrphone, :inadmin)";
			$sql = new Sql();
			$rsl = $sql->select($prc, array(
				":desperson"=>utf8_decode($this->getdesperson()),//grava bd faz o decode 
				":deslogin"=>$this->getdeslogin(), 
				":despassword"=>User::getPasswordHash($this->getdespassword()),
				":desemail"=>$this->getdesemail(), 
				":nrphone"=>$this->getnrphone(), 
				":inadmin"=>$this->getinadmin()
			));

			$this->setData($rsl[0]);
		}

		public function get($iduser)
		{
			$slc = "
				SELECT *
				  FROM tb_users usr
				       INNER JOIN tb_persons prs
         		   		  ON prs.idperson = usr.idperson
				 WHERE usr.iduser = :iduser
			";
			$sql = new Sql();
			$rsl = $sql->select($slc, array(
				":iduser"=>$iduser
			));

			$data = $rsl[0];

			$data['desperson'] = utf8_encode($data['desperson']);//tras do bd faz encode

			$this->setData($data);
		}

		public function update()
		{
			$prc = "CALL sp_usersupdate_save(" .
				   "  :iduser," .
				   "  :desperson, :deslogin, :despassword," .
				   "  :desemail, :nrphone, :inadmin)";
			$sql = new Sql();
			$rsl = $sql->select($prc, array(
				":iduser"=>$this->getiduser(),
				":desperson"=>utf8_decode($this->getdesperson()),//grava no bd faz decode
				":deslogin"=>$this->getdeslogin(), 
				":despassword"=>User::getPasswordHash($this->getdespassword()),
				":desemail"=>$this->getdesemail(), 
				":nrphone"=>$this->getnrphone(), 
				":inadmin"=>$this->getinadmin()
			));

			$this->setData($rsl[0]);
		}		

		public function delete()
		{
			$prc = "CALL sp_users_delete(:iduser)";
			$sql = new Sql();
			$rsl = $sql->select($prc, array(
				":iduser"=>$this->getiduser()
			));
		}

		public static function getForgot($email)
		{
			/*
			$slc = "SELECT *
				      FROM tb_persons prs
				           INNER JOIN tb_users usr USING(idperson)
				     WHERE prs.desemail = :email";
			*/
			$slc = "
			  SELECT prs.*, usr.*
				FROM tb_persons prs
				     INNER JOIN tb_users usr
         		   	   ON usr.idperson = prs.idperson
			   WHERE prs.desemail = :email
			";
			$sql = new Sql();			
			$rsl = $sql->select($slc, array(
				":email"=>$email
			));

			if (count($rsl) === 0)
			{
				throw new \Exception(User::MSGEXPTUSER002);
			}
			else
			{
				$dataPersonsUsers = $rsl[0];

				$prc = "CALL sp_userspasswordsrecoveries_create(:iduser, :desip)";

				$rsl = $sql->select($prc, array(
					":iduser"=>$dataPersonsUsers["iduser"],
					":desip"=>$_SERVER["REMOTE_ADDR"]
				));

				if (count($rsl) === 0)
				{
					throw new \Exception(User::MSGEXPTUSER002);
				}
				else
				{
					$dataUsersPasswordsRecoveries = $rsl[0];
					/*
					mcrypt_encrypt compativel até VERSAO 7.1.?
					$code = base64_encode(mcrypt_encrypt(
						MCRYPT_RIJNDAEL_128,
						User::SECRET,
						$dataUsersPasswordsRecoveries["idrecovery"],
						MCRYPT_MODE_ECB //eletronic code book
					));

					openssl_encrypt compativel VERSAO 7.2.?
					*/

				    $rmdSecret = openssl_random_pseudo_bytes(openssl_cipher_iv_length(User::ALGORITMO));

					$code = openssl_encrypt(
						$dataUsersPasswordsRecoveries["idrecovery"], //string que vai ser encriptada, 
						User::ALGORITMO, //algoritmo
						User::SECRET, //chave
						0, //forma de retorno 0 só encripta e não precisa retornar nada
						$rmdSecret
					);

				    $code = base64_encode($code . "::" . $rmdSecret);

					$lnk = "http://www.tshecommerce.com.br:81/admin/forgot/reset?code=$code";

					$mailer = new Mailer(
						$dataPersonsUsers["desemail"],
						$dataPersonsUsers["desperson"],
						"Redefinir Senha da TSH Loja",
						"forgot",
						array(
							"name"=>$dataPersonsUsers["desperson"],
							"link"=>$lnk
						)
					);

					if (!$mailer->send() ) {
						var_dump($mailer->send()); 
						exit;
					}

					return $dataPersonsUsers;			
				}
			}
		}

		public static function validForgotDecrypt($code)
		{			
			/*
			mcrypt_decrypt compativel até VERSAO 7.1.?
			$idrecovery = mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128, 
				User::SECRET, 
				base64_decode($code), 
				MCRYPT_MODE_ECB
			);

			openssl_decrypt compativel VERSAO 7.2.?
			*/
			list($encrypted, $iv) = explode('::', base64_decode($code));

			//var_dump($encrypted);
			//var_dump($iv);
			//exit;

    		$idrecovery = openssl_decrypt(
    			$encrypted, 
    			USER::ALGORITMO, 
    			USER::SECRET, 
    			0,  
    			$iv
    		);

			$sql = new Sql();

			$rsl = $sql->select(
				"SELECT upr.*, prs.*
				   FROM tb_userspasswordsrecoveries upr
				    	INNER JOIN tb_users usr
				   	    ON usr.iduser = upr.iduser
				  	    INNER JOIN tb_persons prs
				  	    ON prs.idperson = usr.idperson
				  WHERE upr.idrecovery = :idrecovery
				  AND   upr.dtrecovery IS NULL
				  AND   DATE_ADD(upr.dtregister, INTERVAL 1 HOUR) >= NOW()",
				 array(
				 	":idrecovery"=>$idrecovery)
			);

			if (count($rsl) === 0)
			{
				throw new \Exception(User::MSGEXPTUSER002);
			}
			else
			{
				return $rsl[0];	
			}
		}

		public static function setForgotUsed($idrecovery)
		{
			$sql = new Sql();
			$sql->query(
				"UPDATE tb_userspasswordsrecoveries
				    SET dtrecovery = NOW()
				  WHERE idrecovery = :idrecovery",
				array("idrecovery"=>$idrecovery)
			);
		}

		public function setPassword($password)
		{
			$sql = new Sql();
			$sql->query(
				"UPDATE tb_users 
					SET despassword = :password 
				  WHERE iduser = :iduser",
				array(
					":password"=>$password,
					":iduser"=>$this->getiduser()
				)
			);
		}

		//*avaliar necessidade de adaptacao desta funcao
		public static function getPasswordHash($password)
		{
			return password_hash($password, PASSWORD_DEFAULT, [
				'cost'=>12
			]);
		}

		public static function setErrorRegister($msg)
		{
			$_SESSION[User::ERROR_REGISTER] = $msg;
		}

		public static function getErrorRegister()
		{
			$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
			User::clearErrorRegister();
			return $msg;
		}

		public static function clearErrorRegister()
		{
			$_SESSION[User::ERROR_REGISTER] = NULL;
		}

		public static function checkLoginExist($login)
		{
			$sql = new Sql();
			$rst = $sql->select("
				SELECT * FROM tb_users WHERE deslogin = :deslogin", [
				':deslogin'=>$login
			]);
			//*var_dump($rst);exit;
			return (count($rst) > 0);
		}
	}
?>