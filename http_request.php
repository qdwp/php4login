<?php

/**
 * 执行模拟登陆
 * @author Kosmos qidunwei@outlook.com
 * @version 1.0
 * 2015年10月4日20:51:44
 */
class curlRequest {
	/**
	 * [LoginAhut 模拟登陆安工大教务处]
	 * @param [type] $url      [登陆地址]
	 * @param [type] $username [学号]
	 * @param [type] $password [密码]
	 */
	public static function LoginAhut($url, $username, $password) {
		$post['__VIEWSTATE'] = curlRequest::getViewStateOfAhut($url);
		$post['TextBox1'] = $username; //填写学号
		$post['TextBox2'] = $password; //填写密码
		$post['ddl_js'] = iconv('utf-8', 'gb2312', '学生');
		$post['Button1'] = iconv('utf-8', 'gb2312', '登录');
		$result = self::curl_request($url, $post, '', 1);
		return $result['cookie'];
	}

	/**
	 * [getViewStateOfAhut 获取登陆页面的 __VIEWSTATE]
	 * @param  [type] $url [登录页面的地址]
	 * @return [type]      [返回__VIEWSTATE]
	 */
	public static function getViewStateOfAhut($url) {
		$result = curlRequest::curl_request($url);
		$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*?)" \/>/is';
		preg_match_all($pattern, $result, $matches);
		$view[0] = $matches[1][0];
		return $view[0];
	}

	/**
	 * [curl_request 执行模拟登陆功能]
	 * @param  [type]  $url          [登陆地址]
	 * @param  string  $post         [post数据]
	 * @param  string  $cookie       [cookie值]
	 * @param  integer $returnCookie [是否返回cookie]
	 * @return [type]                [description]
	 */
	public static function curl_request($url, $post = '', $cookie = '', $returnCookie = 0) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
		// curl_setopt($curl, CURLOPT_REFERER, "这里一定要换成教务系统登陆的url"); //填写教务系统url
		if ($post) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
		}
		if ($cookie) {
			curl_setopt($curl, CURLOPT_COOKIE, $cookie);
		}
		curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($curl);
		// $data = mb_convert_encoding($data, 'utf-8', 'gbk');
		// echo $data;
		// 正则表达式匹配登录失败信息
		$preg = preg_match('/<script>alert(.*)<\/script>/', $data);
		if ($preg) {
			$array = array('cookie' => -1);
			return $array;
		}
		if (curl_errno($curl)) {
			return curl_error($curl);
		}
		curl_close($curl);
		// echo $data;
		if ($returnCookie) {
			list($header, $body) = explode("\r\n\r\n", $data, 2);
			preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
			$info['cookie'] = substr($matches[1][0], 1);
			$info['content'] = $body;
			return $info;
		} else {
			return $data;
		}
	}
}

?>
