<?php 
	namespace tsh\Model;

	use \tsh\DB\Sql;
	use \tsh\Model;
	use \tsh\Mailer;

	class Category extends Model
	{
		public static function listAll()
		{
			$sql = new Sql();
			return $sql->select("SELECT * FROM tb_categories ctg ORDER BY ctg.descategory");
		}

		public function save()
		{
			$sql = new Sql();
			$rsl = $sql->select(
			 "CALL sp_categories_save(:idcategory, :descategory)",
			  array(
				":idcategory"=>$this->getidcategory(), 
				":descategory"=>$this->getdescategory()
			));
			$this->setData($rsl[0]);
		}

		public function get($idcategory)
		{
			$sql = new Sql();
			$rsl = $sql->select(
			 "SELECT * FROM TB_CATEGORIES WHERE IDCATEGORY = :idcategory",
			 [':idcategory'=>$idcategory] 
			);
			$this->setData($rsl[0]);
		}

		public function delete()
		{
			$sql = new Sql();
			$sql->query(
			 "DELETE FROM TB_CATEGORIES WHERE IDCATEGORY = :idcategory",
			 [':idcategory'=>$this->getidcategory()] 
			);
		}
	}
?>