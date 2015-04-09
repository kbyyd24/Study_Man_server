<?php
include_once '../tools/DBconnecter.php';

header("Content-Type:text/xml;charset=UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class Lister{
	private $userId;
	private $group;
	private $result;
	public $xml;
	
	function __construct($data){
		$this->userId = $data['userId'];
		if (array_key_exists('group', $data)) {
			$this->group = $data['group'];;
		} else {
			$this->group = "";
		}
	}
	
	private function get_data1() {
		$sql = "SELECT `userRename`,`friendId` FROM `userFriend` WHERE `userId`='$this->userId'";// AND `userGroup`='$this->group'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$this->DBdata = $conn->query($sql);
		if (empty($this->DBdata->fetch())) {
			$this->result = "no friend added";
			$this->get_data2();
		} else {
			$this->DBdata = $conn->query($sql);
			$this->result = "success";
		}
	}
	
	private function create_xml1() {
		$this->xml .= "<result>$this->result</result>";
		while($row = $this->DBdata->fetch()){
			$this->xml .= "<data>";
			$this->xml .= "<rename>".$row['userRename']."</rename>";
			$this->xml .= "<id>".$row['friendId']."<id>";
			$this->xml .= "</data>";
		}
	}
	
	private function get_data2() {
		$sql = "SELECT `friendRename`,`userId` FROM `userFriend` WHERE `friendId`='$this->userId'";// AND 'friendGroup'=\"$this->group\"";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$this->DBdata = $conn->query($sql);
		if (empty($this->DBdata->fetch())) {
			$this->result = "no friend added";
		} else {
			$this->DBdata = $conn->query($sql);
			$this->result = "success";
		}
	}
	
	private function create_xml2() {
	while($row = $this->DBdata->fetch()){
			$this->xml .= "<data>";
			$this->xml .= "<rename>".$row['friendRename']."</rename>";
			$this->xml .= "<id>".$row['userId']."<id>";
			$this->xml .= "</data>";
		}
	}

	public function work() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->get_data1();
		$this->create_xml1();
		$this->get_data2();
		$this->create_xml2();
		$this->xml .= "</root>";
	}
}

function main($data) {
	$lister = new Lister($data);
	$lister->work();
	echo $lister->xml;
}

main($data);
