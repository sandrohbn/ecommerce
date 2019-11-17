<?php 
	namespace tsh\Model;

	use \tsh\DB\Sql;
	use \tsh\Model;

	class Address extends Model
	{
		const SESSION_ERROR = "AddressError";

		public static function getCEP($nrcep)
		{
			$nrcep = str_replace("-", "", $nrcep);
			$ch = curl_init();//informa ao php que vai rastrear um url

			curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json/");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//true espera que retorne informacao
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//false nao exige autenticacao ssl

			$data = json_decode(curl_exec($ch), true);//true para serializar retornando array, false retornar como objeto

			curl_close($ch); //fecha ponteiro, senão a cada f5 chama o metodo e abre +uma referencia de memoria

			return $data;
		}

		public function loadFromCEP($nrcep)
		{
			$data = Address::getCEP($nrcep);

			//isset variavel esta definida e esta preenchida
			if (isset($data['logradouro']) && $data['logradouro'])
			{
				$this->setdesaddress($data['logradouro']);
				$this->setdescomplement($data['complemento']);
				$this->setdesdistrict($data['bairro']);
				$this->setdescity($data['localidade']);
				$this->setdesstate($data['uf']);
				$this->setdescountry('Brasil');
				$this->setdeszipcode($nrcep);
				//*var_dump($nrcep);exit;
			}
		}

		public function save()
		{
			//*var_dump($this->getidaddress());exit;
			$sql = new Sql();
			$rst = $sql->select(
				"CALL sp_addresses_save(
					:idaddress, :idperson, :desaddress,
					:descomplement, :descity, :desstate,
					:descountry, :deszipcode, :desdistrict)",
				[':idaddress'=>$this->getidaddress(),
				 ':idperson'=>$this->getidperson(),
				 ':desaddress'=>utf8_decode($this->getdesaddress()),//*!Fazer classe para gravar e converter utf8
				 ':descomplement'=>utf8_decode($this->getdescomplement()),
				 ':descity'=>utf8_decode($this->getdescity()),
				 ':desstate'=>utf8_decode($this->getdesstate()),
				 ':descountry'=>utf8_decode($this->getdescountry()),
				 ':deszipcode'=>$this->getdeszipcode(),
				 ':desdistrict'=>utf8_decode($this->getdesdistrict())]
			);
			if (count($rst) > 0) {
				$this->setData($rst[0]);
			}
		}

		public static function setMsgError($msg)
		{
			$_SESSION[Address::SESSION_ERROR] = $msg;
			//*var_dump($msg);exit;
		}

		public static function getMsgError()
		{
			$msg = (isset($_SESSION[Address::SESSION_ERROR]) ? $_SESSION[Address::SESSION_ERROR] : NULL);
			Address::clearMsgError();
			return $msg;
		}

		public static function clearMsgError()
		{
			$_SESSION[Address::SESSION_ERROR] = NULL;
		}
	}
?>