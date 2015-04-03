<?php
header("Content-Type:text/xml;charset=UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class Lister{
	private $id;
	private $group;
	public $xml;
	
	function __construct($data){
		$this->id = $data['id'];
		if (array_key_exists('group', $data)) {
			$this->group = $data['group'];;
		} else {
			$this->group = "";
		}
	}
	
	private function get_data1() {
		$sql = "SELECT `userRename`,`friendId` FROM `userFriend` WHERE `userId`='$this->id'";// AND `userGroup`='$this->group'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$this->DBdata = $conn->query($sql);
	}
	
	private function create_xml1() {
		while($row = $this->DBdata->fetch()){
			$this->xml .= "<data>";
			$this->xml .= "<rename>".$row['userRename']."</rename>";
			$this->xml .= "<id>".$row['friendId']."<id>";
			$this->xml .= "</data>";
		}
	}
	
	private function get_data2() {
		$sql = "SELECT `friendRename`,`userId` FROM `userFriend` WHERE `friendId`='$this->id'";// AND 'friendGroup'=\"$this->group\"";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$this->DBdata = $conn->query($sql);
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
		$this->xml = "<xml version='1.0' encoding='UTF-8'><root>";
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
