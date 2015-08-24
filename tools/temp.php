<?php
require_once (__DIR__.'/../app/register.php');

HttpCurl::set_cookie(get_cookie());

$url = 'https://www.wacai.com/setting/account_list.action?reqBalance=true&type=all&pageInfo.pageIndex=';
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
	$acc = $infos['accountTypeSum'];
	foreach($acc as $infos)
	{
		if (isset($infos['hiddenAccs']))
		{
			foreach ($infos['hiddenAccs'] as $acc_infos)
			{
				$es_params = array();
				$es_params['currency']		= $acc_infos['moneyType']['name'];
				$es_params['orderNo']		= $acc_infos['moneyType']['orderno'];
				$es_params['inserttime']	= date('Y-m-d H:i:s', time());
				$es_params['updatetime']	= date('Y-m-d H:i:s', time());
				$es_params['category']		= $acc_infos['typeName'];
				$es_params['money']			= $acc_infos['balance'];
				$es_params['cardNo']		= (isset($acc_infos['origCard']['cardNo']) ?
					intval($acc_infos['origCard']['cardNo']) : 0);
				var_dump($es_params);
				exit;
			}
		}
	}
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
