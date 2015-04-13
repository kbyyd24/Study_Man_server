<?php
include_once '../tools/DBconnecter.php';
include_once '../tools/Signature.php';

header("Content-Type:text/xml;charset:UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class Asker{
	private $userId;
	private $title;
	private $objectKey;
	private $result;
	public $xml;
	
	function __construct($data) {
		$this->userId = $data['userId'];
		$this->title = $data['title'];
		$this->objectKey = $data['objectKey'];
	}
	
	private function ask() {
		Signature::get_time("+8 hours");
		$sql = "INSERT INTO `questions` (`userId`, `title`, `answerNumber`, `objectKey`, `time`, `adoptId`) VALUES ('$this->userId', '$this->title', '0', '$this->objectKey', '".Signature::$time."', '0')";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$conn->exec($sql);
	}
	
	private function judge_base() {
		$sql = "SELECT `questionId` FROM `questions` WHERE `userId`='$this->userId' AND `title`='$this->title'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while($row = $DBdata->fetch()){
			$questionId = $row['questionId'];
		}
		if (empty($questionId)) {
			$this->result = "take in question fail";
		} else {
			$this->result = "take in question success";
		}
	}
	
	private function create_xml() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->xml .= "<result>$this->result</result>";
		$this->xml .= "</root>";
	}
	
	public function work() {
		$this->ask();
		$this->judge_base();
		$this->create_xml();
	}
}

function main($data) {
	$asker = new Asker($data);
	$asker->work();
	echo $asker->xml;
}

main($data);