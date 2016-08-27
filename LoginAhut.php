<?php
include_once "./http_request.php";
include_once "./getDataFromHtml.php";
include_once "./DB.php";
/**
 * 设置默认时区为北京
 */
date_default_timezone_set('prc');
/**
 * 安工大教务处登陆课表信息操作类
 * @author Kosmos qidunwei@outlook.com
 * @version 1.0
 * 2015年10月4日20:52:39
 */
class Ahut {
	private $username;
	private $password;
	private $url_post = '';
	private $url_get = '';

	/**
	 * [__construct 构造函数，初始化用户名和密码]
	 * @param [type] $username [学号]
	 * @param [type] $password [密码]
	 */
	public function __construct($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * [getCookies 获取登陆后的Cookie]
	 * @return [type] [返回登陆后的Cookie]
	 */
	private function getCookies() {
		$cookie = curlRequest::LoginAhut($this->url_post, $this->username, $this->password);
		if ($cookie == -1) {
			die("{loginfeiled}");
		}
		return $cookie;
	}

	/**
	 * [getContents 通过Cookie抓取登陆后的课表信息]
	 * @return [type] [返回课表可学生基本信息]
	 */
	public function getContents() {
		$cookies = $this->getCookies();
		$this->url_get .= $this->username;
		$temp_contents = curlRequest::curl_request($this->url_get, '', $cookies);
		// 进行字符编码转换
		$contents = mb_convert_encoding($temp_contents, 'utf-8', 'gbk');
		// 获取课程信息 json 格式
		$content = data::getData($contents);
		return $content;
	}

	/**
	 * [saveContents 学生课表信息保存]
	 * @param  [type] $conents [学生课表信息]
	 * @return [type]          [description]
	 */
	public function saveContents($contents) {
		$result = json_decode($contents);
		$studentInfo = $result->studentInfo;
		$coursesInfo = $result->coursesInfo;

		//检查是否已保存记录
		$temp_sql = "Select * from ahut_course_info where stuNo='" . $studentInfo->stuNo . "'";
		$count = DB::getRowsOfExecute($temp_sql);
		if ($count > 0) {
			//更新数据库
			$sql = "Update ahut_course_info SET coursesInfo='" . json_encode($coursesInfo, JSON_UNESCAPED_UNICODE);
			$sql .= "',lastTime='" . date("Y-m-d H:i:s");
			$sql .= "' where stuNo='" . $studentInfo->stuNo . "'";
			DB::doUpdate($sql);
		} else {
			//新数据插入
			$sql = "Insert into ahut_course_info(stuNo,stuName,class,college,discipline,coursesInfo,lastTime)Values('";
			$sql .= $studentInfo->stuNo . "','";
			$sql .= $studentInfo->stuName . "','";
			$sql .= $studentInfo->class . "','";
			$sql .= $studentInfo->college . "','";
			$sql .= $studentInfo->discipline . "','";
			$sql .= json_encode($coursesInfo, JSON_UNESCAPED_UNICODE) . "','";
			$sql .= date('y-m-d h:i:s', time()) . "');";

			DB::doInsert($sql);
		}

	}
}
?>
