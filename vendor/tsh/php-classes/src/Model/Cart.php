<?php 
	namespace tsh\Model;

	use \tsh\DB\Sql;
	use \tsh\Model;
	use \tsh\Mailer;
	use \tsh\Model\User;

	class Cart extends Model
	{
		const SESSION = "Cart";

		public static function getFromSession()
		{
			$cart = new Cart();
			if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0)
			{
				$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
			} else {
				$cart->getFromSessionID();

				if (!(int)$cart->getidcart() > 0) {
					$data = [
						'dessessionid'=>session_id()
					];
					if (User::checkLogin(false)) {
						$user = User::getFromSession();
						$data['iduser'] = $user->getiduser();
					}
					$cart->setData($data);
					$cart->save();
					$cart->setToSession();
				}
			}
			return $cart;
		}

		public function setToSession() //não é static pq neste caso vou usar this...
		{
			$_SESSION[Cart::SESSION] = $this->getData();
		}

		public function getFromSessionID()
		{
			$sql = new Sql();
			$rst = $sql->select("SELECT * FROM TB_CARTS WHERE DESSESSIONID = :dessessionid",
				['dessessionid'=>session_id()]
			);
			if (count($rst) > 0) {
				$this->setData($rst[0]);
			}
		}

		public function get(int $idcart)
		{
			$sql = new Sql();
			$rst = $sql->select("SELECT * FROM TB_CARTS WHERE IDCART = :idcart",
				[':idcart'=>$idcart]
			);
			if (count($rst) > 0) {
				$this->setData($rst[0]);
			}
		}

		public function save()
		{
			$sql = new Sql();
			$rst = $sql->select("CALL sp_carts_save
				(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)",
				[':idcart'=>$this->getidcart(),
				 ':dessessionid'=>$this->getdessessionid(),
				 ':iduser'=>$this->getiduser(),
				 ':deszipcode'=>$this->getdeszipcode(),
				 ':vlfreight'=>$this->getvlfreight(),
				 ':nrdays'=>$this->getnrdays()]
				);
			$this->setData($rst[0]);
		}
	}
?>