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
			Category::updateFile();
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
			Category::updateFile();
		}

		public static function updateFile()
		{
			$category = Category::listAll();
			$html = [];
			foreach ($category as $row) {
				array_push($html, '<li><a href="/category/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
			}
			file_put_contents(
				$_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."view".DIRECTORY_SEPARATOR."category-menu.html", 
				implode('', $html)
			);
		}
	}
?>