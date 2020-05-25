<?php
$content = '# 2. Original Text'.PHP_EOL;
$content .= 'Mrs. Anne Sterling did not ==think of== ==the risk she was taking== when she ==ran through== a ==forest== after two men. They had ==rushed up to== her while she was ==having a picnic== ==at the edge of a forest== with her children and tried to ==steal== her handbag. ==In the struggle==, the ==strap== broke and with the bag in their ==possession==, both men started running through the trees. Mrs. Sterling got ==so== angry ==that== she ran after them. She was soon ==out of breath==, but she continued to run. When she ==caught up with== them, she saw that they had sat down and were going through ==the contents of the bag==, so she ==ran straight at== them. The men ==got such a fright== that they dropped the bag and ran away. \'The strap needs ==mending==,\' said Mrs. Sterling later, \'but they did not steal anything.\''.PHP_EOL;
$content .= '## 3.2 Expressions'.PHP_EOL;
$content .=	'1. take the risk -- 冒险'.PHP_EOL;
$content .= '	1. take/run the risk of doing -- 冒险做某事'.PHP_EOL;
$content .= '- e.g. We\'ll ==take/run the risk of== setting out in such a weather. '.PHP_EOL;

echo turn_markdown_to_techlog($content).PHP_EOL;

function turn_markdown_to_techlog($content) {
    $lines = explode(PHP_EOL, $content);
    $result = '';
    $olnum = $ulnum = 0;
	$olpattern = "/^\d+\. (?<text>.*$)/i";
	$olinfos = array();
    foreach ($lines as $line) {
        if (substr($line, 0, 2) == '# ') {
            $result .= '<h1>'.md_trans(substr($line, 2)).PHP_EOL;
        } else if (substr($line, 0, 3) == '## ') {
            $result .= '<h3>'.md_trans(substr($line, 3)).PHP_EOL;
        } else if (substr($line, 0, 4) == '### ') {
            $result .= '<h5>'.md_trans(substr($line, 4)).PHP_EOL;
        } else if (substr(trim($line), 0, 2) == '- ') {
            for ($i = 0; $i < $ulnum; ++$i) {
                if ($i == strlen($line) || $line[$i] != "\t") {
                    while ($ulnum > $i) {
                        $result .= '</ul>'.PHP_EOL;
                        $ulnum--;
                    }
                    break;
                }
            }
            if ($i == $ulnum && $line[$i] == "\t") {
                $result .= '<ul>'.PHP_EOL;
				$ulnum++;
            }
            $result .= md_trans(trim($line)).PHP_EOL;
		} else if (preg_match($olpattern, trim($line), $olinfos) > 0) {
            for ($i = 0; $i < $olnum; ++$i) {
                if ($i == strlen($line) || $line[$i] != "\t") {
                    while ($olnum > $i) {
                        $result .= '</ol>'.PHP_EOL;
                        $olnum--;
                    }
                    break;
                }
            }
            if ($i == $olnum && $line[$i] == "\t") {
                $result .= '<ol>'.PHP_EOL;
				$olnum++;
            }
            $result .= md_trans(trim($olinfos['text'])).PHP_EOL;
        } else {
            $result .= md_trans(trim($line)).PHP_EOL;
        }
    }
    return $result;
}

function md_trans($str) {
	return $str;
}
