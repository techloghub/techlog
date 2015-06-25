<?php
//
// AudiStat v1.3 by Alexandre Dubus
// May 2003
//
// Parameters :
// year=YYYY
// month=MM
// mday=dd
// phpinfo <- to debug

require("config.php");
require("lang.php");

function db_open () {
	global $link;
	global $sql_host, $sql_login,$sql_passe,$sql_dbase;
	$link = mysql_connect($sql_host, $sql_login,$sql_passe)
	        or die ("Can't connect to $sql_host: $!\n");
	mysql_select_db ($sql_dbase)
	        or die ("Can't select database $sql_dbase: $!\n");
}
	
function db_close () {
	global $link;
	mysql_close($link);
}

function my_query ($q) {
	$r = mysql_query($q);
	if (!$r) {
		echo "<!-- query $q failed : " . mysql_error() . " -->\n";
		return;
	}
	return $r;
}

function db_query_daystats($querystring) {
	global $sql_table;
	global $m_name;

	$day_stat         = array();
	$day_stat2        = array();
	$max              = 0;

	$query = "SELECT DAYOFMONTH(time_str), COUNT(*) FROM $sql_table WHERE $querystring GROUP BY 1 ORDER BY 1";
	$result = my_query($query);

	while (list($day,$count) = mysql_fetch_row($result)) {
		$day_stat[$day] = $count;
		if ($max<$count) $max=$count;
	}
	
	$q2 = "SELECT DAYOFMONTH(time_str) , remote_host FROM $sql_table WHERE $querystring GROUP BY 1,2 ORDER BY 1";
	$r2 = my_query($q2);
	reset($day_stat2);
	for($i=0;$i<31;$i++) {$day_stat2[$i]=0;}
	while (list($day,$c) = mysql_fetch_row($r2)) {
		$day_stat2[$day] += 1;
	}
?>
<P>
<TABLE class="Table1" cellSpacing=1 cellPadding=0>
 <TR class="TR1">
  <TH colspan="2">Stats per day</TH></TR>
 <TR class="TR2">
  <TD class="TD10" rowspan="2"><?= $max ?> --</TD>
  <TD class="TD11">
   <TABLE cellSpacing=0 cellpadding=0>
    <TR>
<?php
	reset($day_stat);
	while (list($day,$count) = each($day_stat)) {
?>
     <TD class="TD12">
      <IMG src="images/bleu.png" width="12" height="<?=100*$day_stat[$day]/$max?>"><IMG src="images/orange.gif" width="4" height="<?=100*$day_stat2[$day]/$max?>" STYLE="position:relative; left:-8"></TD>
<?php
        }
?>
    </TR></TABLE></TD></TR>
 <TR class="TR3">
  <TD class="TD1">
   <TABLE cellSpacing=0 cellpadding=0>
    <TR>
<?php
	reset($day_stat);
	$i=0;
	while (list($day,$count) = each($day_stat)) {
		$d = sprintf("%02d",$day);
?>
     <TD class="TD12"><?= $d ?></TD>
<?php
	}
?>
    </TR></TABLE></TD></TR></TABLE>
<?php
}

