<?php
require_once (__DIR__.'/../app/register.php');

$options = getopt('m:');
if (!isset ($options['m']))
{
	echo 'usage: php get_wacai_datas.php -m month'
		.PHP_EOL;
	exit;
}

HttpCurl::set_cookie(get_cookie());
$url = 'https://www.wacai.com/biz/ledger_list.action?'
	.'cond.date='.$options['m'].'-01&cond.date_end='.$options['m'].'-31'
	.'&cond.reimbursePrefer=0&cond.withDaySum=false&pageInfo.pageIndex=';
$pageCount = 1;
for ($i=1; $i<=$pageCount; $i++)
{
	$ret = HttpCurl::get($url.$i);
	if ($ret['body'] == false)
	{
		echo 'get_earnings ERROR: url '.$url.$i.'; error '.$ret['error'].PHP_EOL;
		exit;
	}
	$infos = json_decode($ret['body'], true);
	if ($infos == false)
	{
		echo 'get_earnings ERROR: url '.$url.$i.'; body '.$ret['body'].PHP_EOL;
		exit;
	}
	if ($i == 1)
		$pageCount = $infos['pi']['pageCount'];
	$ledgers = $infos['ledgers'];
	foreach ($ledgers as $infos)
	{
		if (!isset($infos['date']) || !isset($infos['recType']))
		{
			echo 'INFOS ERROR : '.json_encode($infos).PHP_EOL;
			continue;
		}
		$es_params = array();
		$es_params['date']		= (isset($infos['date']) ? $infos['date'] : '');
		$es_params['recType']	= (isset($infos['recType']) ? $infos['recType'] : '');
		$es_params['tag'] 		= (isset($infos['tag']) ? $infos['tag'] : '');
		$es_params['comment'] 	= (isset($infos['comment']) ? $infos['comment'] : '');
		if ($es_params['recType'] == 3)
		{
			$es_params['type'] = -1;
			$es_params['fromAcc'] = $infos['srcAcc'];
			$es_params['toAcc'] = $infos['tgtAcc'];
			$es_params['money'] = $infos['transin'];
			$es_params['currency'] = ($infos['srcMflag'] == '￥' ? '人民币' : '美元');
		}
		else
		{
			$es_params['type'] = $infos['type'];
			$es_params['fromAcc'] = $infos['acc'];
			$es_params['toAcc'] = '';
			$es_params['money'] = $infos['money'];
			$es_params['currency'] = ($infos['mflag'] == '￥' ? '人民币' : '美元');
		}
		$category = explode('-', $infos['typeTitle']);
		$es_params['category'] = $category[0];
		$es_params['subcategory'] = (isset($category[1]) ? $category[1] : '');

		$es_url = 'http://localhost:9200/wacai/ledgers/'.$infos['id'].'/_create';
		$ret = HttpCurl::put($es_url, json_encode($es_params));
		if ($ret['code'] == 409)
		{
			echo $infos['id']."\t".json_encode($es_params).PHP_EOL;
		}
		else if ($ret['body'] == false || !in_array($ret['code'], array(200, 201)))
		{
			var_dump($ret);
			exit;
		}
	}
	sleep(3);
}

function get_cookie()
{
	$url = 'https://www.wacai.com/user/user!login.action?cmd=null';
	$config = file_get_contents(APP_PATH.'/config.json');
	$config = json_decode($config, true);
	$config = $config['wacai'];
	$post_data = array(
		'user.account' => $config['user'],
		'user.pwd' => $config['pwd']
	);
	$ret = HttpCurl::post($url, $post_data);
	if ($ret['body'] == false)
	{
		echo 'get_cookie ERROR: '.$ret['error'].PHP_EOL;
		return false;
	}
	return (isset($ret['header']['set_cookie']) ?
		$ret['header']['set_cookie'] : false);
}
?>
