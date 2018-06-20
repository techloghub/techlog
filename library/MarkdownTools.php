<?php
class MarkdownTools {
	public static function treat_articla($content) {
		$lines = explode(PHP_EOL, $content);
		$contents = '';
		for ($index = 0; $index < count($lines); ++$index) {
			$line = $lines[$index];
			$line = trim($line);
			if (empty($line)) {
				$contents .= PHP_EOL;
			} else if ($line == '<div>') {
				$contents .= PHP_EOL.PHP_EOL
					."#以下内容需要手动处理：".PHP_EOL;
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = $lines[$index];
					if ($line == '</div>')
						break;
					$contents .= self::str_trans($line).PHP_EOL.PHP_EOL;
				}
			} else if ($line == '<table>') {
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
					if ($line == '</table>') {
						break;
					} else if (substr($line, 0, 9) == '<caption>') {
						$caption = substr($line, 9);
						$contents .= '####'.self::str_trans($caption).PHP_EOL;
					} else {
						$tds = explode("\t", $line);
						if (substr($line, 0, 4) == '<tr>') {
							$tds = array_shift($tds);
						}
						$contents .= self::str_trans('|'.implode('|', $tds).'|').PHP_EOL;
						if (substr($line, 0, 4) == '<tr>') {
							$contents .= '|';
							for ($i = 0; $i < count($tds); ++$i) {
								$contents .= '--|';
							}
							$contents.PHP_EOL;
						}
					}
				}
			} else if ($line == '<ol>') {
				$i = 1;
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
					if ($line == '</ol>' || $line == '</ul>') {
						break;
					}
					$contents .= self::str_trans($i.'. '.$line).PHP_EOL;
				}
			} else if ($line == '<ul>') {
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
					if ($line == '</ol>' || $line == '</ul>') {
						break;
					}
					$contents .= '- '.self::str_trans($line).PHP_EOL;
				}
			} else if (substr($line, 0, 4) == '<img') {
				$contents .= PHP_EOL.PHP_EOL.'#此处有图片'.PHP_EOL.PHP_EOL;
			} else if (substr($line, 0, 5) == '<code') {
				$mode = StringOpt::spider_string($line, 'mode="', '"');
				if (empty($mode))
					$mode = 'cpp';
				$contents .= '```'.$mode.PHP_EOL;
				$code_line = 0;
				$is_php = false;
				if ($mode === 'php' && $lines[$index+1] != '<?php') {
					$contents.= '\<?php'.PHP_EOL;
					$is_php = true;
				}
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = $lines[$index];
					if (trim($line) === '</code>') {
						if ($is_php) {
							$contents .= '?>'.PHP_EOL;
						}
						$contents .= '```'.PHP_EOL;
						break;
					}
					$contents .= self::str_trans($line).PHP_EOL;
				}
			} else if (substr($line, 0, 4) === '<h1>') {
				$contents .= self::str_trans('#'.substr($line, 4)).PHP_EOL;
			} else if (substr($line, 0, 4) === '<h3>') {
				$contents .= self::str_trans('##'.substr($line, 4)).PHP_EOL;
			} else if (substr($line, 0, 3) == '<a ') {
				$id = StringOpt::spider_string($line, 'id="', '"');
				$title = Repository::findTitleFromArticle(
					array('eq' => array('article_id' => $id))
				);
				$contents .= '['.self::str_trans($title).'](http://techlog.cn/article/list/'.$id.')'.PHP_EOL;
			} else {
				$contents .= self::str_trans($line).PHP_EOL;
			}
		}
		return $contents;
	}

	private static function str_trans($str) {
		$str = str_replace('<', '\<', $str);
		return $str;
	}
}
?>
