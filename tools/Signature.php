<?php
class Signature {
	const BUCKET = "study-man";
	const ACCESSID = "oLUDl17Sz4PbT8aW";
	const ACCESSKEY = "HBssPIgoaIIsuH5ZkbiVwXh6RcKPlk";
	static $time;

	static function Authorization() {
		$num = func_num_args();
		$arr = func_get_args();
		if ($num == 2) {
			return Signature::download($arr[0], $arr[1]);
		} elseif ($num == 3) {
			return Signature::upload($arr[0], $arr[1], $arr[2]);
		}
	}

	static function download($method, $objectKey) {
		Signature::get_time("+5 minutes");
		$data = $method."\n\n\n".Signature::$time."\n/".Signature::BUCKET."/".$objectKey;
		$signature = "OSS ".Signature::ACCESSID.":".base64_encode(hash_hmac("sha1", $data, Signature::ACCESSKEY, true));
		return $signature;
	}

	static function upload($method, $objectKey, $contentType) {
		Signature::get_time("+5 minutes");
		$data = $method."\n\n".$contentType."\n".Signature::$time."\n/".Signature::BUCKET."/".$objectKey;
		$signature = "OSS ".Signature::ACCESSID.":".base64_encode(hash_hmac("sha1", $data, Signature::ACCESSKEY, true));
		return $signature;
	}

	static function get_time($time){
		Signature::$time = gmdate("D, d M Y H:i:s T", strtotime($time));
	}
}
