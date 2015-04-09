<?php
include_once '../tools/DBconnecter.php';

header("Content-Type:text/xml;charset:UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class Adopter{
	private $userId;
	private $questionId;
	private $answerId;
	private $result;
	public $xml;
	
	function __construct($data) {
		$this->userId = $data['userId'];
		$this->answerId = $data['answerId'];
		$this->questionId = $data['questionId'];
	}
	
	private function update_question() {
		$sql = "UPDATE `questions` SET `adoptId`='$this->answerId' WHERE `questionId`='$this->questionId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$conn->exec($sql);
	}
	
	private function update_answer() {
		$sql = "UPDATE `answers` SET `adopted`='true' WHERE `answerId`='$this->answerId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$conn->exec($sql);
	}
	
	private function adopt() {
		$this->update_question();
		$this->update_answer();
	}
	
	private function judge_base() {
		$sql = "SELECT `adoptId` FROM `questions` WHERE `questionId`='$this->questionId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$questionDB = $conn->query($sql);
		$sql = "SELECT `adopted` FROM `answers` WHERE `answerId`='$this->answerId";
		$answerDB = $conn->query($sql);
		while ($row = $questionDB->fetch()) {
			$adoptId = $row['adoptId'];
		}	
		while ($row = $answerDB->fetch()) {
			$adopted = $row['adopted'];
		}
		if (($adoptId == $this->answerId)&&($adopted == 'true')) {
			$this->result = "adopted answer";;
		} else {
			$this->result = "adopted fail";
		}
	}
	
	private function create_xml() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->xml .= "<result>$this->result</result>";
		$this->xml .= "</root>";
	}
	
	public function work() {
		$this->adopt();
		$this->judge_base();
		$this->create_xml();
	}
}

function main($data) {
	$adopter = new Adopter($data);
	$adopter->work();
	echo $adopter->xml;
}

main($data);