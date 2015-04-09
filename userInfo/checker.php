<?php
include_once '../tools/DBconnecter.php';

header("Content-Type:text/xml;charset:UTF-8");
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);

class ckecker{
	private $userId;
	private $time;
	private $continouns;
	private $result;
	public $xml;
	
	function __construct($data) {
		$this->userId = $data['userId'];
		$this->time = strtotime(gmdate("D, d M Y", strtotime("+8 hours")));
	}
	
	private function check() {
		$sql = "SELECT * FROM `check` WHERE `userId`='$this->userId' order by `checkId` desc LIMIT 0,1";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while ($row = $DBdata->fetch()) {
			$time = strtotime($row['time']);
			$continouns = $row['continouns'];
			$subtract = floor((strtotime($this->time)-$time)/3600/24);
			if ($subtract == 0) {
				$this->result = "checked today";
				$this->continouns = $continouns;
			}elseif ($subtract == 1) {	
				$this->result = "check success";
				if (empty($continouns) || $continouns == 0) {
					$this->continouns = 1;
				} else {
					$this->continouns = $continouns+1;
				}
			}elseif ($subtract > 1) {
				$this->result = "check success";
				$this->continouns = 1;
			}else {
				$this->result = "unknow error";
				$this->continouns = null;
			}
		}
		$sql = "INSERT INTO `check`(`userId`,`time`,`continouns`) VALUES ('$this->userId', '$this->time', '$this->continouns')";
		$conn->exec($sql);
	}
	
	private function create_xml() {
		$this->xml = "<xml version='1.0' encoding='UTF-8'><root>";
		$this->xml .= "<result>$this->result</result>";
		$this->xml .= "<continouns>$this->continouns</continouns>";
		$this->xml .= "</root>";
	}
	
	public function work() {
		$this->check();
		$this->create_xml();
	}
}

function main($data) {
	$checker = new ckecker($data);
	$checker->work();
	echo $checker->xml;
}

main($data);