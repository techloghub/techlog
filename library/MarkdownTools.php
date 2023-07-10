<?php
class MarkdownTools {
	public static function treat_article($content) {
		$lines = explode(PHP_EOL, $content);
		$contents = '';
		$h1_no = 0;
		$h3_no = 0;
		$h5_no = 0;
		$imgnu = 0;
		for ($index = 0; $index < count($lines); ++$index) {
			$line = $lines[$index];
			$line = trim($line);
			if (empty($line)) {
				$contents .= PHP_EOL;
			} else if ($line == '<div>') {
				$contents .= PHP_EOL.PHP_EOL
					."# 以下内容需要手动处理：".PHP_EOL;
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = $lines[$index];
					if (trim($line) === '</div>')
						break;
					$contents .= self::str_trans($line).PHP_EOL.PHP_EOL;
				}
			} else if (substr($line, 0, 6) == '<table') {
				$split = StringOpt::spider_string($line, 'split="', '"');
				if (empty($split) || $split == '\t') {
					$split = "\t";
				}
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
					if ($line == '</table>') {
						$contents .= PHP_EOL;
						break;
					} else if (substr($line, 0, 9) == '<caption>') {
						$caption = substr($line, 9);
						$contents .= '#### '.self::str_trans($caption, true).PHP_EOL.PHP_EOL;
					} else {
						$tds = explode($split, $line);
						if (substr($line, 0, 4) == '<tr>') {
							$tds[0] = substr($tds[0], 4);
							if ($tds[0][0] == '[' && $tds[0][strlen($tds[0])-1] == ']') {
								$tmptds = array();
								for ($i = 1; $i < count($tds); ++$i) {
									$tmptds[$i - 1] = $tds[$i];
								}
								$tds = $tmptds;
							}
						}
						$table_tds = array();
						foreach ($tds as $td) {
							$table_tds[] = self::str_trans($td, true);
						}
						$contents .= '|'.implode('|', $table_tds).'|'.PHP_EOL;
						if (substr($line, 0, 4) == '<tr>') {
							$contents .= '|';
							for ($i = 0; $i < count($tds); ++$i) {
								$contents .= '--|';
							}
							$contents .= PHP_EOL;
						}
					}
				}
			} else if ($line == '<ol>') {
				$idx = 1;
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
					if ($line == '</ol>' || $line == '</ul>') {
						$contents .= PHP_EOL;
						break;
					}
					$contents .= $idx++.'. '.self::str_trans($line).PHP_EOL;
				}
			} else if ($line == '<ul>') {
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
					if ($line == '</ol>' || $line == '</ul>') {
						$contents .= PHP_EOL;
						break;
					}
					$contents .= '- '.self::str_trans($line).PHP_EOL;
				}
			} else if ($line == '<block>' || $line == '<bl>') {
				while (1) {
					$index++;
					if ($index >= count($lines))
						break;
					$line = trim($lines[$index]);
					if ($line == '</block>' || $line == '</bl>') {
						break;
					}
					$contents .= '> '.self::str_trans($line).PHP_EOL;
				}
			} else if (substr($line, 0, 4) == '<img') {
				$imgnu++;
				$contents .= PHP_EOL.PHP_EOL.'# 此处有图片 '.$imgnu.PHP_EOL.PHP_EOL;
			} else if (substr($line, 0, 5) == '<code') {
				$this_mode = StringOpt::spider_string($line, 'mode="', '"');
				if (!empty($this_mode)) {
					$mode = $this_mode;
				} if (empty($mode)) {
					$mode = 'c_cpp';
				}

				$mode = self::techmode_to_md($mode);
				$contents .= '```'.$mode.PHP_EOL;
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
					$line = trim($line, "\r");
					$line = trim($line, "\n");
					$contents .= $line.'  '.PHP_EOL;
				}
			} else if (substr($line, 0, 4) === '<h1>') {
				$h1_no++;
				$h3_no = 0;
				$contents .= '# '.$h1_no.". ".self::str_trans(substr($line, 4)).PHP_EOL.PHP_EOL;
			} else if (substr($line, 0, 4) === '<h3>') {
				$h3_no++;
                $h5_no = 0;
				$contents .= '## '.$h1_no.".".$h3_no.". ".self::str_trans(substr($line, 4)).PHP_EOL.PHP_EOL;
			} else if (substr($line, 0, 4) === '<h5>') {
				$h5_no++;
				$contents .= '### '.$h1_no.".".$h3_no.".".$h5_no.". ".self::str_trans(substr($line, 4)).PHP_EOL.PHP_EOL;
			} else if (substr($line, 0, 3) == '<a ') {
				$id = StringOpt::spider_string($line, 'id="', '"');
				$title = Repository::findTitleFromArticle(
					array('eq' => array('article_id' => $id))
				);
				$contents .= '['.self::str_trans($title).'](http://techlog.cn/article/list/'.$id.')'.PHP_EOL;
			} else {
				$pattern = '/[[:punct:]]/';
				$lastchar = mb_substr($line, -1, 1, 'UTF-8');
				if (strlen($lastchar) > 1) {
					$pattern = '/(：)|(？)|(（)|(）)|(，)|(。)|(、)|(！)|(；)|(·)/';
				}
				if (preg_match($pattern, $lastchar) == 0) {
					$line = $line.'。';
				}
				$contents .= self::str_trans($line).PHP_EOL.PHP_EOL;
			}
		}
		return $contents;
	}

    public static function turn_markdown_to_techlog($content) {
        $lines = explode("\n", $content);
        $result = '';
        $olpattern = "/^(?<num>\d+)\. (?<text>.*$)/i";
        $ulpattern = "/^\- (?<text>.*$)/i";
        $lastolulnum = -1;
        $ulols = array();
        $olinfos = array();
        for ($i = 0; $i < sizeof($lines); ++$i) {
            $line = str_replace("    ", "\t", $lines[$i]);

            if (preg_match($ulpattern, trim($line), $olinfos) > 0) {
                $result .= self::treat_olul($line, '<ul>', $lastolulnum, $olinfos, $ulols);
            } else if (preg_match($olpattern, trim($line), $olinfos) > 0) {
                $result .= self::treat_olul($line, '<ol>', $lastolulnum, $olinfos, $ulols);
			} else if (substr($line, 0, 3) == '```') {
				$result .= '<code mode="'.self::mdmode_to_techlog(trim($line)).'">'.PHP_EOL;
				while (trim($lines[$i+1]) != '```') {
					$i++;
					$line = str_replace("    ", "\t", $lines[$i]);
					$result .= $line.PHP_EOL;
				}
				$result .= '</code>'.PHP_EOL;
				$i++;
			} else if (!empty($line) && $line[0] == '|') {
				$result .= '<table split="|">'.PHP_EOL;
				$hastr = false;
				while (!empty($lines[$i]) && $lines[$i][0] == '|') {
					$line = trim($lines[$i]);
					$line = trim($line, '|');
					if (substr($line, 0, 2) == '--') {
						$i++;
						continue;
					}
					if (!$hastr) {
						$result .= '<tr>';
						$hastr = true;
					}
					$result .= $line.PHP_EOL;
					$i++;
				}
				$result .= '</table>'.PHP_EOL;
			} else if (!empty($line) && substr($line, 0, 2) == '> ') {
				$result .= '<bl>'.PHP_EOL;
				while (!empty($line) && $line[0] === '>') {
					$result .= ltrim($line, '> ').PHP_EOL;
					$i++;
					$line = str_replace("    ", "\t", $lines[$i]);
				}
				$result .= '</bl>'.PHP_EOL;
				$i--;
			} else {
                $result .= self::close_olul($ulols, $lastolulnum);

                if (substr($line, 0, 2) == '# ') {
                    $result .= '<h1>'.self::md_trans(substr($line, 2)).PHP_EOL;
                } else if (substr($line, 0, 3) == '## ') {
                    $result .= '<h3>'.self::md_trans(substr($line, 3)).PHP_EOL;
                } else if (substr($line, 0, 4) == '### ') {
                    $result .= '<h5>'.self::md_trans(substr($line, 4)).PHP_EOL;
                } else {
					$result .= self::md_trans($line).PHP_EOL;
				}
            }
        }
        $result .= self::close_olul($ulols, $lastolulnum);
        return $result;
    }

    private static function treat_olul($line, $isul, &$lastolulnum, $olinfos, &$ulols) {
        $result = '';
        // find first index which is not tab
        for ($oli = 0; $oli < strlen($line); ++$oli) {
            if ($line[$oli] != "\t") {
                break;
            }
        }
        $temp_lastnum = $oli;
        $need_label = false;
        if ($oli > $lastolulnum) {
            // Increased indentation
            while ($oli > $lastolulnum) {
                $result .= $isul.PHP_EOL;
                $ulols[] = $isul;
                $oli--;
            }
        } else if ($oli < $lastolulnum) {
            // Reduced indentation
            while ($oli < $lastolulnum && !empty($ulols)) {
                // close last label
                $mark = array_pop($ulols);
                $result .= $mark == '<ol>' ? '</ol>' : '</ul>';
                $result .= PHP_EOL;
                $oli++;
            }
            if (empty($ulols) || $ulols[sizeof($ulols) - 1] != $isul) {
                $need_label = true;
            }
        } else if (empty($ulols) || $ulols[sizeof($ulols) - 1] != $isul) {
            if (!empty($ulols)) {
                array_pop($ulols);
                $result .= $isul == '<ul>' ? '</ol>' : '</ul>';
                $result .= PHP_EOL;
            }
            $need_label = true;
        }
        if ($need_label) {
            $result .= $isul.PHP_EOL;
            $ulols[] = $isul;
            if ($isul == '<ol>' && intval($olinfos['num']) > 1) {
                $result .= '<li value="'.intval($olinfos['num']).'">';
            }
        }
        $result .= self::md_trans(trim($olinfos['text'])).PHP_EOL;
        $lastolulnum = $temp_lastnum;
        return $result;
    }

    private static function close_olul(&$ulols, &$lastolulnum) {
        $result = '';
        while (sizeof($ulols) > 0) {
            $mark = array_pop($ulols);
            $result .= $mark == '<ol>' ? '</ol>' : '</ul>';
            $result .= PHP_EOL;
        }
        $lastolulnum = 0;
        return $result;
    }

    private static function md_trans($str) {
		$str = trim($str);
        $marks = array(
            '`' => array('<c>', '</c>', false),
            '==' => array('<mark>', '</mark>', false),
            '~~' => array('<s>', '</s>', false),
        );
        for ($i = 0; $i < strlen($str); $i++) {
            foreach ($marks as $key => $value) {
                if ($str[$i] == $key[0]) {
                    if (substr($str, $i, strlen($key)) == $key) {
                        if ($value[2]) {
                            $str = substr($str, 0, $i).$value[1].substr($str, $i+strlen($key));
                            $i += strlen($value[1]) - 1;
                        } else {
                            $str = substr($str, 0, $i).$value[0].substr($str, $i+strlen($key));
                            $i += strlen($value[0]) - 1;
                        }
                        $marks[$key][2] = !$value[2];
                        break;
                    }
                }
            }
        }
        return $str;
    }

	private static function mdmode_to_techlog($str) {
		$marks = array(
			'```cpp' => 'c_cpp',
			'```bash' => 'sh',
			'```x86asm' => 'asm'
		);
		return isset($marks[$str]) ? $marks[$str] : ltrim('`', $str);
	}

	private static function techmode_to_md($str) {
		$marks = array(
			'c_cpp' => 'cpp',
			'sh' => 'bash',
			'asm' => 'x86asm'
		);
		return isset($marks[$str]) ? $marks[$str] : $str;
	}

	private static function str_trans($str, $intable = false) {
		if (empty($str)) {
			return null;
		}
		$marks = array(
			'<c>' => '`',
			'</c>' => '`',
			'<s>' => '~~',
			'</s>' => '~~',
			'<mark>' => '==',
			'</mark>' => '==',
			'*' => '\*',
			'<' => '\<',
			'>' => '\>',
			'$' => '\$',
			'_' => '\_',
			'[' => '\[',
			']' => '\]',
		);

		if ($intable) {
			$marks['|'] = '&#124;';
			$marks['&nbsp;'] = ' ';
			$marks['&bull;'] = '·';
		} else {
			$marks['&'] = '\&';
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
}
