<?php
include_once '../tools/DBconnecter.php';

header("Content-Type:text/xml;charset:UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class quesViewer {
	const METHOD = "GET";
	private $userId;
	private $questionId;
	private $objectKey;
	private $title;
	private $time;
	private $signature;
	private $answerViewer;
	private $DBdata;
	private $result;
	public $xml;
	/**
	 * question viewer
	 * @param received xml data, an array $data
	 */
	function __construct($data) {
		$this->userId = $data['userId'];
		$this->questionId = $data['questionId'];
	}
	
	private function viewQuestion() {
		$sql = "SELECT * FROM `questions` WHERE `questionId`='$this->questionId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while ($row = $DBdata->fetch()) {
			$this->objectKey = $row['objectKey'];
			$this->title = $row['title'];
			$this->time = $row['time'];
		}
	}
	
	private function viewAnswers() {
		$sql = "SELECT `answerId` FROM `answers` WHERE `questionId`='$this->questionId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$this->DBdata = $conn->query($sql);
		if (empty($this->DBdata->fetch())) {
			$this->result = "no friend added";
		} else {
			$this->DBdata = $conn->query($sql);
			$this->result = "success";
		}
	}
	
	private function add_answer_xml($data) {
		$this->answerViewer = new ansViewer($data);
		$this->answerViewer->work();
		$this->xml .= "<answer>";
		$this->xml .= $this->answerViewer->get_xmlTemp();
		$this->xml .= "</answer>";
	}
	
	private function create_xml() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->xml .= "<result>$this->result</result>";
		$this->xml .= "<title>$this->title</title>";
		$this->xml .= "<time>$this->time</time>";
		$this->xml .= "<authorization>".Signature::Authorization(quesViewer::METHOD, $this->objectKey)."</authotization>";
		$this->xml .= "<bucket>".Signature::BUCKET."</bucket>";
		$this->xml .= "<objectKey>$this->objectKey</objectKey>";
		$this->xml .= "<date>".Signature::$time."</date>";
		while ($row = $this->DBdata->fetch()) {
			$this->add_answer_xml($row);
		}
		$this->xml .= "</root>";
	}
	
	public function work() {
		$this->viewQuestion();
		$this->viewAnswers();
		$this->create_xml();
	}
}

function main($data) {
	$viewer = new quesViewer($data);
	$viewer->work();
	echo $viewer->xml;
}

main($data);