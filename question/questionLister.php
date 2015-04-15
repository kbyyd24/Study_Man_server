<?php
include_once '../tools/DBconnecter.php';

header("Content-Type:text/xml;charset:UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);
class questionLister{
	private $userId;
	private $searcherId;
	private $page;
	private $number;
	private $DBdata;
	private $result = 0;
	public $xml;
	
	function __construct($data) {
		$this->userId = $data['userId'];
		$this->searcherId = $data['searcherId'];
		$this->page = $data['page'];
		if (array_key_exists('number', $data)) {
			$this->number = $data['number'];;
		} else {
			$this->number = 10;
		}
	}
	/**
	 * search friend relationship
	 */
	private function search_friend() {
		
	}
	
	private function search_name() {
		$sql = "SELECT `name` FROM `users_infomation` WHERE `id`='$this->searcherId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while ($row = $DBdata->fetch()) {
			$this->name = $row['name'];
		}
		if (empty($this->name)) {
			$this->result = 1;
		}
	}
	
	private function search_question() {
		$lowest = ($this->page-1)*$this->number;
		$highest = $this->page*$this->number;
		$sql = "SELECT `questionId`,`title`,`time`,`answerNumber`,`adoptId` FROM `questions` WHERE `userId`='$this->searcherId' LIMIT $lowest,$highest";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$this->DBdata = $conn->query($sql);
		$row = $this->DBdata->fetch();
		if (empty($row)) {
			$this->result += 2;
		} else {
			$this->DBdata = $conn->query($sql);
		}
	}
	
	private function create_xml() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->xml .= "<result>$this->result</result>";
		while($row = $this->DBdata->fetch()){
			$this->xml .= "<question>";
			$this->xml .= "<questionId>".$row['questionId']."</questionId>";
			$this->xml .= "<title>".$row['title']."</title>";
			$this->xml .= "<time>".$row['time']."</time>";
			$this->xml .= "<answerNumber>".$row['answerNumber']."</answerNumber>";
			$this->xml .= "<adoptId>".$row['adoptId']."</adoptId>";
			$this->xml .= "</question>";
		}
		$this->xml .= "</root>";
	}
	
	public function work() {
		$this->search_name();
		$this->search_question();
		$this->create_xml();
	}
}

function main($data) {
	$lister = new questionLister($data);
	$lister->work();
	echo $lister->xml;
}

main($data);