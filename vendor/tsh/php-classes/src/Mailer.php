<?php 
	namespace tsh;

	use Rain\Tpl;

	class Mailer
	{
		/*? Avaliar gravar bd ou ini.cfg criptografado
		const USERNAME = ":USERNAME"; //Username to use for SMTP authentication - use full email address for gmail
		const PASSWORD = ":PASSWORD";
		*/
		const NAMEFROM = "TSH Loja";

		private $mail;

		public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
		{
			$config = array(
				"tpl_dir"   => $_SERVER["DOCUMENT_ROOT"]."/view/email/",
				"cache_dir" => $_SERVER["DOCUMENT_ROOT"]."/view-cache/",
				"debug"     => true // set to false to improve the speed
			);

			Tpl::configure($config);

			$tpl = new Tpl;

			foreach ($data as $key => $value) {
				$tpl->assign($key, $value); //criar as variaveis dentro do template
			}

			$html = $tpl->draw($tplName, true); //true retorna para variavel, false na tela

			$this->mail = new \PHPMailer; //inclui a \ pq esta no escopo principal

			$this->mail->isSMTP(); //Tell PHPMailer to use SMTP
			$this->mail->SMTPDebug = 2; //0=Sem debug; 1=client msg; 2=client e server msg
			$this->mail->Debugoutput = 'html';
			$this->mail->Host = 'smtp.gmail.com'; //Set the hostname of the mail server
			//$this->mail->Port = 587; //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
			//$this->mail->SMTPSecure = 'tls'; //Set the encryption system to use - ssl (deprecated) or tls
			$this->mail->Port = 465; //suporte a tls/ssl
			$this->mail->SMTPSecure = 'ssl'; //Set the encryption system to use - ssl (deprecated) or tls
			$this->mail->SMTPAuth = true; //Whether to use SMTP authentication
			$this->mail->Username = Mailer::USERNAME;
			$this->mail->Password = Mailer::PASSWORD; //Password to use for SMTP authentication
			$this->mail->setFrom(Mailer::USERNAME, Mailer::NAMEFROM); //Set who the message is to be sent from
			//$this->mail->addReplyTo("",""); //Set an alternative reply-to address
			$this->mail->addAddress($toAddress, $toName); //Set who the message is to be sent to
			$this->mail->Subject = $subject; //Set the subject line
			$this->mail->msgHTML($html); //Read an HTML message body from an external file, convert referenced images to embedded, convert HTML into a basic plain-text alternative body

			//Replace the plain text body with one created manually
			$this->mail->AltBody = 'This is a plain-text message body (contents.html falhou';
			
			//*$this->mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file
		}

		public function send()
		{
			return $this->mail->send();
		}
	}
?>