function db_query_hourstats($querystring) {
	global $sql_table;
	$hour_stat   = array();
	$hour_stat2  = array();

	$query = "SELECT HOUR(time_str), COUNT(*) FROM $sql_table WHERE $querystring GROUP BY 1 ORDER BY 1";
	$result = my_query($query);
	reset($hour_stat);
	$max=0;
	for($i=0;$i<24;$i++) {$hour_stat[$i]=0;}
	while (list($hour,$count) = mysql_fetch_row($result)) {
		if ($count>$max) $max = $count;
		$hour_stat[$hour] = $count;
	}
	$q2 = "SELECT HOUR(time_str) , remote_host FROM $sql_table WHERE $querystring GROUP BY 1,2 ORDER BY 1";
	$r2 = my_query($q2);
	reset($hour_stat2);
	for($i=0;$i<24;$i++) {$hour_stat2[$i]=0;}
	while (list($hour,$count) = mysql_fetch_row($r2)) {
		$hour_stat2[$hour] += 1;
	}
?>
<P>
<TABLE class="Table1" cellSpacing=1 cellPadding=0>
 <TR class="TR1">
  <TH colspan="2">Stats per hour</TH></TR>
 <TR class="TR2">
  <TD class="TD10" rowspan="2"><?= $max ?>--</TD>
  <TD class="TD11">
   <TABLE cellspacing=0 cellPadding=0>
    <TR>
<?php
	for ($hour=0;$hour<24;$hour++) {
?>
     <TD class="TD12">
      <IMG src="images/bleu.png" width="12" height="<?= 100 * $hour_stat[$hour] / $max ?>"><IMG src="images/orange.gif" width="4" height="<?= 100 * $hour_stat2[$hour] / $max ?>" STYLE="position:relative; left:-8"></TD>
<?php
        }
?>
    </TR></TABLE></TD></TR>
 <TR class="TR3">
  <TD class="TD1">
   <TABLE cellspacing=0 cellPadding=0>
    <TR>
<?php
	for ($hour=0;$hour<24;$hour++) {
		$h = sprintf("%02d",$hour);
?>
     <TD class="TD12"><?= $h ?></TD>
<?php
	}
?>
    </TR></TABLE></TD></TR></TABLE>
<?php
}

function db_query_lastsites($querystring) {
	global $sql_table;

	$query = "SELECT remote_host,MAX(time_str) FROM $sql_table WHERE $querystring GROUP BY 1 ORDER BY 2 DESC LIMIT 15";
	$result = my_query($query);
?>
<P>
<TABLE class="Table1" cellSpacing=1>
 <TR class="TR1">
  <TH colspan="3">Last Visitors</TH></TR>
 <TR class="TR1">
  <TH class="TD6">#</TH>
  <TH class="TD5">Date</TH>
  <TH>Hostname</TH></TR>
<?php
	$idx=1;
	while (list($site,$date) = mysql_fetch_row($result)) {
?>
 <TR class="TR2">
  <TD class="TD4"><?= $idx ?> 
   <IMG src="images/closed.gif" id="c<?=$idx?>" class="Outline" width="12" height="12" onclick="clickHandler('c<?=$idx?>')" style="CURSOR: hand"></TD>
  <TD class="TD2"><?= $date ?></TD>
  <TD class="TD2"><?= $site ?></TD></TR>
 <TBODY div id=dc<?=$idx?> style="Display:none">
 <TR class="TR3">
  <TD class="TD4">.</TD>
  <TD class="TD3">&nbsp;</TD>
<?php
		$q3 = "SELECT referer,time_str FROM $sql_table WHERE $querystring AND remote_host=\"$site\" ORDER BY 2 LIMIT 1";
		$r3 = my_query($q3);
		list($ref,$date) = mysql_fetch_row($r3);
		if ($ref == "-") {
?>
  <TD class="TD2">First referrer: direct hit</TD></TR>
<?php
		} else {
			if (strlen($ref) > 55) {
				$sstr = substr($ref,0,55)." ".substr($ref,55);
			} else {
				$sstr = $ref;
			}
?>
  <TD class="TD2">First referrer: <A href="<?= $ref ?>"><?= $sstr ?></A></TD></TR>
<?php
		}
		$q2 = "SELECT request,time_str FROM $sql_table WHERE $querystring AND remote_host=\"$site\" ORDER BY 2 DESC LIMIT 15";
		$r2 = my_query($q2);
		while (list($req,$date) = mysql_fetch_row($r2)) {
?>
 <TR class="TR3">
  <TD class="TD4">.</TD>
  <TD class="TD3"><?= $date ?></TD>
  <TD class="TD2"><A href="<?= $req ?>"><?= $req ?></A></TD></TR>
<?php			
		}
?>
 </TBODY>
<?php
		$idx++;
	}
?>
</TABLE>
<?php
}

function table_head($tablename) {
?>
<P>
<TABLE class="Table1" cellSpacing=1>
 <TR class="TR1">
  <TH colspan="4">Top <?= $tablename ?></TH></TR>
 <TR class="TR1">
  <TH class="TD6">#</TH>
  <TH class="TD7" colspan="2">Hits</TH>
  <TH class="TD8"><?= $tablename ?></TH></TR>
<?php
}

