<?php

/**
 * php连接操作MySQL数据示例类，单例模式
 * @author Kosmos qidunwei@outlook.com
 * @version 1.1
 * 2015/10/04
 */
class DB {

	private static $myconn;
	private static $trans = 0;
	private static $_dbConfig = array(
		'host' => 'qdm114586232.my3w.com',
		'user' => 'qdm114586232',
		'password' => 'wait3344',
		'database' => 'qdm114586232_db',
		'port' => 3306,
	);

	/**
	 * [__construct 构造函数私有化，不允许类外实例化对象]
	 */
	public function __construct() {
	}

	/**
	 * [connect 直行联结数据库操作]
	 * @return [type] [返回一个数据库资源连接]
	 */
	private static function connect() {
		$mysql = new mysqli(
			self::$_dbConfig['host'],
			self::$_dbConfig['user'],
			self::$_dbConfig['password'],
			self::$_dbConfig['database'],
			self::$_dbConfig['port']
		);
		$mysql->set_charset('UTF8');
		if ($mysql->connect_error) {
			return False;
		}
		return $mysql;
	}

	/**
	 * [showInfo 测试，显示连接信息]
	 * @return [type] [description]
	 */
	public static function showInfo() {
		if ($mysql = self::connect()) {
			var_dump($mysql);
			$mysql->close();
		} else {
			echo "connect_error...";
		}
	}

	/**
	 * [getRowsOfExecute 获取SQL语句影响的数据库表的行数]
	 * @param  [type] $sql [执行的SQl语句]
	 * @return [type]      [返回行数]
	 */
	public static function getRowsOfExecute($sql) {
		if ($mysql = self::connect()) {
			$result = $mysql->query($sql);
			if ($result) {
				$count = $mysql->affected_rows;
			} else {
				$count = 0;
			}
			$mysql->close();
		}
		return $count;
	}

	/**
	 * [doInsert 执行插入数据库操作]
	 * @param  [type] $sql [执行的SQl语句]
	 * @return [bool]      [返回插入成功与否]
	 */
	public static function doInsert($sql) {
		if (self::$trans) {
			$result = self::$myconn->real_query($sql);
		} else {
			if ($mysql = self::connect()) {
				$result = $mysql->real_query($sql);
				$mysql->close();
			}
			if ($result == True) {
				$result = 1;
			} else {
				$result = 0;
			}
		}
		return $result;
	}

	/**
	 * [doUpdate 执行数据库更新操作]
	 * @param  [type] $sql [执行的SQl语句]
	 * @return [type]      [返回更新影响的行数]
	 */
	public static function doUpdate($sql) {
		if (self::$trans) {
			$result = self::$myconn->query($sql);
			$count = -1;
		} else {
			if ($mysql = self::connect()) {
				$result = $mysql->query($sql);
				if ($result) {
					$count = $mysql->affected_rows;
				} else {
					$count = 0;
				}
				$mysql->close();
			}
		}
		return $count;
	}

	/**
	 * [doDelete 执行数据库删除操作]
	 * @param  [type] $sql [执行的SQl语句]
	 * @return [type]      [返回更新影响的行数]
	 */
	public static function doDelete($sql) {
		if ($mysql = self::connect()) {
			$result = $mysql->query($sql);
			if ($result) {
				$count = $mysql->affected_rows;
			} else {
				$count = 0;
			}
			$mysql->close();
		}
		return $count;
	}

	/**
	 * [doSelectRow 执行查询数据库操作]
	 * @param  [type] $sql [执行的SQl语句]
	 * @return [bool]      [返回查询的一行结果]
	 */
	public static function doSelectRow($sql) {
		if ($mysql = self::connect()) {
			$result = $mysql->query($sql);
			if ($result != FALSE) {
				$row = $result->fetch_row();
			}
			$mysql->close();
			return $row;
		} else {
			return False;
		}
	}

	/**
	 * [doSlectTable 执行查询数据库操作]
	 * @param  [type] $sql [执行的SQl语句]
	 * @return [type]      [返回查询的所有结果]
	 */
	public static function doSelectTable($sql) {
		if ($mysql = self::connect()) {
			$result = $mysql->query($sql);
			if ($result != FALSE) {
				foreach ($result as $row) {
					$array[] = $row;
				}
			}
			$mysql->close();
			return $array;
		} else {
			return False;
		}
	}

	/**
	 * [BeginTransaction 开始事务，设置事务可读写，关闭自动提交]
	 */
	public static function BeginTransaction() {
		if (!(self::$myconn instanceof mysqli)) {
			self::$myconn = self::connect();
		}
		if (self::$myconn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE)) {
			self::$myconn->autocommit(FALSE);
			self::$trans = 1;
		}
	}

	/**
	 * [CommitTransaction 提交事务，打开自动提交，关闭连接]
	 */
	public static function CommitTransaction() {
		if (self::$myconn->commit()) {
			self::$myconn->autocommit(TRUE);
			self::$myconn->close();
			self::$myconn = False;
			self::$trans = 0;
		}
	}

	/**
	 * [RollbackTransaction 回滚事务，打开自动提交，关闭连接]
	 */
	public static function RollbackTransaction() {
		if (self::$myconn->rollback()) {
			self::$myconn->autocommit(TRUE);
			self::$myconn->close();
			self::$myconn = False;
			self::$trans = 0;
		}
	}
}

?>
