<?php
//$content = '# 2. Original Text'.PHP_EOL;
//$content .= 'Mrs. Anne Sterling did not ==think of== ==the risk she was taking== when she ==ran through== a ==forest== after two men. They had ==rushed up to== her while she was ==having a picnic== ==at the edge of a forest== with her children and tried to ==steal== her handbag. ==In the struggle==, the ==strap== broke and with the bag in their ==possession==, both men started running through the trees. Mrs. Sterling got ==so== angry ==that== she ran after them. She was soon ==out of breath==, but she continued to run. When she ==caught up with== them, she saw that they had sat down and were going through ==the contents of the bag==, so she ==ran straight at== them. The men ==got such a fright== that they dropped the bag and ran away. \'The strap needs ==mending==,\' said Mrs. Sterling later, \'but they did not steal anything.\''.PHP_EOL;
//$content .= '## 3.2 Expressions'.PHP_EOL;
$content = '- e.g. We\'ll ==take/run the risk of== setting out in such a weather. ';

echo turn_markdown_to_techlog($content).PHP_EOL;

function turn_markdown_to_techlog($content) {
    $lines = explode(PHP_EOL, $content);
    $result = '';
    $olnum = $ulnum = 0;
	$olpattern = "/^\d+\. (?<text>.*$)/i";
    $ulpattern = "/^\- (?<text>.*$)/i";
    $ulols = array();
	$olinfos = array();
    foreach ($lines as $line) {
        if (substr($line, 0, 2) == '# ') {
            $result .= '<h1>'.md_trans(substr($line, 2)).PHP_EOL;
        } else if (substr($line, 0, 3) == '## ') {
            $result .= '<h3>'.md_trans(substr($line, 3)).PHP_EOL;
        } else if (substr($line, 0, 4) == '### ') {
            $result .= '<h5>'.md_trans(substr($line, 4)).PHP_EOL;
        } else if (preg_match($ulpattern, trim($line), $olinfos) > 0) {
            for ($i = 0; $i < $ulnum; ++$i) {
                if ($i == strlen($line) || ($line[$i] != "\t" && $i != 0)) {
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
            } else {
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
                if ($i == strlen($line) || ($line[$i] != "\t" && $i != 0)) {
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
            } else {
                $ulols[] = 'ol';
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