function table_obj($obj_stat,$total,$link) {
	reset ($obj_stat);
	while (list($idx,$val) = each ($obj_stat)) {
		$obj     = $obj_stat[$idx]['obj'];
		$count   = $obj_stat[$idx]['count'];
		$percent = sprintf("%3.2f",$count * 100 / $total);
?>
 <TR class="TR2">
  <TD class="TD4"><?= $idx ?></TD>
  <TD class="TD3"><?= $count ?></TD>
  <TD class="TD2"><IMG src="images/bleu.png" width="<?= 100 * $count/$total ?>" height="8"><?= $percent ?>%</TD>
<?php
		if ($link == 1) {
?>
  <TD class="TD2"><A href="<?= $obj ?>"><?= $obj ?></A></TD></TR>
<?php
		} else {
?>
  <TD class="TD2"><?= $obj ?></TD></TR>
<?php
		}
	}
?>
</TABLE>
<?php
}

function db_query_top($result,$tablename,$link) {
	$obj_stat         = array();
	$total            = 0;
	
	$idx = 1;
	while (list($obj,$count) = mysql_fetch_row($result)) {
		$obj_stat [$idx]['obj']   = $obj;
		$obj_stat [$idx]['count'] = $count;
		$total += $count;
		$idx ++;
	}
	table_head($tablename);
	table_obj($obj_stat,$total,$link);
}


function db_query_topurls($querystring) {
	global $sql_table;

	$url_stat         = array();
	$total            = 0;

	$query = "SELECT request, COUNT(*) FROM $sql_table WHERE $querystring GROUP BY 1 ORDER BY 2 DESC LIMIT 30";
	$result = my_query($query);

	db_query_top($result,"URLs",1);
}

function db_query_topsites($querystring) {
	global $sql_table;

	$query = "select remote_host,COUNT(*) from $sql_table WHERE $querystring GROUP BY 1 ORDER BY 2 DESC LIMIT 30";
	$result = my_query($query);

	db_query_top($result,"Visitors",0);
}


function db_query_toprefs($querystring) {
	global $sql_table;
	global $HTTP_HOST;
	$obj_stat = array();

	$query = "select referer,COUNT(*) from $sql_table WHERE $querystring AND referer NOT LIKE \"%$HTTP_HOST%\" GROUP BY 1 ORDER BY 2 DESC LIMIT 20";
	$result = my_query($query);

	$query2 = "select request from $sql_table WHERE $querystring AND referer LIKE \"%$HTTP_HOST%\" ";
	$result2 = my_query($query2);
	$n_local = mysql_num_rows($result2);

	$query3 = "select request from $sql_table WHERE $querystring";
	$result3 = my_query($query3);
	$total = mysql_num_rows($result3);

	$idx = 1;
	while (list($obj,$count) = mysql_fetch_row($result)) {
		$obj_stat [$idx]['obj']   = $obj;
		$obj_stat [$idx]['count'] = $count;
		$idx ++;
	}
	table_head("Referrers");
	$idx_max = $idx;
	reset ($obj_stat);
	while (list($idx,$val) = each ($obj_stat)) {
		$obj     = $obj_stat[$idx]['obj'];
		$count   = $obj_stat[$idx]['count'];
		$percent = sprintf("%3.2f",$count * 100 / $total);
?>
 <TR class="TR2">
  <TD class="TD4"><?= $idx ?>
   <IMG src="images/closed.gif" id="cref<?=$idx?>" class="Outline" width="12" height="12" onclick="clickHandler('cref<?=$idx?>')" style="CURSOR: hand"></TD>
  <TD class="TD3"><?= $count ?></TD>
  <TD class="TD2"><IMG src="images/bleu.png" width="<?= 100 * $count/$total ?>" height="8"><?= $percent ?>%</TD>
<?php
		if (strcmp($obj,"-") == 1) {
			if (strlen($obj) > 55) {
				$sstr = substr($obj,0,55)." ".substr($obj,55);
			} else {
				$sstr = $obj;
			}
?>
  <TD class="TD2"><A href="<?= $obj ?>"><?= $sstr ?></A></TD></TR>
<?php
		} else {
?>
  <TD class="TD2">direct hit</TD></TR>
<?php		
		}
?>
 <TBODY div id=dcref<?=$idx?> style="Display:none">
<?php
		$q = "select request,COUNT(*) from $sql_table WHERE $querystring AND referer = \"$obj\" GROUP BY 1 ORDER BY 2 DESC LIMIT 15";
		$r = my_query($q);
		while (list($rr,$c) = mysql_fetch_row($r)) {
?>
 <TR class="TR3">
  <TD class="TD4">.</TD>
  <TD class="TD3" colspan=2>.</TD>
  <TD class="TD2">-----&gt; <A href="<?= $rr ?>"><?=$rr?></A>(<?=$c?>)</TD></TR>
<?php
		}
?>
 </TBODY>
<?php
	}
	$percent = sprintf("%3.2f",$n_local * 100 / $total);
?>
 <TR class="TR2">
  <TD class="TD4"><?= $idx_max ?>
   <IMG src="images/closed.gif" id="cref<?=$idx_max?>" class="Outline" width="12" height="12" onclick="clickHandler('cref<?=$idx_max?>')" style="CURSOR: hand"></TD>
  <TD class="TD3"><?= $n_local ?></TD>
  <TD class="TD2"><IMG src="images/bleu.png" width="<?= 100 * $n_local/$total ?>" height="8"><?= $percent ?>%</TD>
  <TD class="TD2">local referrer</TD></TR>
 <TBODY div id=dcref<?=$idx_max?> style="Display:none">
<?php
	$q = "select request,COUNT(*) from $sql_table WHERE $querystring AND referer LIKE \"%$HTTP_HOST%\" GROUP BY 1 ORDER BY 2 DESC LIMIT 15";
	$r = my_query($q);
	while (list($rr,$c) = mysql_fetch_row($r)) {
?>
 <TR class="TR3">
  <TD class="TD4">.</TD>
  <TD class="TD3" colspan=2>.</TD>
  <TD class="TD2">-----&gt; <?=$rr?> (<?=$c?>) </TD></TR>
<?php
	}
?>
 </TBODY>
</TABLE>
<?php
}




