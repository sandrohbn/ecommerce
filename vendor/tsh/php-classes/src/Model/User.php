<?php 
	namespace tsh\Model;

	use \tsh\DB\Sql;
	use \tsh\Model;
	use \tsh\Mailer;

	class User extends Model
	{
		const SESSION = "User";
		/*? Avaliar gravar bd ou ini.cfg criptografado
		const SECRET = ":SECRET"; //tam fixo 16,24, e outros
		const ALGORITMO = ":ALGORITMO";
		*/
		const MSGEXPTUSER001 = "Usuário ou senha inválidos";
		const MSGEXPTUSER002 = "Não foi possivel recuperar a senha";

		public static function login($login, $password)
		{
			$sql = new Sql();
			$result = $sql->select(
				"SELECT * FROM tb_users WHERE deslogin = :LOGIN", 
				array(":LOGIN"=>$login));

			if (count($result) === 0)
			{
				throw new \Exception(User::MSGEXPTUSER001);
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
				throw new \Exception(User::MSGEXPTUSER001);
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
				header("Location: /admin/login/");
				exit;
			}
		}

		public static function logout()
		{
			$_SESSION[User::SESSION] = NULL;
		}

		public static function listAll()
		{
			$slc = "SELECT *" .
				   "  FROM tb_users usr" .
				   "       INNER JOIN tb_persons prs" .
         		   "		  ON prs.idperson = usr.idperson" .
				   " ORDER BY prs.desperson";
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
				":desperson"=>$this->getdesperson(), 
				":deslogin"=>$this->getdeslogin(), 
				":despassword"=>$this->getdespassword(),
				":desemail"=>$this->getdesemail(), 
				":nrphone"=>$this->getnrphone(), 
				":inadmin"=>$this->getinadmin()
			));

			$this->setData($rsl[0]);
		}

		public function get($iduser)
		{
			$slc = "SELECT *" .
				   "  FROM tb_users usr" .
				   "       INNER JOIN tb_persons prs" .
         		   "		  ON prs.idperson = usr.idperson" .
				   " WHERE usr.iduser = :iduser";
			$sql = new Sql();
			$rsl = $sql->select($slc, array(
				":iduser"=>$iduser
			));

			$this->setData($rsl[0]);
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
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(), 
				":despassword"=>$this->getdespassword(),
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
			  SELECT *
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
				    $rmdSecretIV = openssl_random_pseudo_bytes(openssl_cipher_iv_length(User::ALGORITMO)); // generate 16  random bytes

					$code = openssl_encrypt(
						$dataUsersPasswordsRecoveries["idrecovery"], //string que vai ser encriptada, 
						User::ALGORITMO, //algoritmo
						User::SECRET, //chave
						0, //forma de retorno 0 só encripta e não precisa retornar nada
						$rmdSecretIV
					);

				    $code = base64_encode($code . "::" . $rmdSecretIV);

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

					//var_dump($mailer->send()); exit;

					$mailer->send();

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
				"SELECT *
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
	}
?>