<?php 
	namespace tsh\Model;

	use \tsh\DB\Sql;
	use \tsh\Model;
	use \tsh\Model\Cart;

	class Order extends Model
	{
		public function save()
		{
			//*var_dump($this->getidaddress());exit;
			$sql = new Sql();
			$rst = $sql->select(
				"CALL sp_orders_save(
					:idorder, :idcart, :iduser,
					:idstatus, :idaddress, :vltotal)",
				[':idorder'=>$this->getidorder(),
				 ':idcart'=>$this->getidcart(),
				 ':iduser'=>$this->getiduser(),
				 ':idstatus'=>$this->getidstatus(),
				 ':idaddress'=>$this->getidaddress(),
				 ':vltotal'=>$this->getvltotal()]
			);
			if (count($rst) > 0) {
				$this->setData($rst[0]);
			}
		}

		public function get($idorder)
		{
			$sql = new Sql();
			$rst = $sql->select(
				"SELECT *
				   FROM TB_ORDERS ORD
				        JOIN TB_ORDERSSTATUS OST
				          ON OST.IDSTATUS = ORD.IDSTATUS
				        JOIN TB_CARTS CAR
				          ON CAR.IDCART = ORD.IDCART
				        JOIN TB_USERS USR
				          ON USR.IDUSER = ORD.IDUSER
				        JOIN TB_ADDRESSES ADR
   				          ON ADR.IDADDRESS = ORD.IDADDRESS
				        JOIN TB_PERSONS PER
				          ON PER.IDPERSON = USR.IDPERSON
				  WHERE ORD.IDORDER = :idorder",
				[':idorder'=>$idorder]
			);
			if (count($rst) > 0) {
				$this->setData($rst[0]);
			}
		}

		public static function listAll()
		{
			$sql = new Sql();
			$rst = $sql->select(
				"SELECT *
				   FROM TB_ORDERS ORD
				        JOIN TB_ORDERSSTATUS OST
				          ON OST.IDSTATUS = ORD.IDSTATUS
				        JOIN TB_CARTS CAR
				          ON CAR.IDCART = ORD.IDCART
				        JOIN TB_USERS USR
				          ON USR.IDUSER = ORD.IDUSER
				        JOIN TB_ADDRESSES ADR
   				          ON ADR.IDADDRESS = ORD.IDADDRESS
				        JOIN TB_PERSONS PER
				          ON PER.IDPERSON = USR.IDPERSON
				  ORDER BY ORD.DTREGISTER DESC"
			);
			return $rst;
		}

		public function delete()
		{
			$sql = new Sql();
			$sql->query(
				"DELETE FROM TB_ORDERS WHERE idorder = :idorder",
				[':idorder'=>$this->getidorder()]
			);
		}		

		public function getCart():Cart
		{
			$cart = new Cart();
			$cart->get((int)$this->getidcart());
			return $cart;
		}

		public static function listPage($page=1, $itemPerPage=10, $search="")
		{
			$start = ($page-1) * $itemPerPage;	
			$sql = new Sql();
			$rst = $sql->select(
				"SELECT *
				   FROM TB_ORDERS ORD
				        JOIN TB_ORDERSSTATUS OST
				          ON OST.IDSTATUS = ORD.IDSTATUS
				        JOIN TB_CARTS CAR
				          ON CAR.IDCART = ORD.IDCART
				        JOIN TB_USERS USR
				          ON USR.IDUSER = ORD.IDUSER
				        JOIN TB_ADDRESSES ADR
   				          ON ADR.IDADDRESS = ORD.IDADDRESS
				        JOIN TB_PERSONS PER
				          ON PER.IDPERSON = USR.IDPERSON				  
				"
				.(($search=="") ? "" : 
				" WHERE ord.idorder = :id OR PER.desperson LIKE :search").
				" ORDER BY ORD.DTREGISTER DESC
				  LIMIT $start, $itemPerPage",
				[':id'=>$search,
				 ':search'=>$search]
			);

			$rstTotal = $sql->select("SELECT FOUND_ROWS() AS RSTTOTAL");

			return [
				'data'=>$rst,
				'total'=>(int)$rstTotal[0]["RSTTOTAL"],
				'pages'=>ceil($rstTotal[0]["RSTTOTAL"] / $itemPerPage)
			];         //ceil arrendonda para cima
		}
	}
?>