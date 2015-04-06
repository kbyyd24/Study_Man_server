<?php
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);
header("Content-Type:text/xml;charset=UTF-8");

class Lister{
	private $userId;
	private $page;
	private $number;
	private $result;
	private $DBdata;
	public $xml;
	
	function __construct($data){
		$this->userId = $data['userId'];
		$this->page = $data['page'];
		if (array_key_exists('number', $data)) {
			$this->number = $data['number'];;
		} else {
			$this->number = 10;
		}
	}
	
	private function get_data() {
		$lowest = ($this->page-1)*$this->number;
		$highest = $this->page*$this->number;
		$sql = "SELECT `noteId`,`title`,`time` FROM `notes` WHERE `userId`='$this->userId' LIMIT $lowest,$highest";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$this->DBdata = $conn->query($sql);
		if (empty($this->DBdata->fetch())) {
			$this->result = "no note saved";
		} else {
			$this->DBdata = $conn->query($sql);
			$this->result = "success";
		}
	}
	
	private function create_xml() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->xml .= "<result>$this->result</result>";
		while($row = $this->DBdata->fetch()){
			$this->xml .= "<note>";
			$this->xml .= "<id>".$row['noteId']."</id>";
			$this->xml .= "<title>".$row['title']."</title>";
			$this->xml .= "<time>".$row['time']."</time>";
			$this->xml .= "</data>";
		}
		$this->xml .= "</root>";
	}

	public function work() {
		$this->get_data();
		$this->create_xml();
	}
}

function main($data) {
	$lister = new Lister($data);
	$lister->work();
	echo $lister->xml;
}

main($data);
