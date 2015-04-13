<?php
include_once 'DBconnecter.php';
include_once 'Signature.php';

header("Content-Type:text/xml;charset:UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class uploader {
	const METHOD = "PUT";
	private $userId;
	private $title;
	private $objectKey;
	private $contentType;
	private $type;
	private $result;
	public $xml;
	
	function __construct($data) {
		$this->userId = $data['userId'];
		$this->objectKey = $data['objectKey'];
		$this->contentType = $data['contentType'];
		$this->type = $data['type'];
		if (array_key_exists('title', $data)) {
			$this->title = $data['title'];;
		} else {
			$this->title = null;
		}
	}
	
	private function check_note() {
		$sql = "SELECT `noteId` FROM `notes` WHERE `userId`='$this->userId' AND `title`='$this->title'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$data = $conn->query($sql);
		return $data->fetch();
	}
	
	private function check_question() {
		$sql = "SELECT `questionId` FROM `questions` WHERE `userId`='$this->userId' AND `title`='$this->title'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$data = $conn->query($sql);
		return $data->fetch();
	}
	
	private function set_objectKey() {
		if (empty($this->title) || $this->title == NULL) {
			$this->objectKey = $this->userId."/".$this->type."/".$this->objectKey;
			$this->result = "ready to upload1";
		}elseif ($this->type == "note") {
			if ($this->check_note()) {
				$this->result = "note is exist";
			} else {
				$this->objectKey = $this->userId."/".$this->type."/".$this->objectKey;
				$this->result = "ready to upload2";
			}
		}elseif ($this->type == "question") {
			if ($this->check_question()) {
				$this->result = "question is exist";
			} else {
				$this->objectKey = $this->userId."/".$this->type."/".$this->objectKey;
				$this->result = "ready to upload3";
			}
		}else {
			$this->result = "type is wrong";
		}
	}
	
	private function create_xml() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->xml .= "<result>$this->result</result>";
		$this->xml .= "<authorization>".Signature::Authorization(uploader::METHOD, $this->objectKey, $this->contentType)."</authorization>";
		$this->xml .= "<bucket>".Signature::BUCKET."</bucket>";
		$this->xml .= "<objectKey>".$this->objectKey."</objectKey>";
		$this->xml .= "<date>".Signature::$time."</date>";
		$this->xml .= "<contentType>$this->contentType</contentType>";
		$this->xml .= "</root>";		
	}
	
	public function work() {
		$this->set_objectKey();
		$this->create_xml();
	}
}

function main($data) {
	$uploader = new uploader($data);
	$uploader->work();
	echo $uploader->xml;
}

main($data);