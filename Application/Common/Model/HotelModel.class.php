<?php
namespace Common\Model;
use Think\Model;

class HotelModel extends Model {
	
	public function __construct() {
		# code...
	}

	public function setConfig($data = array()) {
		if (!$data) {
			throw_exception('没有提交的数据');
		}
		return F('hotel_config', $data);
	}

	public function getConfig() {
		return F('hotel_config');
	}

	public function getWhiteListPage($data, $firstRow, $listRow) {
		$condition = array();
		if (isset($data["name"]) && $data["name"]) {
			$condition["name"] = array("like", "%".$data["name"]."%");
		}
		if (isset($data["phone"]) && $data["phone"]) {
			$condition["phone"] = $data["phone"];
		}
		return M("hotel_whitelist")->where($condition)
			->order("id desc")
			->limit($firstRow, $listRow)
			->select();
	}

	public function getWhiteListCount($data) {
		$condition = array();
		if (isset($data["name"]) && $data["name"]) {
			$condition["name"] = array("like", "%".$data["name"]."%");
		}
		if (isset($data["phone"]) && $data["phone"]) {
			$condition["phone"] = $data["phone"];
		}
		return M("hotel_whitelist")->where($condition)->count();
	}

	public function findWhite($phone) {
		$condition["phone"] = $phone;
		return M("hotel_whitelist")->where($condition)->find();
	}

	public function addWhiteUser($data) {
		if (!is_array($data) || !$data) {
			return 0;
		}
		return M("hotel_whitelist")->add($data);
	}

	public function delWhiteUser($id) {
		if (!is_numeric($id) || !$id) {
			return 0;
		}
		return M("hotel_whitelist")->delete($id);
	}

	public function clearWhiteUser() {
		return M("hotel_whitelist")->where('1')->delete();
	}

	public function getCodeListPage($data, $firstRow, $listRow) {
		$condition = array();
		if (isset($data["name"]) && $data["name"]) {
			$condition["name"] = array("like", "%".$data["name"]."%");
		}
		if (isset($data["phone"]) && $data["phone"]) {
			$condition["phone"] = $data["phone"];
		}
		if (isset($data["code"]) && $data["code"]) {
			$condition["code"] = $data["code"];
		}
		return M("hotel_code")->where($condition)
			->limit($firstRow, $listRow)
			->select();
	}

	public function getCodeListCount($data) {
		$condition = array();
		if (isset($data["name"]) && $data["name"]) {
			$condition["name"] = array("like", "%".$data["name"]."%");
		}
		if (isset($data["phone"]) && $data["phone"]) {
			$condition["phone"] = $data["phone"];
		}
		if (isset($data["code"]) && $data["code"]) {
			$condition["code"] = $data["code"];
		}
		return M("hotel_code")->where($condition)->count();
	}

	public function exchange($code) {
		$hotel_code = M('hotel_code');

		$data['status'] = 1;
		$data['exchange_time'] = time();

		$condition["code"] = $code;
		return $hotel_code->where($condition)->save($data);
	}
}