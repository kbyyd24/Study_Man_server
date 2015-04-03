<?php
header("Content-Type:text/xml;charset=UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class Searcher{
	private $name;
	private $id;
	private $result;
	public $xml;
	
	function __construct($data) {
		$this->name = $data['userName'];
	}
	
	private function search() {
		$sql = "SELECT `userId` WHERE `name`='$this->name'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while ($row = $DBdata->fetch()) {
			$this->id = $row['userId'];
		}
	}
	
	private function check_result() {
		if (empty($this->id)) {
			$this->result = "no user";
		} else {
			$this->result = "found user";
		}
	}
	
	private function create_xml() {
		$this->xml = "<xml version='1.0' encoding='UTF-8'><root>";
		$this->xml .= "<result>$this->result</result>";
		$this->xml .= "<id>$this->id</id>";
		$this->xml .= "<name>$this->name<name>";
		$this->xml .= "</root>";
	}
	
	public function work() {
		$this->search();
		$this->check_result();
		$this->create_xml();
	}
	
}

function main($data) {
	$searcher = new Searcher($data);
	$searcher->work();
	echo $searcher->xml;
}

main($data);