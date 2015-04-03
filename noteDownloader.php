<?php
// error_reporting(E_ALL);
// ini_set('display_error', '1');
/**
 * @return request headers in xml
 */
header("Content-Type:text/xml;charset=UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class Downloader{
	const METHOD = "GET";
	private $id;
	private $noteId;
	private $objectKey;
	private $signature;
	public $xml;
	
	function __construct($data){
		$this->id = $data['id'];
		$this->noteId = $data['noteId'];
	}
	
	private function get_messages() {
		$sql = "SELECT * FROM `notes` WHERE `userId`='$this->id' AND `noteId`='$this->noteId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while ($row = $DBdata->fetch()) {
			$this->objectKey = $row['objectKey'];
		}
		mysql_close($conn);
	}
	
	private function create_xml() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?>";
		$this->xml .= "<root>";
		$this->xml .= "<authorization>".Signature::Authorization(Downloader::METHOD, $this->objectKey)."</authorization>";
		$this->xml .= "<bucket>".DBconnecter::BUCKET."</bucket>";
		$this->xml .= "<date>".Signature::$time."</date>";
		$this->xml .= "<id>$this->id</id>";
		$this->xml .= "<objectKey>$this->objectKey</objectKey>";
		$this->xml .= "</root>";
	}
	
	public function work() {
		$this->get_messages();
		$this->create_xml();
	}
}

function main($data) {
	$downloader = new Downloader($data);
	$downloader->work();
    echo $downloader->xml;
}

main($data);
?>