function db_query_topsearch($querystring) {
	global $sql_table;

	$site_stat         = array();
	$site_stat_percent = array();
	$total             = 0;

	$query = "select referer,COUNT(*) from $sql_table WHERE $querystring AND (INSTR(referer,'?q=') OR INSTR(referer,'?p=') OR INSTR(referer,'?query=') OR INSTR(referer,'?qkw=') OR INSTR(referer,'?search=') OR INSTR(referer,'?qr=') OR INSTR(referer,'?string=')) GROUP BY 1 ORDER BY 2 DESC LIMIT 30";
	$result = my_query($query);
	
	$idx = 1;

	while (list($site,$count) = mysql_fetch_row($result)) {
		$url = parse_url($site);
		$query = $url['query'];
		unset ($q);
		$a = explode('&', $query);
		reset ($a);
		while (list($k,$v) = each($a)) {
			$b = split('=',$v);
			$key = htmlspecialchars(urldecode($b[0]));
			$val = htmlspecialchars(urldecode($b[1]));
			if ($key == 'q')
				$q = $val;
		}
		if (isset($q)) {
			$site_stat [$idx]['obj']  = $q;
			$site_stat [$idx]['count'] = $count;
			$total += $count;
			$idx ++;
		}
	}
	table_head("Searchs");
	table_obj($site_stat,$total,0);
}


#Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 4.0; CrazyBrowser 1.0.1)
#Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)
function db_query_topagents($querystring) {
	global $sql_table;

	$query = "SELECT
	IF ( INSTR(user_agent,'MSIE 6.0') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'MSIE 6.0'),8),
	IF ( INSTR(user_agent,'MSIE 5.5') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'MSIE 5.5'),8),
	IF ( INSTR(user_agent,'MSIE 5.0') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'MSIE 5.0'),8),
	IF ( INSTR(user_agent,'MSIE 4.0') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'MSIE 4.0'),8),
	IF ( INSTR(user_agent,'Opera') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Opera'),5),
	IF ( INSTR(user_agent,'Konqueror') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Konqueror'),9),
	IF ( INSTR(user_agent,'Mozilla/4') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Mozilla/4'),9),
	IF ( INSTR(user_agent,'Mozilla/5') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Mozilla/5'),9),
	IF ( INSTR(user_agent,'Mozilla') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Mozilla'),7),
	IF ( INSTR(user_agent,'Google') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Google'),6),
	'Other'
	))))))))))
	,COUNT(*) FROM $sql_table WHERE $querystring GROUP BY 1 ORDER BY 2 DESC LIMIT 30";
	$result = my_query($query);
	db_query_top($result,"Agents",0);
}

