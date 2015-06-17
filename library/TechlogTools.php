<?php
require_once (dirname(__FILE__).'/'.'../library/mysqlopt.php');
require_once (dirname(__FILE__).'/'.'../library/logopt.php');
require_once (dirname(__FILE__).'/'.'../library/stringopt.php');

class TechlogTools
{
	public static function pre_treat_article ($file)
	{
		$font = '';
		$lines = explode(PHP_EOL, $file);
		$contents = '';
		$code_id = 'a';
		$codes = array();
		for ($index=0; $index<count($lines); ++$index)
		{
			$line = $lines[$index];
			$line = trim($line);
			if (empty($line))
				$contents .= '<p>&nbsp;</p>';
			else if ($line == '<div>')
			{
				while (1)
				{
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
					if ($line == '</div>')
						break;
					$contents .= $line.PHP_EOL;
				}
			}
			else if ($line == '<table>')
			{
				$contents .=
					'<table class="stdtable" border="1" style="font-size:18;">';
				while (1)
				{
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
					if ($line == '</table>')
					{
						$contents .= $line;
						break;
					}
					else if (substr($line, 0, 9) == '<caption>')
					{
						$caption = substr($line, 9);
						$contents .=
							'<caption'
							.' style=\'font-weight:bold;'
							.' font-family:"PT Serif","Georgia","Helvetica Neue",Arial,sans-serif;'
							.' background-color:#D2E1F0; height:30px;\'>'
							.$caption
							.'</caption>';
					}
					else
					{
						$tds = explode("\t", $line);
						if (substr($line, 0, 4) == '<tr>')
						{
							$tds[0] = substr($tds[0], 4);
							$contents .= '<thead>'
								.'<tr style="background-color:#C5C5C5;">';
							if ($tds[0][0] == '[' && $tds[0][strlen($tds[0])-1] == ']')
							{
								$widths = substr($tds[0], 1, strlen($tds[0])-2);
								$widths = explode(":", $widths);
								for ($i=1; $i<count($tds); ++$i)
								{
									if (isset($widths[$i-1]))
										$contents .= '<td width="'.$widths[$i-1].'%"><strong>'
											.$tds[$i].'</strong></td>';
									else
										$contents .= '<td><strong>'.$tds[$i].'</strong></td>';
								}
							}
							else
							{
								$contents .= '<td><strong>'
									.implode('</strong></td><td><strong>', $tds)
									.'</strong></td>';
							}
							$contents .= '</tr></thead>';
						}
						else
						{
							$tmp_tds = array();
							foreach ($tds as $td)
							{
								if ($td !== '')
									$tmp_tds[] = $td;
							}
							$contents .= '<tr><td>'
								.implode('</td><td>', $tmp_tds)
								.'</td></tr>';
						}
					}
				}
			}
			else if ($line == '<ol>' || $line == '<ul>')
			{
				$contents .= $line;
				while (1)
				{
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
					if ($line == '</ol>' || $line == '</ul>')
					{
						$contents .= $line;
						break;
					}
					else
					{
						$line = self::str_trans($line);
						if ($font != '')
						{
							$line = '<span style="font-family:'.$font.';">'
								.$line
								.'</span>';
						}
						$contents .= '<p><li>'.$line.'</li></p>';
					}
				}
			}
			else if (substr($line, 0, 5) == '<font')
				$font = StringOpt::spider_string($line, '<font ', '>');
			else if ($line == '</font>')
				$font = '';
			else if (substr($line, 0, 4) == '<img')
			{
				$id = StringOpt::spider_string($line, 'id="', '"');
				if ($id != null)
				{
					$image_id = intval(trim($id));
					$sql = 'select path from images where image_id='.$image_id;
					$path = MySqlOpt::select_query($sql);
					if (isset($path[0]['path']))
					{
						$path = $path[0]['path'];
						if (StringOpt::spider_string($line, 'width="', '"') == null)
						{
							$image_info = GetImageSize(dirname(__FILE__).'/../resource/'.$path);
							$image_info = $image_info['3'];
							$width = StringOpt::spider_string($image_info, 'width="', '"');
							$width = intval(trim($width));
							if ($width > '765')
							{
								$line =
									str_replace(
										'id="'.$id.'"',
										'src="'.$path.'" width="765px;"',
										$line
									);
							}
							else
							{
								$line =
									str_replace(
										'id="'.$id.'"',
										'src="'.$path.'"',
										$line
									);
							}
						}
						else
						{
							$line =
								str_replace(
									'id="'.$id.'"',
									'src="'.$path.'"',
									$line
								);
						}
					}
					else
					{
						$line = '<strong>图片ID不存在</strong>';
					}
				}
				else
				{
					$path = StringOpt::spider_string($line, 'src="', '"');
				}
				$contents .= '<p style="text-indent:0em;">'
					.'<a target="_blank" href="'.$path.'">'
					.$line.'</a></p><p>&nbsp;</p>';
			}
			else if (substr($line, 0, 5) == '<code')
			{
				$mode = StringOpt::spider_string($line, 'mode="', '"');
				if (empty($mode))
					$mode = 'c_cpp';
				$code = '';
				$code_line = 0;
				$is_php = false;
				if ($mode === 'php' && $lines[$index+1] != '<?php')
					$is_php = true;
				while (1)
				{
					$index++;
					if ($index >= count($lines))
						break;
					$line = $lines[$index];

					if (trim($line) === '</code>')
						break;

					$code_wrap = 0;
					for ($idx = 0; $idx < strlen($line); ++$idx )
					{
						if ($line[$idx] == "\t")
						{
							$code_wrap += 4;
							continue;
						}
						$value = ord($line[$idx]);
						if($value > 127)
						{
							$code_wrap++;
							if ($value >= 192 && $value <= 223)
								$idx++;
							elseif ($value >= 224 && $value <= 239)
								$idx = $idx + 2;
							elseif ($value >= 240 && $value <= 247)
								$idx = $idx + 3;
						}
						$code_wrap++;
					}

					$code_line += floor($code_wrap / 80) + 1;

					$code .= self::str_trans($line, false).PHP_EOL;
				}

				if ($is_php)
				{
					$code = '&lt;?php'.PHP_EOL.$code.'?&gt;';
					$code_line+=2;
				}
				
				if ($code_line > 30)
					$code_line = 30;

				$contents .= '<div id="editor_'.$code_id.'"'
					.' style="position: relative;'
					.' width: 765px;'
					.' height: '.$code_line.'px">'
					.trim($code)
					.'</div><p>&nbsp;</p>';

				$codes[] = array('id'=>'editor_'.$code_id++, 'mode'=>$mode);
				continue;
			}
			else if (substr($line, 0, 4) === '<h1>')
			{
				$contents .= '<div class="page-header"><h1 id="'.$code_id++.'">'
					.self::str_trans(substr($line, 4))
					.'</h1></div>';
			}
			else if (substr($line, 0, 4) === '<h3>')
			{
				$contents .= '<p><h3>'
					.self::str_trans(substr($line, 4))
					.'</h3></p>';
			}
			else
			{
				$line = self::str_trans($line);
				if ($font != '')
				{
					$line = '<span style="font-family:'.$font.';">'
						.$line
						.'</span>';
				}

				$contents .= '<p>'.$line.'</p>';
			}
		}

		if (!empty($codes))
		{
			$js_arr = array();
			foreach ($codes as $code)
			{
				$js_arr[] = '{"id":"'.$code['id'].'","mode":"'.$code['mode'].'"}';
			}
			$contents .= '<script>var CODE_DIVS=[';
			$contents .= implode(',', $js_arr);
			$contents .= '];</script>';
		}

		return $contents;
	}

