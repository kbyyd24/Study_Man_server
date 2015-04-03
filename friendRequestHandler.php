<?php
header("Content-Type:text/xml;charset:UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class Handler{
	private $id;
	private $answer;
	private $result;
	public $xml;
	
	function __construct($data) {
		$this->id = $data['id'];
		$this->answer = $data['answer'];
	}
	
	private function handle() {
		$sql = "UPDATE `friend_request` SET `status`='$this->answer' WHERE `requestId`='$this->id'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$conn->exec($sql);
	}
	
	private function judge_base() {
		$sql = "SELECT `status` FROM `friend_request` WHERE `requestId`='$this->id'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while($row = $DBdata->fetch()){
			$status = $row['status'];
		}
		if ($status == $this->answer) {
			$this->result = "handle request fail";
		} else {
			$this->result = "handle request success";
		}
	}
	
	private function create_xml() {
		$this->xml = "<xml version='1.0' encoding='UTF-8'><root>";
		$this->xml .= "<result>$this->result</result>";
		$this->xml .= "</root>";
	}
	
	public function work() {
		$this->handle();
		$this->judge_base();
		$this->create_xml();
	}
}

function main($data) {
	$handler = new Handler($data);
	$handler->work();
	echo $handler->xml;
}

main($data);