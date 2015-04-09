<?php
include_once '../tools/DBconnecter.php';

header("Content-Type:text/xml;charset:UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);
/**
 * answer viewer
 * @author Kobe Ebanks
 *
 */
class ansViewer {
	const METHOD = "GET";
	private $answerId;
	private $questionId;
	private $responserId;
	private $time;
	private $objectKey;
	private $adopted;
	private $title;
	private $name;
	private $xmlTemp;
	private $result;
	public $xml;
	/**
	 * 
	 * @param received xml data,an array $data
	 */
	function __construct($data) {
		$this->answerId = $data['answerId'];
	}
	
	private function get_title() {
		$sql = "SELECT `title` FROM `questions` WHERE `questionId`='$this->questionId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while ($row = $DBdata->fetch()) {
			$this->title = $row['title'];
		}
	}
	
	private function get_name() {
		$sql = "SELECT `name` FROM `users_information` WHERE `userId`='$this->responserId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while ($row = $DBdata->fetch()) {
			$this->name = $row['name'];
		}
	}
	
	private function view() {
		$sql = "SELECT * FROM `answers` WHERE `answerId`='$this->answerId'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		if (empty($this->DBdata->fetch())) {
			$this->result = "answer is not exist";
		} else {
			$this->DBdata = $conn->query($sql);
			$this->result = "answer is exist";
		}
		while ($row = $DBdata->fetch()) {
			$this->questionId = $row['questionId'];
			$this->responserId = $row['responserId'];
			$this->time = $row['time'];
			$this->objectKey = $row['objectKey'];
			$this->adopted = $row['adopted'];
		}
		$this->get_title();
		$this->get_name();
	}
	
	private function create_xml() {
		$this->xmlTemp = "<result>$this->result</result>";
		$this->xmlTemp .= "<name>$this->name</name>";
		$this->xmlTemp .= "<answerId>$this->answerId</answerId>";
		$this->xmlTemp .= "<authorization>".Signature::Authorization(viewer::METHOD, $this->objectKey)."</authorization>";
		$this->xmlTemp .= "<bucket>".Signature::BUCKET."</bucket>";
		$this->xmlTemp .= "<objectKey>$this->objectKey</objectKey>";
		$this->xmlTemp .= "<date>".Signature::$time."</date>";
		
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->xml .= $this->xmlTemp;
		$this->xml .= "<time>$this->time</time>";
		$this->xml .= "<adopted>$this->adopted</adopted>";
		$this->xml .= "<title>$this->title</title>";
		$this->xml .= "<questionId>$this->questionId</questionId>";
		$this->xml .= "</root>";
	}
	
	public function get_xmlTemp() {
		return $this->xmlTemp;
	}
	
	public function work() {
		$this->view();
		$this->create_xml();
	}
}

function main($data) {
	$viewer = new viewer($data);
	$viewer->work();
	echo $viewer->xml;
}

main($data);