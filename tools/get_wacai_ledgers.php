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
		if (empty($infos['id']) || !isset($infos['date']) || !isset($infos['recType']))
		{
			echo 'INFOS ERROR : '.json_encode($infos).PHP_EOL;
			continue;
		}
		$model = new LedgersModel(
			array(
				'esid' => isset($infos['id']) ? $infos['id'] : '',
				'date' => isset($infos['date']) ? $infos['date'] : '',
				'recType' => isset($infos['recType']) ? $infos['recType'] : '',
				'tag' => isset($infos['tag']) ? $infos['tag'] : '',
				'comment' => isset($infos['comment']) ? $infos['comment'] : '',
				'inserttime' => 'now()'
			)
		);
		if ($model->get_recType() == 3)
		{
			$model->set_type(-1);
			$model->set_fromAcc($infos['srcAcc']);
			$model->set_toAcc($infos['tgtAcc']);
			$model->set_money($infos['transin']);
			$infos['mflag'] = $infos['srcMflag'];
		}
		else if ($model->get_recType() == 4)
		{
			$model->set_type($infos['type']);
			$model->set_fromAcc($infos['acc']);
			$model->set_toAcc($infos['srcAcc']);
			$model->set_money($infos['money']);
		}
		else if ($model->get_recType() == 5)
		{
			$model->set_type($infos['type']);
			$model->set_fromAcc($infos['srcAcc']);
			$model->set_toAcc($infos['acc']);
			$model->set_money($infos['money']);
		}
		else
		{
			$model->set_type($infos['type']);
			$model->set_fromAcc($infos['acc']);
			$model->set_toAcc('');
			$model->set_money($infos['money']);
		}
		switch ($infos['mflag'])
		{
		case '￥':
			$model->set_currency('人民币');
			break;
		case '◎':
			$model->set_currency('虚拟币');
			break;
		case '$':
			$model->set_currency('美元');
			break;
		default:
			$model->set_currency('');
			break;
		}
		$category = explode('-', $infos['typeTitle']);
		$model->set_category($category[0]);
		$model->set_subcategory((isset($category[1]) ? $category[1] : ''));
		$id = Repository::persist($model);

		echo 'INFO: BACKUP'."\t".$infos['id']."\t"
			.json_encode($infos).PHP_EOL;

		$account = Repository::findOneFromAccount(
			array('eq' => array('esid' => $model->get_fromAcc()))
		);
		if ($account != false)
		{
			if ($model->get_recType() == 2)
				$account->set_money($model->get_money() + $account->get_money());
			else
				$account->set_money($account->get_money() - $model->get_money());
			$account->set_orderNo($account->get_orderNo() + 1);
			$account->set_updatetime('now()');
			$id = Repository::persist($account);
		}

		if (empty($model->get_toAcc()))
			continue;
		$account = Repository::findOneFromAccount(
			array('eq' => array('esid' => $model->get_toAcc()))
		);
		if ($account == false)
			continue;
		$account->set_money($model->get_money() + $account->get_money());
		$account->set_orderNo($account->get_orderNo() + 1);
		$account->set_updatetime('now()');
		$id = Repository::persist($account);
	}
	sleep(3);
}

function get_cookie()
{
	#$url = 'https://www.wacai.com/user/user!login.action?cmd=null';
	#$config = file_get_contents(APP_PATH.'/config.json');
	#$config = json_decode($config, true);
	#$config = $config['wacai'];
	#$post_data = array(
	#	'user.account' => $config['user'],
	#	'user.pwd' => $config['pwd']
	#);
	#$ret = HttpCurl::post($url, $post_data);
	#if ($ret['body'] == false)
	#{
	#	echo 'get_cookie ERROR: '.$ret['error'].PHP_EOL;
	#	return false;
	#}
	#return (isset($ret['header']['set_cookie']) ?
	#	$ret['header']['set_cookie'] : false);
	return 'wctk=13f1b1021f9c43edaa4513c7fbc1889c';
}
?>