	private static function str_trans($str, $nbsp = true)
	{
		$str = str_replace('&', '&amp;', $str);
		$str = str_replace('"', '&quot;', $str);
		$str = str_replace('<', '&lt;', $str);
		$str = str_replace('>', '&gt;', $str);
		if ($nbsp)
			$str = str_replace(' ', '&nbsp;', $str);
		return $str;
	}

	public static function getfilepath($file_path)
	{
		$file_path = trim($file_path);
		if ($file_path[0] != '/')
			$file_path = dirname(__FILE__).'/'.$file_path;
		$file_path = str_replace('/./', '/', $file_path);
		while (($current_pos = strpos($file_path, '/../')) !== false)
		{
			$current_path = substr($file_path, 0, $current_pos);
			$last_pos = strrpos($current_path, '/');
			if ($last_pos === false)
				return;
			$last_path = substr($file_path, 0, $last_pos+1);
			$extra_path = substr($file_path, $current_pos+strlen('/../'));
			$file_path = $last_path.$extra_path;
		}
		return $file_path;
	}

	/**
	 * return:
	 * 		0 : success
	 * 		-1: source file not exist
	 * 		-2: exchange error
	 * 		-3: mkdir /home/zeyu/Documents/images error
	 * 		-4: id not exist
	 * 		-5: insert file error
	 */
	public static function picture_insert($name, $category, $id=null)
	{
		$file = trim('/home/zeyu/Documents/images/'.$name);
		if (!file_exists($file))
			return -1;
		if (!empty($id))
		{
			$sql = 'select path from images where image_id='.$id;
			$info = MySqlOpt::select_query($sql);
			if (isset($info[0]['path']))
			{
				$path = $info[0]['path'];
				$blog_image = dirname(__FILE__).'/../html/'.$path;
				unlink($blog_image);

				$ret = copy($file, $blog_image);
				if ($ret == false)
					return -2;

				MySqlOpt::update(
					'images',
					array('md5'=>md5_file($blog_image),
					'category'=>$category),
					array('image_id'=>$id)
				);
			}
			else
				return -4;
		}
		else
		{
			$format = strrpos($file, '.');
			$format = substr($file, $format);
			$md5 = md5_file($file);
			$path = 'images/'.$md5.$format;
			$blog_image = WEB_PATH.'/resource/'.$path;

			$ret = copy($file, $blog_image);
			if ($ret == false)
				return -5;
			$id = MySqlOpt::insert('images',
				array('md5'=>md5_file($blog_image),
				'inserttime'=>'now()',
				'path'=>$path,
				'category'=>$category),
				true
			);
		}
		if (!is_dir('/home/zeyu/Documents/images'))
		{
			$ret = mkdir('/home/zeyu/Documents/images');
			if (!$ret)
				return -3;
		}
		rename($file, '/home/zeyu/Documents/'.$path);
		return $id;
	}

