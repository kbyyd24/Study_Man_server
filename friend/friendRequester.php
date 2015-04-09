<?php
include_once '../tools/DBconnecter.php';

header("Content-Type:text/xml;charset:UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class Requester{
	private $userId;
	private $friendId;
	private $result;
	public $xml;
	
	function __construct($data) {
		$this->userId = $data['userId'];
		$this->friendId = $data['firendId'];
	}
	
	private function request() {
		$sql = "INSERT INTO `friend_request` (`requesterId`, `responserId`, `status`) values('$this->userId', '$this->friendId', 'wait')";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$conn->exec($sql);
	}
	
	private function judge_base() {
		$sql = "SELECT `requestId` FROM `friend_request` WHERE `requesterId`='$this->userId' AND `responserId`='$this->firendId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while($row = $DBdata->fetch()){
			$id = $row['requestId'];
		}
		if (empty($id)) {
			$this->result = "take in request fail";
		} else {
			$this->result = "take in request success";
		}
	}
	
	private function create_xml() {
		$this->xml = "<xml version'1.0' encoding='UTF-8'><root>";
		$this->xml .= "<result>$this->result</result>";
		$this->xml .= "</root>";
	}
	
	public function work() {
		$this->request();
		$this->judge_base();
		$this->create_xml();
	}
	
}

function main($data) {
	$requester = new Requester($data);
	$requester->work();
	echo $requester->xml;
}

main($data);