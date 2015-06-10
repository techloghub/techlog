<?php
require_once (dirname(__FILE__).'/../'.'library/zeyublog.php');

ini_set('date.timezone','Asia/Shanghai');

LogOpt::init('mood_loader', true);
$options = getopt('i:c:d:');
if (isset($options['c']) && trim($options['c']) != '')
{
	$infos = array();
	if (isset($options['i']) && trim($options['i']) != '')
	{
		$infos['inserttime'] = $options['i'];
	}
	$infos ['contents'] = $options['c'];
	if (isset($options['d']) && trim($options['d']) != '')
	{
		$infos['mood_id'] = $options['d'];
	}
	$mood_id = MySqlOpt::insert('mood', $infos, true);
	if ($mood_id === false)
	{
		LogOpt::set('exception', 'mood add error',
			MySqlOpt::errno(), MySqlOpt::error()
		);

		continue;
	}
	unset($infos['contents']);
	unset($infos['mood_id']);
	LogOpt::set('info', 'mood add success', 'mood_id', $mood_id);
}
else
{
	echo 'usage: you can also use by ----'
		.' php mood_loader -c contents [-i inserttime] [-d mood_id]'.PHP_EOL;

	while (1)
	{
		echo '> ';
		$contents = fgets(STDIN);
		$contents = trim($contents);
		if (strtolower($contents) === 'quit')
			break;
		if ($contents != '' && $contents != null)
		{
			echo '确认添加至说说吗？ Y/N [N]：';
			$sure = fgets(STDIN);
			if (trim($sure[0]) == 'Y' || trim($sure[0]) == 'y')
			{
				$mood_id = MySqlOpt::insert(
					'mood',
					array('contents'=>$contents),
					true
				);

				if ($mood_id === false)
				{
					LogOpt::set('exception', 'mood add error',
						MySqlOpt::errno(), MySqlOpt::error(),
						'contents', $contents
					);

					continue;
				}

				LogOpt::set('info', 'mood add success', 'mood_id', $mood_id);
			}
		}
	}
}
?>
