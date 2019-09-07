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

		public function getProduct($related = true)
		{
			$sql = new Sql();
			return $sql->select(
				"SELECT PRD.* FROM TB_PRODUCTS PRD
				  WHERE PRD.IDPRODUCT " . (($related)?"":"NOT ") . "IN 
				       (SELECT PRD.IDPRODUCT 
				          FROM TB_PRODUCTS PRD
				               JOIN TB_PRODUCTSCATEGORIES PCA
				                 ON PCA.IDPRODUCT = PRD.IDPRODUCT
				         WHERE PCA.IDCATEGORY = :idcategory)",
				[':idcategory'=>$this->getidcategory()]
			);
		}

		public function addProduct(Product $product)
		{
			$sql = new Sql();
			$sql->query(
				"INSERT INTO TB_PRODUCTSCATEGORIES
					(IDCATEGORY, IDPRODUCT)
				 VALUES
				 	(:idcategory, :idproduct)",
				[':idcategory'=>$this->getidcategory(),
				 ':idproduct'=>$product->getidproduct()]
			);
		}

		public function removeProduct(Product $product)
		{
			$sql = new Sql();
			$sql->query(
				"DELETE FROM TB_PRODUCTSCATEGORIES
				  WHERE IDCATEGORY = :idcategory
				  AND   IDPRODUCT  = :idproduct",
				[':idcategory'=>$this->getidcategory(),
				 ':idproduct'=>$product->getidproduct()]
			);
		}
	}
?>