<?php 
	namespace tsh\Model;

	use \tsh\DB\Sql;
	use \tsh\Model;
	//*-use \tsh\Mailer;

	class Product extends Model
	{
		public static function listAll()
		{
			$sql = new Sql();
			return $sql->select("SELECT * FROM tb_products prd ORDER BY prd.desproduct");
		}

		public function save()
		{
			$sql = new Sql();
			$rsl = $sql->select(
			 "CALL sp_products_save(
			 	:idproduct, :desproduct, :vlprice, :vlwidth,
				:vlheight, :vllength, :vlweight, :desurl)",
			  array(
				":idproduct"=>$this->getidproduct(), 
				":desproduct"=>$this->getdesproduct(),
				":vlprice"=>$this->getvlprice(), 
				":vlwidth"=>$this->getvlwidth(),
				":vlheight"=>$this->getvlheight(), 
				":vllength"=>$this->getvllength(),
				":vlweight"=>$this->getvlweight(), 
				":desurl"=>$this->getdesurl()
			));
			$this->setData($rsl[0]);
		}

		public function get($idproduct)
		{
			$sql = new Sql();
			$rsl = $sql->select(
			 "SELECT * FROM TB_PRODUCTS WHERE IDPRODUCT = :idproduct",
			 [':idproduct'=>$idproduct] 
			);
			$this->setData($rsl[0]);
		}

		public function delete()
		{
			$sql = new Sql();
			$sql->query(
			 "DELETE FROM TB_PRODUCTS WHERE IDPRODUCT = :idproduct",
			 [':idproduct'=>$this->getidproduct()] 
			);
		}

		public function checkPhoto()
		{
			if (file_exists(
				$_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
				"res".DIRECTORY_SEPARATOR.
				"site".DIRECTORY_SEPARATOR.
				"img".DIRECTORY_SEPARATOR.
				"product".DIRECTORY_SEPARATOR.
				$this->getidproduct().".jpg"))
			{
				$url = "/res/site/img/product/".$this->getidproduct().".jpg";
			} else {
				$url = "/res/site/img/product.jpg";
			}
			return $this->setdesphoto($url);
		}

		public function getData()
		{
			$this->checkPhoto();
			$data = parent::getData();
			return $data;
		}

		public function setPhoto($file)
		{
			$extension = explode('.', $file['name']);
			$extension = end($extension);
			switch ($extension) {
				case 'jpg':
				case 'jpeg':
					$image = imagecreatefromjpeg($file["tmp_name"]);
					break;
				case 'gif':
					$image = imagecreatefromgif($file["tmp_name"]);
					break;
				case 'png':
					$image = imagecreatefrompng($file["tmp_name"]);
					break;
				default:
					$image = imagecreatefromwbmp($file["tmp_name"]);
					break;
			}
			$imageDestino = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
				"res".DIRECTORY_SEPARATOR.
				"site".DIRECTORY_SEPARATOR.
				"img".DIRECTORY_SEPARATOR.
				"product".DIRECTORY_SEPARATOR.
				$this->getidproduct().".jpg";
			imagejpeg($image, $imageDestino);
			imagedestroy($image);
			$this->checkPhoto();
		}
	}
?>