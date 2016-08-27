<?php

/**
 * php解析课表HTML并获取课表信息
 * @author Kosmos qidunwei@outlook.com
 * @version 1.0
 * 2015年10月4日20:51:34
 */
class data {

	/**
	 * [getData description]
	 * @param  [type] $result [description]
	 * @return [type]         [description]
	 */
	public static function getData($result) {
		$studentInfo = self::studentInfo($result);
		preg_match_all('/<table id="Table1"[\w\W]*?>([\w\W]*?)<\/table>/', $result, $out);
		$table = $out[0][0]; //获取整个课表

		$table = str_replace('<br>', '*', $table);
		$table = str_replace('&nbsp;', '', $table);
		// echo $table;

		// 解决 DOMDocument 下中文显示乱码的问题
		$htmldoc = '<meta http-equiv="Content-Type" content="text/html;charset=utf-8">' . $table;
		$html = new DOMDocument;
		$html->loadHTML($htmldoc);
		// 获取所有的 "tr" 节点
		$trs = $html->getElementsByTagName('tr');

		$i = 0;
		$courseArray = array();
		foreach ($trs as $trvalue) {
			if ($i > 1 && ($i % 2 == 0) && $i < 11) {
				// 获取所有的 "td" 节点
				$tds = $trvalue->getElementsByTagName('td');
				// echo '$tds count : '.(string)($tds->length).'<br>';
				$j = 0;
				$tempArray = array();
				foreach ($tds as $tdvalue) {
					if ($tds->length == 9 && $j > 1) {
						$course = explode('*', $tdvalue->textContent);
						$tempArray[] = data::tempDataSerialiser($course);
					} elseif ($tds->length == 8 && $j > 0) {
						$course = explode('*', $tdvalue->textContent);
						$tempArray[] = data::tempDataSerialiser($course);
					}
					$j++;
				}
				$courseArray[] = $tempArray;
				unset($tempArray);
			}
			$i++;
		}
		$temp = array(
			'studentInfo' => $studentInfo,
			'coursesInfo' => $courseArray,
		);
		/*
		 * 转换成json格式数据，且中文不转码
		 */
		$info = json_encode($temp, JSON_UNESCAPED_UNICODE);
		return $info;
	}

	/**
	 * [tempDataSerialiser 课程基本信息序列化]
	 * @param  [type] $course [单节课程]
	 * @return [type]         [单节课程序列化]
	 */
	private static function tempDataSerialiser($course) {
		$arr = array(
			"name" => $course[0],
			"time" => $course[1],
			"teacher" => $course[2],
			"place" => $course[3],
		);
		return $arr;
	}

	/**
	 * [studentInfo 学生基本信息序列化]
	 * @param  [type] $result [所有信息页面]
	 * @return [type]         [学生信息]
	 */
	private static function studentInfo($result) {
		$htmltemp = '<meta http-equiv="Content-Type" content="text/html;charset=utf-8">' . $result;
		$ht = new DOMDocument;
		$ht->loadHTML($htmltemp);
		$temp_stuNo = explode('：', $ht->getElementById('Label5')->textContent);
		$temp_stuName = explode('：', $ht->getElementById('Label6')->textContent);
		$temp_college = explode('：', $ht->getElementById('Label7')->textContent);
		$temp_discipline = explode('：', $ht->getElementById('Label8')->textContent);
		$temp_class = explode('：', $ht->getElementById('Label9')->textContent);
		$stuNo = $temp_stuNo[1];
		$stuName = $temp_stuName[1];
		$college = $temp_college[1];
		$discipline = $temp_discipline[1];
		$class = $temp_class[1];

		$info = array(
			'stuNo' => $stuNo,
			'stuName' => $stuName,
			'college' => $college,
			'discipline' => $discipline,
			'class' => $class,
		);
		return $info;
	}

}

?>
