<?php /** @noinspection CssInvalidPropertyValue */

class TechlogTools {
	public static function pre_treat_article ($file) {
		$font = '';
		$lines = explode(PHP_EOL, $file);
		$contents = '';
		$code_id = 'a';
		$codes = array();
		$inh1 = false;
		for ($index = 0; $index < count($lines); ++$index) {
			$line = $lines[$index];
			$line = trim($line);
			if (empty($line)) {
				$contents .= '<p>&nbsp;</p>';
			} else if ($line == '<div>') {
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
					if ($line == '</div>')
						break;
					$contents .= $line.PHP_EOL;
				}
			} else if (substr($line, 0, 6) == '<table') {
				$split = StringOpt::spider_string($line, 'split="', '"');
				if (empty($split) || $split == '\t') {
					$split = "\t";
				}
				$contents .=
					'<table class="stdtable" border="1" style="font-size:18px;">';
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
					if ($line == '</table>') {
						$contents .= $line;
						break;
					} else if (substr($line, 0, 9) == '<caption>') {
						$caption = substr($line, 9);
						$contents .=
							'<caption'
							.' style=\'font-weight:bold;'
							.' font-family:"PT Serif","Georgia","Helvetica Neue",Arial,sans-serif;'
							.' background-color:#D2E1F0; height:30px;\'>'
							.$caption
							.'</caption>';
					} else {
						$tds = explode($split, $line);
						if (substr($line, 0, 4) == '<tr>') {
							$tds[0] = substr($tds[0], 4);
							$contents .= '<thead>'
								.'<tr style="background-color:#C5C5C5;">';
							if ($tds[0][0] == '[' && $tds[0][strlen($tds[0])-1] == ']') {
								$widths = substr($tds[0], 1, strlen($tds[0])-2);
								$widths = explode(":", $widths);
								for ($i=1; $i<count($tds); ++$i) {
									if (isset($widths[$i-1]))
										$contents .= '<td width="'.$widths[$i-1].'%"><strong>'
											.$tds[$i].'</strong></td>';
									else
										$contents .= '<td><strong>'.$tds[$i].'</strong></td>';
								}
							} else {
								$contents .= '<td><strong>'
									.implode('</strong></td><td><strong>', $tds)
									.'</strong></td>';
							}
							$contents .= '</tr></thead>';
						} else {
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
			} else if ($line == '<ol>' || $line == '<ul>') {
                $olnum = 1;
                begin_olul:
				$contents .= substr($line, 0, 3)
					.' class="article_'.substr($line, 1, 2).'">';
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
                    if ($line == '<ol>' || $line == '<ul>') {
                        $olnum++;
                        goto begin_olul;
                    }
					if ($line == '</ol>' || $line == '</ul>') {
						$contents .= $line;
                        $olnum--;
                        if ($olnum == 0) {
                            break;
                        }
					} else {
						if ($font != '')
						{
							$line = '<span style="font-family:'.$font.';">'
								.$line
								.'</span>';
						}
						$infostr = '';
						if (substr($line, 0, 3) == '<li') {
							$infostr = StringOpt::spider_string($line, '<li', '>', $line);
						}
						$contents .= '<p><li class="article_li"'.$infostr.'>'.self::str_trans($line).'</li></p>';
					}
				}
			} else if ($line == '<block>' || $line == '<bl>') {
				$contents .= '<blockquote class="article_block">';
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = $lines[$index];
					if ($line == '</block>' || $line == '</bl>') {
						$contents .= '</blockquote>';
						break;
					}
					else {
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
			} else if (substr($line, 0, 5) == '<font') {
				$font = StringOpt::spider_string($line, '<font ', '>');
			} else if ($line == '</font>') {
				$font = '';
			} else if (substr($line, 0, 4) == '<img') {
				$error = false;
				$id = StringOpt::spider_string($line, 'id="', '"');
				if ($id === 'rqcode') {
					$id = '3378171';
					$line = '<img id="3378171"/>';
				}
				if ($id != null) {
					$image_id = intval(trim($id));
					$image = Repository::findOneFromImages(
						array('eq' => array('image_id' => $image_id))
					);
					if ($image != false)
					{
						$path = $image->get_path();
						$src = $image->get_path().'?id='.$image_id
							.'&v='.$image->get_version();
						$line =
							str_replace(
								'id="'.$id.'"',
								'src="'.$src.'"',
								$line
							);
					}
					else {
						$line = '<strong>图片ID不存在</strong>';
						$error = true;
					}
				} else {
					$path = StringOpt::spider_string($line, 'src="', '"');
				}
				if (!$error) {
					$image_info = GetImageSize(WEB_PATH.'/resource/'.$path);
					$width = intval($image_info[0]);
					$contents .= '<p style="text-indent:0em;">'
						.'<a target="_blank" alt="'.$path.'" href="'.$path.'">'
						.$line.'</a></p><p>&nbsp;</p>';
				}
			}
			else if (substr($line, 0, 5) == '<code')
			{
				$this_mode = StringOpt::spider_string($line, 'mode="', '"');
				if (empty($mode) && empty($this_mode)) {
					$mode = 'c_cpp';
				} else if (!empty($this_mode)) {
					$mode = $this_mode;
				}
                if ($this_mode == 'asm') {
                    $mode = 'assembly_x86';
                }
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

				$contents .= '<div id="editor_'.$code_id.'"'
					.' style="position: relative;'
					.' width: 765px;'
					.' height: '.$code_line.'px">'
					.trim($code)
					.'</div><p>&nbsp;</p>';
				$codes[] = array('id'=>'editor_'.$code_id++, 'mode' => $mode);
				continue;
			} else if (substr($line, 0, 4) === '<h1>') {
				if ($inh1) {
					$contents .= '</div>';
					$inh1 = false;
				}
				$contents .= '<div class="page-header"><h1 id="'.$code_id++.'">'
					.self::str_trans(substr($line, 4))
					.'</h1></div>';
				$inh1 = true;
				$contents .= '<div id="'.$code_id.'_contents" class="blog_content">';
			} else if (substr($line, 0, 4) === '<h3>') {
				$contents .= '<p><h3>'
					.self::str_trans(substr($line, 4))
					.'</h3></p>';
			} else if (substr($line, 0, 4) === '<h5>') {
				$contents .= '<p><h5 class="techlog_third_title">'
					.self::str_trans(substr($line, 4))
					.'</h5></p>';
			} else if (substr($line, 0, 3) == '<a ') {
				$id = StringOpt::spider_string($line, 'id="', '"');
				$title = Repository::findTitleFromArticle(
					array('eq' => array('article_id' => $id))
				);
				if (!$title)
					$title = 'ERROR：加载失败';
				$contents .= '<p><a target="_blank" href="/article/list/'.$id.'">'
					.$title.'</a></p>';
			} else {
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
		if (!empty($codes)) {
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

    public static function str_trans($str, $nbsp = true)
    {
        $marks = array(
            "<c>" => "<code>",
            "</c>" => "</code>",
            "<mark>" => "<mark>",
            "</mark>" => "</mark>",
            "<s>" => "<s>",
            "</s>" => "</s>",
            "&" => "&amp;",
            "\"" => "&quot;",
            "<" => "&lt;",
            ">" => "&gt;",
        );
        if ($nbsp) {
            $marks[' '] = '&nbsp;';
        }
        for ($i = 0; $i < strlen($str); $i++) {
            foreach ($marks as $key => $value) {
                if ($str[$i] == $key[0]) {
                    if (substr($str, $i, strlen($key)) == $key) {
                        $str = substr($str, 0, $i).$value.substr($str, $i+strlen($key));
                        $i += strlen($value) - 1;
                        break;
                    }
                }
            }
        }

        return $str;
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
			$image = Repository::findOneFromImages(
				array('eq' => array('image_id' => $id))
			);
			if ($image != false)
			{
				$path = $image->get_path();
				$blog_image = WEB_PATH.'/resource/'.$path;
				unlink($blog_image);
				$ret = copy($file, $blog_image);
				if ($ret == false)
					return -2;
				$image->set_md5(md5_file($blog_image));
				$image->set_category($category);
				$image->set_inserttime(date('Y-m-d H:i:s', time()));
				$image->set_version($image->get_version() + 1);
				$id = Repository::persist($image);
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
			$image = new ImagesModel(
				array(
					'md5'	=> md5_file($blog_image),
					'path'	=> $path,
					'version'	=> 1,
					'category'	=> $category,
					'inserttime' => 'now()'
				)
			);
			$id = Repository::persist($image);
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
		$pos = strpos($file_path, '/html/');
		if ($pos === false)
			return false;
		$image = new ImagesModel(
			array(
				'md5' => md5_file($file_path),
				'path' => substr($file_path, $pos+strlen('/html/')),
				'category' => $category,
				'inserttime' => 'now()'
			)
		);
		$id = Repository::persist($image);
		return $id;
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

	public static function isMobile()
	{
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
		{
			return true;
		}
		// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset ($_SERVER['HTTP_VIA']))
		{
			// 找不到为flase,否则为true
			return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
		}
		// 脑残法，判断手机发送的客户端标志,兼容性有待提高
		if (isset ($_SERVER['HTTP_USER_AGENT']))
		{
			$clientkeywords = array ('nokia',
				'sony',
				'ericsson',
				'mot',
				'samsung',
				'htc',
				'sgh',
				'lg',
				'sharp',
				'sie-',
				'philips',
				'panasonic',
				'alcatel',
				'lenovo',
				'iphone',
				'ipod',
				'blackberry',
				'meizu',
				'android',
				'netfront',
				'symbian',
				'ucweb',
				'windowsce',
				'palm',
				'operamini',
				'operamobi',
				'openwave',
				'nexusone',
				'cldc',
				'midp',
				'wap',
				'mobile'
			);
			// 从HTTP_USER_AGENT中查找手机浏览器的关键字
			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
			{
				return true;
			}
		}
		// 协议法，因为有可能不准确，放到最后判断
		if (isset ($_SERVER['HTTP_ACCEPT']))
		{
			// 如果只支持wml并且不支持html那一定是移动设备
			// 如果支持wml和html但是wml在html之前则是移动设备
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false)
				&& (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false
				|| (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
			{
				return true;
			}
		}
		return false;
	}
}
?>
