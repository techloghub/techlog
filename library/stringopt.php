<?php
class StringOpt
{
	public static function spider_string (
		$string, $begtab, $endtab, &$remain_str=null
	)
	{
		if ($begtab == '')
		{
			$string = '<![-INF]>'.$string;
			$begtab = '<![-INF]>';
		}
		if ($endtab == '')
		{
			$string .= '<![+INF]>';
			$endtab = '<![+INF]>';
		}
		$tabs = explode('<![||]>', $begtab);
		$ret = self::orstropt($string, $tabs, $string);
		if ($ret === false || $ret === null)
			return $ret;
		$tabs = explode('<![||]>', $endtab);
		$remain = '';
		$ret = self::orstropt($string, $tabs, $remain);
		if ($remain_str !== null)
			$remain_str = $remain;
		return $ret === null ? $string : $ret;
	}

	private static function orstropt($string, $tabs, &$remain)
	{
		if (!is_array($tabs) || count($tabs)<1)
			return false;
		foreach ($tabs as $tab)
		{
			if ($tab === null || $string === null)
				return null;
			$andtabs = explode('<![&&]>', $tab);
			$string = self::andstropt($string, $andtabs, $remain);
			if ($string === false)
				return false;
			if ($remain !== null)
				return $string;
		}
		return null;
	}

	private static function andstropt($string, $tabs, &$remain)
	{
		if (!is_array($tabs) || count($tabs)<1)
			return false;
		$lasttab = '';
		$remain = $string;
		$string = '';
		foreach ($tabs as $tab)
		{
			$pos = strpos($remain, $tab);
			if ($pos === false)
				return null;
			$string = $string.$lasttab.substr($remain, 0, $pos);
			$remain = substr($remain, $pos+strlen($tab));
			$lasttab = $tab;
		}
		return $string;
	}
}
?>
