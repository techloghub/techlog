<?php
require_once (__DIR__.'/../app/register.php');
LogOpt::init('mood_loader', true);

$options = getopt('i:c:d:');
if (isset($options['c']) && trim($options['c']) != '')
{
	$infos = array();
	$infos['inserttime'] = !empty($options['i']) ? $options['i'] : 'now()';
	$infos['contents'] = $options['c'];
	if (isset($options['d']) && trim($options['d']) != '')
	{
		$mood = Repository::findOneFromMood(
			array('eq' => array('mood_id' => $options['d'])));
		foreach ($infos as $key=>$value)
		{
			$func = 'set_'.$key;
			$mood->$func($value);
		}
	}
	else
	{
		$mood = new MoodModel($infos);
	}
	$mood_id = Repository::persist($mood);
	if ($mood_id === false)
	{
		LogOpt::set('exception', 'mood add error');
		return;
	}
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
				$mood = new MoodModel(array('contents' => $contents, 'inserttime' => 'now()'));
				$mood_id = Repository::persist($mood);
				if ($mood_id === false)
				{
					LogOpt::set('exception', 'mood add error',
						'contents', $contents);
					continue;
				}
				LogOpt::set('info', 'mood add success', 'mood_id', $mood_id);
			}
		}
	}
}
?>
