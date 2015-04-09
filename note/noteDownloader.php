<?php
include_once '../tools/DBconnecter.php';

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
	private $userId;
	private $noteId;
	private $result;
	private $objectKey;
	private $signature;
	public $xml;
	
	function __construct($data){
		$this->userId = $data['userId'];
		$this->noteId = $data['noteId'];
	}
	
	private function get_messages() {
		$sql = "SELECT * FROM `notes` WHERE `noteId`='$this->noteId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while ($row = $DBdata->fetch()) {
			$this->objectKey = $row['objectKey'];
			$this->result = "success";
		}
		if (empty($this->result)) {
			$this->result = "fail";
		}
	}
	
	private function create_xml() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->xml .= "<result>$this->result</result>";
		$this->xml .= "<authorization>".Signature::Authorization(Downloader::METHOD, $this->objectKey)."</authorization>";
		$this->xml .= "<bucket>".DBconnecter::BUCKET."</bucket>";
		$this->xml .= "<date>".Signature::$time."</date>";
		$this->xml .= "<userId>$this->userId</userId>";
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