function db_query_toposs($querystring) {
	global $sql_table;

	$query = "SELECT
	IF ( INSTR(user_agent,'Windows 2000') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Windows 2000'),12),
	IF ( INSTR(user_agent,'Windows 98') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Windows 98'),10),
	IF ( INSTR(user_agent,'Windows 95') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Windows 95'),10),
	IF ( INSTR(user_agent,'Win95') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Win95'),5),
	IF ( INSTR(user_agent,'Win98') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Win98'),5),
	IF ( INSTR(user_agent,'Windows NT 4.0') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Windows NT 4.0'),14),
	IF ( INSTR(user_agent,'Windows NT 5.0') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Windows NT 5.0'),14),
	IF ( INSTR(user_agent,'Windows NT 5.1') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Windows NT 5.1'),14),
	IF ( INSTR(user_agent,'Windows XP') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Windows XP'),10),
	IF ( INSTR(user_agent,'Windows ME') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Windows ME'),10),
	IF ( INSTR(user_agent,'WinNT') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'WinNT'),5),
	IF ( INSTR(user_agent,'Mac_PowerPC') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Mac_PowerPC'),11),
	IF ( INSTR(user_agent,'Macintosh') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Macintosh'),9),
	IF ( INSTR(user_agent,'SunOS') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'SunOS'),5),
	IF ( INSTR(user_agent,'Linux') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Linux'),5),
	IF ( INSTR(user_agent,'Windows') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Windows'),7),
	IF ( INSTR(user_agent,'Google') > 0 , SUBSTRING(user_agent,INSTR(user_agent,'Google'),6),
	'Other'
	)))))))))))))))))
	,COUNT(*) FROM $sql_table WHERE $querystring GROUP BY 1 ORDER BY 2 DESC LIMIT 30";

	$result = my_query($query);
	db_query_top($result,"Operating Systems",0);
}


function db_query_topctrys($querystring) {
	global $sql_table;
	global $g_tld;

	$tld_stat         = array();
	$total            = 0;
	$total_unknown    = 0;

	$query = "SELECT RIGHT(remote_host,INSTR(REVERSE(remote_host),\".\")-1),COUNT(*) FROM $sql_table WHERE $querystring GROUP BY 1 ORDER BY 2 DESC LIMIT 30";
	$result = my_query($query);

	$idx=1;
	while (list($tld, $count) = mysql_fetch_row($result)) {
		if (isset($g_tld[$tld])) {
			$tld_stat[$idx]['obj'] = $g_tld[$tld];
			$tld_stat[$idx]['count']= $count;
			$idx++;
		} else
			$total_unknown += $count;
		$total += $count;
	}
	if ($total_unknown>0) {
		$tld_stat[$idx]['obj'] = "Unresolved/Unknown";
		$tld_stat[$idx]['count'] = $total_unknown;
	}
	table_head("Countries");
	table_obj($tld_stat,$total,0);
}

