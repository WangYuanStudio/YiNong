<?php
/**
 * 数据库防注入操作
 *
 * @author 		YowFung
 * @copyright 	网园资讯工作室
 * @license		http://www.wangyuan.info
 * @version 	2017.7.19.1.0
 */


/**
* 数据库防注入类
*/
class Safety
{
	//定义关键字内容
	protected $KeyWord = "ADD|ALL|ALTER|ANALYZE|AND|AS|ASC|ASENSITIVE|BEFORE|BETWEEN|BIGINT|BINARY|BLOB|BOTH|BY|CALL|CASCADE|CASE|CHANGE|CHAR|CHARACTER|CHECK|COLLATE|COLUMN|CONDITION|CONNECTION|CONSTRAINT|CONTINUE|CONVERT|CREATE|CROSS|CURRENT_DATE|CURRENT_TIME|CURRENT_TIMESTAMP|CURRENT_USER|CURSOR|DATABASE|DATABASES|DAY_HOUR|DAY_MICROSECOND|DAY_MINUTE|DAY_SECOND|DEC|DECIMAL|DECLARE|DEFAULT|DELAYED|DELETE|DESC|DESCRIBE|DETERMINISTIC|DISTINCT|DISTINCTROW|DIV|DOUBLE|DROP|DUAL|EACH|ELSE|ELSEIF|ENCLOSED|ESCAPED|EXISTS|EXIT|EXPLAIN|FALSE|FETCH|FLOAT|FLOAT4|FLOAT8|FOR|FORCE|FOREIGN|FROM|FULLTEXT|GOTO|GRANT|GROUP|HAVING|HIGH_PRIORITY|HOUR_MICROSECOND|HOUR_MINUTE|HOUR_SECOND|IF|IGNORE|IN|INDEX|INFILE|INNER|INOUT|INSENSITIVE|INSERT|INT|INT1|INT2|INT3|INT4|INT8|INTEGER|INTERVAL|INTO|IS|ITERATE|JOIN|KEY|KEYS|KILL|LABEL|LEADING|LEAVE|LEFT|LIKE|LIMIT|LINEAR|LINES|LOAD|LOCALTIME|LOCALTIMESTAMP|LOCK|LONG|LONGBLOB|LONGTEXT|LOOP|LOW_PRIORITY|MATCH|MEDIUMBLOB|MEDIUMINT|MEDIUMTEXT|MIDDLEINT|MINUTE_MICROSECOND|MINUTE_SECOND|MOD|MODIFIES|NATURAL|NOT|NO_WRITE_TO_BINLOG|NULL|NUMERIC|ON|OPTIMIZE|OPTION|OPTIONALLY|OR|ORDER|OUT|OUTER|OUTFILE|PRECISION|PRIMARY|PROCEDURE|PURGE|RAID0|RANGE|READ|READS|REAL|REFERENCES|REGEXP|RELEASE|RENAME|REPEAT|REPLACE|REQUIRE|RESTRICT|RETURN|REVOKE|RIGHT|RLIKE|SCHEMA|SCHEMAS|SECOND_MICROSECOND|SELECT|SENSITIVE|SEPARATOR|SET|SHOW|SMALLINT|SPATIAL|SPECIFIC|SQL|SQLEXCEPTION|SQLSTATE|SQLWARNING|SQL_BIG_RESULT|SQL_CALC_FOUND_ROWS|SQL_SMALL_RESULT|SSL|STARTING|STRAIGHT_JOIN|TABLE|TERMINATED|THEN|TINYBLOB|TINYINT|TINYTEXT|TO|TRAILING|TRIGGER|TRUE|UNDO|UNION|UNIQUE|UNLOCK|UNSIGNED|UPDATE|USAGE|USE|USING|UTC_DATE|UTC_TIME|UTC_TIMESTAMP|VALUES|VARBINARY|VARCHAR|VARCHARACTER|VARYING|WHEN|WHERE|WHILE|WITH|WRITE|X509|XOR|YEAR_MONTH|ZEROFILL";
	protected $KeyWordArr = array();
	protected $Chars = array();

	private function SetReplace(){
		//遍历关键字并设置替换值
		$this->KeyWordArr = explode('|', $this->KeyWord);	//字符串到数组
		$TempArr = array();
		foreach ($this->KeyWordArr as $value) {
			$length = mb_strlen($value);
			$temp = "";
			for($i = 0; $i < $length; $i++)
				$temp .= '#'.$value[$i];
			$temp .= '#';
			$TempArr[$value] = $temp;
		}
		$this->KeyWordArr = $TempArr;		//替换数组

		//定义敏感字符及其替换内容
		$this->Chars = array(
			'\\' => '\\\\',
			'\'' => '\\\'',
			'"'  => '\"',
			'`'  => '\`',
			'('  => '#[#',
			')'  => '#]#',
		);
	}


	/**
	 * 字符转义（转入）
	 * @param 	string 	$content 	欲转义的字符串
	 * @return 	string 				转换后的字符串
	 */
	public function ConvertTextIn($content)
	{
		$this->SetReplace();

		//替换关键字
		foreach ($this->KeyWordArr as $key => $value) {
			$content = str_replace($key, $value, $content);
			$content = str_replace(strtolower($key), strtolower($value), $content);
		}
		//替换敏感符号
		foreach ($this->Chars as $key => $value) {
			$content = str_replace($key, $value, $content);
		}

		//返回替换后的内容
		return $content;
	}


	/**
	 * 字符转义（转出）
	 * @param 	string 	$content 	欲转义的字符串
	 * @return 	string 				转换后的字符串
	 */
	public function ConvertTextOut($content)
	{
		$this->SetReplace();

		//替换关键字
		foreach ($this->KeyWordArr as $key => $value) {
			$content = str_replace($value, $key, $content);
			$content = str_replace(strtolower($value), strtolower($key), $content);
		}
		//替换敏感符号
		foreach ($this->Chars as $key => $value) {
			$content = str_replace($value, $key, $content);
		}

		//返回替换后的内容
		return $content;
	}
}