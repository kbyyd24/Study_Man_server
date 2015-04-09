<?php
include_once '../tools/DBconnecter.php';

header("Content-Type:text/xml;charset:UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class questionLister{
	private $friendId;
	private $page;
	private $number;
	private $name;
	private $DBdata;
	private $nameResult;
	private $questionResult;
	public $xml;
	
	function __construct($data) {
		$this->friendId = $data['friendId'];
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
		$sql = "SELECT `name` FROM `users_information` WHERE `userId`='$this->friendId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while ($row = $DBdata->fetch()) {
			$this->name = $row['name'];
			$this->nameResult = "user is exist";
		}
		if (empty($this->name)) {
			$this->nameResult = "user is not exist";
		}
	}
	
	private function search_question() {
		$lowest = ($this->page-1)*$this->number;
		$highest = $this->page*$this->number;
		$sql = "SELECT `questionId`,`title`,`time`,`answerNumber`,`adoptId` FROM `questions` WHERE `userId`='$this->friendId' LIMIT $lowest,$highest";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$this->DBdata = $conn->query($sql);
		if (empty($this->DBdata->fetch())) {
			$this->questionResult = "no question exist";
		} else {
			$this->DBdata = $conn->query($sql);
			$this->questionResult = "success";
		}
	}
	
	private function create_xml() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->xml .= "<nameResult>$this->nameResult</nameResult>";
		$this->xml .= "<questionResult>$this->questionResult</questionResult>";
		$this->xml .= "<name>$this->name</name>";
		while($row = $this->DBdata->fetch()){
			$this->xml .= "<question>";
			$this->xml .= "<questionId>".$row['questionId']."</questionId>";
			$this->xml .= "<title>".$row['title']."</title>";
			$this->xml .= "<time>".$row['time']."</time>";
			$this->xml .= "<answerNumber>".$row['answerNumber']."</answerNumber>";
			$this->xml .= "<adoptId>".$row['adoptId']."</adoptId>";
			$this->xml .= "</question>";
		}
	}
	
	public function work() {
		$this->search_name();
		$this->search_question();
		if ($this->nameResult == "user is not exist") {
			$this->questionResult = $this->nameResult;
		}
		$this->create_xml();
	}
}

function main($data) {
	$lister = new questionLister($data);
	$lister->work();
	echo $lister->xml;
}

main($data);