function db_query_all_months($querystring,$paramsup) {
	global $sql_table;
	global $m_name;
	global $googlestats;
	
	$query = "SELECT YEAR(time_str),MONTH(time_str),DAYOFMONTH(time_str),COUNT(*),COUNT(DISTINCT remote_host) FROM $sql_table $querystring GROUP BY 1,2,3 ORDER BY 1,2,3";
	$result = my_query($query);

?>
<P>
<TABLE class="Table1" cellSpacing=1>
 <TR class="TR1">
  <TH rowspan="2">Period</TH>
  <TH colspan="2">Mean hits per day</TH>
  <TH colspan="2">Total</TH></TR>
 <TR class="TR1">
  <TH>Visits</TH>
  <TH>Hits</TH>
  <TH>Visits</TH>
  <TH>Hits</TH></TR>
<?php
	$idx=10;
	$init = 1;
	$yyear = 0;
	$mmonth = 0;
	$one = 0;
	$chain = "";
	$thits = 0;
	$tvisits = 0;

	while (list($year,$month,$mday,$hits,$visits) = mysql_fetch_row($result)) {
		$idx++;
		if ($init == 0 && ($year != $yyear || $month != $mmonth)) {
			if ($one == 1) {
?>
 </TBODY>
<?php
			}
			$one = 1;
?>
 <TR class="TR2">
  <TD class="TD9"><A HREF="?year=<?= $yyear ?>&month=<?= $mmonth ?><?= $paramsup ?>"><?= $m_name[$mmonth] ?> <?= $yyear ?></A>
   <IMG src="images/closed.gif" id="c<?=$idx?>" class="Outline" width="12" height="12" onclick="clickHandler('c<?=$idx?>')" style="CURSOR: hand"></TD>
  <TD><?= round($mvisits / $nday) ?></TD>
  <TD><?= round($mhits / $nday) ?></TD>
  <TD><?= $mvisits ?></TD>
  <TD><?= $mhits ?></TD></TR>
 <TBODY div id="dc<?=$idx?>" style="display:none;">
<?= $chain ?>
<?php
			$chain = "";
			$nday= 0;
			$mvisits = 0;
			$mhits = 0;
		}
		$mmonth = $month;
		$yyear = $year;
		$nday ++;
		$init = 0;
		$mhits += $hits;
		$mvisits += $visits;
		$thits += $hits;
		$tvisits += $visits;
		$chain = $chain .
" <TR class=\"TR2\">
  <TD class=\"TD9\" colspan=\"3\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<A HREF=\"?year=$year&month=$month&mday=$mday$paramsup\">$mday</A><BR></TD>
  <TD>$visits</TD>
  <TD>$hits</TD></TR>
";
	}
if ($nday >0 ) {
?>
 </TBODY>
 <TR class="TR2">
  <TD class="TD9"><A HREF="?year=<?= $yyear ?>&month=<?= $mmonth ?><?= $paramsup ?>"><?= $m_name[$mmonth] ?> <?= $yyear ?></A>
   <IMG src="images/closed.gif" id="c<?=$idx?>" class="Outline" width="12" height="12" onclick="clickHandler('c<?=$idx?>')" style="CURSOR: hand"></TD>
  <TD><?= round($mvisits / $nday) ?></TD>
  <TD><?= round($mhits / $nday) ?></TD>
  <TD><?= $mvisits ?></TD>
  <TD><?= $mhits ?></TD></TR>
 <TBODY div id="dc<?=$idx?>" style="display:none;">
<?= $chain ?>
 </TBODY>
<?php
}
?>
 <TR class="TR3">
  <TD colspan="3"><b>Total</b><BR></TD>
  <TD><?= $tvisits ?></TD>
  <TD><?= $thits ?></TD></TR></TABLE>
<?php
if ($googlestats == 1) {
?>
<A HREF="?">All Stats</A>
<?php
}else {
?>
<A HREF="?googlestats=1">Google Stats</A>
<?php
}

}

function html_header() {
	global $HTTP_HOST;
	global $m_name;
	global $mday;
	global $month;
	global $year;
	global $_SERVER;
	global $googlestats;

	$HTTP_HOST = $_SERVER['HTTP_HOST'];
	$title = "Site statistics -- $HTTP_HOST";
	if (isset($month) && isset($year)) {
		$title = $title . " - " . $mday. " " . $m_name[$month] . " " . $year;
	}

	if ($googlestats == 1) {
#		$title2 = "<IMG SRC=\"images/nav_first.gif\"><IMG SRC=\"images/nav_page.gif\"><IMG SRC=\"images/nav_current.gif\"><IMG SRC=\"images/nav_last.gif\"> $title";
		$title2 = "<span style=\"letter-spacing: 6pt\">Google &nbsp;</span> $title";
		$title = "Google $title";
	} else {
		$title2 = $title;
	}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
 <HEAD>
  <META http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <SCRIPT language="JavaScript">
   <!--
   function clickHandler(id) {
    var targetId, srcElement, targetElement;
    var srcElement    = document.getElementById(id);
    var targetElement = document.getElementById("d" + id);
    if (targetElement.style.display == "none") {
     srcElement.src              = "images/open.gif";
     targetElement.style.display = "";
    } else {
     srcElement.src              = "images/closed.gif";
     targetElement.style.display = "none";
    }
   }
   //-->
  </SCRIPT>
  <LINK rel="StyleSheet" type="text/css" href="php.css">
   <TITLE><?= $title ?></TITLE>
 </HEAD>
<BODY class="Body">
<CENTER>
<TABLE class="Table1" cellSpacing="1">
    <TR class="TR1">
      <TD><H2><?= $title2 ?></H2></TD></TR></TABLE>
<HR>
<?php
}

