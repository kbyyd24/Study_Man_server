<?php
include_once '../tools/DBconnecter.php';
include_once '../tools/Signature.php';

header("Content-Type:text/xml;charset:UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class responser{
	private $questionId;
	private $responserId;
	private $objectKey;
	private $result;
	public $xml;
	
	function __construct($data) {
		$this->questionId = $data['questionId'];
		$this->responserId = $data['responserId'];
		$this->objectKey = $data['objectKey'];
	}
	
	private function response() {
		Signature::get_time("+8 hours");
		$sql = "INSERT INTO `answers` (`questionId`, `responserId`, `time`, `objectKey`, `adopted`) VALUES ('$this->questionId', '$this->responserId', '".Signature::$time."', '$this->objectKey', '0')";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$conn->exec($sql);
	}
	
	private function judge_base() {
		$sql = "SELECT `answerId` FROM `answers` WHERE `questionId`='$this->questionId' AND `responserId`='$this->responserId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while($row = $DBdata->fetch()){
			$answerId = $row['answerId'];
		}
		if (empty($answerId)) {
			$this->result = "take in answer fail";
		} else {
			$this->result = "take in answer success";
		}
	}
	
	private function create_xml() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->xml .= "<result>$this->result</result>";
		$this->xml .= "</root>";
	}
	
	public function work() {
		$this->response();
		$this->judge_base();
		$this->create_xml();
	}
}

function main($data) {
	$responser = new responser($data);
	$responser->work();
	echo $responser->xml;
}

main($data);