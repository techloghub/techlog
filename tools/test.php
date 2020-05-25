<?php
$content = "1. go to some where
    1. go to the theatre
    2. go to the cinema/show
    3. go to the bank/post office
2. be getting ... -- 变化 e.g. The food is getting cold
    1. get 半联系动词，半系动词后可用名词、不定式、分词和形容词等作表语
    2. 表感官的系动词：look, sound, notice，taste, smell, feel（这些词用形容词作表语）
    3. 表似乎的系动词：seem, appear
    4. 表变化的系动词：become, get, turn, grow, make, come, go, fall, run
    5. 表依旧的系动词：remain, keep, stay, continue, stand, rest, lie, hold
    6. 可带名词作表语的系动词：become, make, look, sound, fall, prove, remain, turn（该词后接的单数名词前多不用冠词。如：He turned teacher.）
can't 用于口语 cannot 用于书面
3. hear & lisen to
    1. Do you hear me?
    2. He's not listening to me.
    3. eavesdrop vi.偷听
4. look at & see & watch
    1. look -- 强调动作，不及物
    2. see -- 强调结果，及物动词
    3. watch -- 观赏，及物动词，看的对象一定是会动的
    4. watch movie，look at the picture
    5. I looked at the young man and the young woman angrily.
    6. Did you see Sam?
    7. She's watching TV
5. pay attention to
    1. Please pay attention to that picture on the wall.
6. in the end = at last
7. 忍受
    1. can't bear/stand/endure it. -- 不能忍受（忍受的极限在加大）
    2. suffer -- 忍受痛苦
8. 交谈 have a ...
    1. talk
    2. conversation
    3. chat
    4. gasip -- 嚼舌头
9. theatre & cinema -- 剧院 & 电影院
10. 坐
    1. seat -- 名词、及物动词
    2. sit -- 不及物动词
        1. have a good seat
        2. take a seat -- 就坐
        3. Is this seat taken?
        4. Be seated please
        5. seat yourself
        6. seat him -- 给他找个座位
        7. he is sitting there
11. 生气
    1. angry
    2. cross = angry
    3. annoyed -- 恼火的
    4. be blue in the face -- 相当生气
12. 注意
    1. pay no attention to ...
    2. pay a little attention to ...
    3. pay close attention -- 特别注意
    3. pay more attention to ...
    4. notice -- 注意到，等同于 see
13. business
    1. businessman -- 生意人
    2. do business -- 做生意
    3. go to someplace on business -- 出差
    4. my business -- 私事
    5. none of your business
14. go to
    1. 除 go to school、go home、go to hospital、go to church 外 go to the ...
15. 位置
    1. behind -- 在后面
    2. in front of -- 在前面
    3. above -- 在上面
    4. before -- 在时间之前
    5. ahead of -- 在时间上提前，he goes ahead of me 位置上走在前面，与 in front of 不同，ahead of 表示动态的位置关系
16. private
    1. Let's have a conversation in private. -- 私下里
";

echo turn_markdown_to_techlog($content).PHP_EOL;

function turn_markdown_to_techlog($content) {
    $lines = explode("\n", $content);
    $result = '';
    $olnum = $ulnum = 0;
	$olpattern = "/^\d+\. (?<text>.*$)/i";
    $ulpattern = "/^\- (?<text>.*$)/i";
    $ulols = array();
	$olinfos = array();
    foreach ($lines as $line) {
        $line = str_replace("    ", "\t", $line);
        if (substr($line, 0, 2) == '# ') {
            $result .= '<h1>'.md_trans(substr($line, 2)).PHP_EOL;
        } else if (substr($line, 0, 3) == '## ') {
            $result .= '<h3>'.md_trans(substr($line, 3)).PHP_EOL;
        } else if (substr($line, 0, 4) == '### ') {
            $result .= '<h5>'.md_trans(substr($line, 4)).PHP_EOL;
        } else if (preg_match($ulpattern, trim($line), $olinfos) > 0) {
            for ($i = 0; $i < $ulnum; ++$i) {
                if ($i > strlen($line) || ($i != 0 && $line[$i-1] != "\t")) {
                    while ($ulnum > $i || $olnum > $i) {
                        $mark = array_pop($ulols);
                        if ($mark == 'ul') {
                            $result .= '</ul>' . PHP_EOL;
                            $ulnum--;
                        } else {
                            $result .= '</ol>' . PHP_EOL;
                            $olnum--;
                        }
                    }
                    break;
                }
            }
            $mark = array_pop($ulols);
            if ($mark == 'ol') {
                $result .= '</ol>' . PHP_EOL;
                $olnum--;
            } else if ($mark != null) {
                $ulols[] = 'ul';
            }
            while ($i == $ulnum && ($i == 0 || $line[$i-1] == "\t")) {
                $result .= '<ul>'.PHP_EOL;
                $ulnum++;
                $i++;
                $ulols[] = 'ul';
            }
            $result .= md_trans(trim($olinfos['text'])).PHP_EOL;
		} else if (preg_match($olpattern, trim($line), $olinfos) > 0) {
            for ($i = 0; $i < $olnum; ++$i) {
                if ($i > strlen($line) || ($i != 0 && $line[$i-1] != "\t")) {
                    while ($ulnum > $i || $olnum > $i) {
                        $mark = array_pop($ulols);
                        if ($mark == 'ul') {
                            $result .= '</ul>' . PHP_EOL;
                            $ulnum--;
                        } else {
                            $result .= '</ol>' . PHP_EOL;
                            $olnum--;
                        }
                    }
                    break;
                }
            }
            $mark = array_pop($ulols);
            if ($mark == 'ul') {
                $result .= '</ul>' . PHP_EOL;
                $ulnum--;
            } else if ($mark != null) {
                $ulols[] = $mark;
            }
            while ($i >= $olnum && ($i == 0 || $line[$i-1] == "\t")) {
                $result .= '<ol>'.PHP_EOL;
                $olnum++;
                $i++;
                $ulols[] = 'ol';
            }
            $result .= md_trans(trim($olinfos['text'])).PHP_EOL;
        } else {
            while ($olnum > 0 || $ulnum > 0) {
                $mark = array_pop($ulols);
                if ($mark == 'ul') {
                    $result .= '</ul>' . PHP_EOL;
                    $ulnum--;
                } else {
                    $result .= '</ol>' . PHP_EOL;
                    $olnum--;
                }
            }
            $result .= md_trans(trim($line)).PHP_EOL;
        }
    }
    while ($olnum > 0 || $ulnum > 0) {
        $mark = array_pop($ulols);
        if ($mark == 'ul') {
            $result .= '</ul>' . PHP_EOL;
            $ulnum--;
        } else {
            $result .= '</ol>' . PHP_EOL;
            $olnum--;
        }
    }
    return $result;
}

function md_trans($str) {
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