function html_footer() {
	global $m_name;

	// compute the current date
	$today   = getdate();
	$jour    = $today['mday'];
	$mois    = $m_name[$today['mon']];
	$annee   = $today['year'];
	$heure   = $today['hours'];
	$minute  = $today['minutes'];
	$seconde = $today['seconds'];
?>
</CENTER>
<P>
<HR>
<SMALL><STRONG>@ <A HREF="http://adubus.free.fr/audistat/">AudiStat</A> v1.3</STRONG></SMALL>
</BODY></HTML>
<?php
}

function db_update_remote_host() {
	global $sql_table;
	
	$query = "SELECT remote_host FROM $sql_table GROUP BY 1";
	$result = my_query($query);
	while (list($site) = mysql_fetch_row($result)) {
		if (preg_match("/^\d+\.\d+\.\d+\.\d+$/",$site)) {
			$host = gethostbyaddr($site);
			if (!isset($host) || preg_match("/^\d+\.\d+\.\d+\.\d+$/",$host)) {
				// add a final space to avoid re-matching
				$host = $site . " ";
			}
			$q2 = "UPDATE $sql_table SET remote_host=\"$host\" WHERE remote_host=\"$site\"";
			$r2 = my_query($q2);
		}
	}
}


if (isset($_GET['phpinfo'])) {phpinfo();return;}

if (isset($_GET['month'])) {$month = $_GET['month'];}
if (isset($_GET['year'])) {$year = $_GET['year'];}
if (isset($_GET['mday'])) {$mday = $_GET['mday'];}

if (isset($_GET['googlestats'])) {$googlestats = 1;} else {$googlestats = 0;}


error_reporting(0);
db_open();
db_update_remote_host();
html_header ();

#test
if ($googlestats == 1) {
	$querystring = "user_agent LIKE \"%Google%\" AND ";
	$paramsup = "&googlestats=1";
} else {
	$querystring = "";
	$paramsup = "";	
}

if (isset($month) && isset($year) && !isset($mday)) {
	$querystring .= "MONTH(time_str) = $month AND YEAR(time_str) = $year";
	db_query_daystats  ($querystring);
	db_query_hourstats ($querystring);
	db_query_lastsites ($querystring);
	db_query_toprefs   ($querystring);
	db_query_topurls   ($querystring);
	db_query_topsites  ($querystring);
	db_query_topsearch ($querystring);
	db_query_topagents ($querystring);
	db_query_toposs    ($querystring);
	db_query_topctrys  ($querystring);
} elseif (isset($month) && isset($year) && isset($mday)) {
	$querystring .= "DAYOFMONTH(time_str) = $mday AND MONTH(time_str) = $month AND YEAR(time_str) = $year";
	db_query_hourstats ($querystring);
	db_query_lastsites ($querystring);
	db_query_toprefs   ($querystring);
	db_query_topurls   ($querystring);
	db_query_topsites  ($querystring);
	db_query_topsearch ($querystring);
	db_query_topagents ($querystring);
	db_query_toposs    ($querystring);
	db_query_topctrys  ($querystring);
} elseif (!isset($month) && !isset($year)) {
	if ($googlestats == 1) {
		$querystring = "WHERE user_agent LIKE \"%Google%\"";
	}
	db_query_all_months($querystring,$paramsup);
}
html_footer();
db_close();
?>
