<?php
ini_set('date.timezone','Asia/Shanghai');

class LogOpt
{
	private static $condition = null;
	private static $echo = false;
	public static function init($con, $needecho=false)
	{
		self::$condition = strtoupper($con);
		self::$echo = $needecho;
	}
	public static function set_log ($label, $info)
	{
		#file_put_contents(
		#	'/home/zeyu/Workspace/log/zeyu_bloglog_'.date('Ymd'),
		#	self::$condition."\t".date('Y-m-d H:i:s')."\t".$label.":\t".$info."\n",
		#	FILE_APPEND
		#);

		#if (self::$echo)
		#{
		   echo $label.":\t".$info.PHP_EOL;
		#}
	}
	public static function set ()
	{
		if (($arg_num = func_num_args()) < 2)
			return false;
		$arg_list = func_get_args();
		
		$info = '';
		for ($i = 0; $i < $arg_num; $i+=2)
		{
			if (is_array($arg_list[$i]))
			{
				foreach ($arg_list[$i] as $key => $value)
					$info .= "\t".'['.$key.':'.$value.']';
				$i -= 1;
			}
			$info .= "\t".'['.$arg_list[$i].':'.$arg_list[$i+1].']';
		}
		$logfile = '/home/zeyu/Workspace/log/zeyu_bloglog_'.date('Ymd');
		#file_put_contents(
		#	$logfile,
		#	self::$condition."\t".date('Y-m-d H:i:s').$info."\n",
		#	FILE_APPEND
		#);

		#chmod($logfile, 0777);
		#if (self::$echo)
		#{
		   echo self::$condition."\t".$info.PHP_EOL;
		#}
	}
}
?>