	public static function load_image ($path, $category='')
	{
		$file_path = self::getfilepath($path);
		if (!file_exists($file_path))
		{
			echo $file_path.PHP_EOL;
			return false;
		}
		$db_parrams = array();
		$db_parrams['md5'] = md5_file($file_path);
		$pos = strpos($file_path, '/html/');
		if ($pos === false)
			return false;
		$db_parrams['path'] = substr($file_path, $pos+strlen('/html/'));
		$db_parrams['category'] = $category;
		$ret = MySqlOpt::insert('images', $db_parrams, true);
		if ($ret == false)
		{
			LogOpt::set('exception', 'insert_into_images_error',
				MySqlOpt::errno(), MySqlOpt::error()
			);
		}
		else
		{
			LogOpt::set('info', 'insert_into_images_success',
				'image_id', $ret,
				'path', $path,
				'category', $category
			);
		}
		return $ret;
	}

	public static function get_index ($html_str)
	{
		$str = $html_str;
		$index = array();
		while (1)
		{
			$value = '';
			$key = StringOpt::spider_string($str, '<div', '</div>', $str);
			if ($key === null)
			{
				break;
			}
			else if ($key === false)
			{
				return false;
			}

			$key = StringOpt::spider_string($key, 'class="page-header"<![&&]>id="', '"', $value);
			if ($key === null)
				continue;

			$value = StringOpt::spider_string($value, '>', '<');
			if ($value === null)
			{
				continue;
			}
			else if ($value === false)
			{
				return false;
			}

			$index[$key] = $value;
		}
		return $index;
	}

	public static function get_tags ($article_id)
	{
		$sql = 'select C.* from article as A, article_tag_relation as B, tags as C where A.article_id = B.article_id and B.tag_id = C.tag_id and A.article_id = '.intval($article_id);
		$tag_infos = MySqlOpt::select_query($sql);
		return $tag_infos;
	}
}
?>
