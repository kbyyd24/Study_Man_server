<?php
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);
header("Content-Type:text/xml;charset=UTF-8");

class Loger{
	private $objectKey;
	private $userId;
	private $title;
	private $sharing;
	private $time;
	private $result;
	public $xml;
	
	function __construct($data) {
		$this->userId = $data['userId'];
		$this->title = $data['title'];
		$this->objectKey = $data['objectKey'];
		$this->sharing = $data['sharing'];
		$this->time = $data['time'];
	}
		
	private function save_into_base() {
		$sql = "INSERT INTO `notes` (`userId`, `objectKey`, `title`, `sharing`, `time`) VALUES ('$this->userId', '$this->objectKey', '$this->title', '$this->sharing', '$this->time')";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$conn->exec($sql);
		return true;
	}
	/**
	 * update note,no use now
	 * @return boolean
	 */
	private function update_base() {
		$sql = "UPDATE `notes` SET ";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		return true;
	}
	
	private function judge_base() {
		$sql = "SELECT `noteId` FROM `notes` WHERE `userId`='$this->userId' AND `title`='$this->title'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while($row = $DBdata->fetch()){
			$noteId = $row['noteId'];
		}
		if (empty($noteId)) {
			$this->result = "save note fail";
		} else {
			$this->result = "save note success";
		}
	}
	
	private function create_xml() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->xml .= "<result>$this->result</result>";
		$this->xml .= "</root>";
	}
	
	public function work() {
		$this->save_into_base();
		$this->judge_base();
		$this->create_xml();
	}
}

function main($data) {
	$log = new Loger($data);
	$log->work();
	echo $log->xml;
}

main($data);
