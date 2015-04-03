<?php
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);
header("Content-Type:text/xml;charset=UTF-8");

class Lister{
	private $id;
	private $page;
	private $number;
	private $DBdata;
	public $xml;
	
	function __construct($data){
		$this->id = $data['id'];
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
		$sql = "SELECT `noteId`,`title`,`time` FROM `notes` WHERE `userId`='$this->id' LIMIT $lowest,$highest";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$this->DBdata = $conn->query($sql);
	}
	
	private function create_xml() {
		$i = 1;
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		while($row = $this->DBdata->fetch()){
			$this->xml .= "<data num=\"".$i."\">";
			$this->xml .= "<id>".$row['noteId']."</id>";
			$this->xml .= "<title>".$row['title']."</title>";
			$this->xml .= "<time>".$row['time']."</time>";
			$this->xml .= "</data>";
			$i++;
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
