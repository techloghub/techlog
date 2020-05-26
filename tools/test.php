<?php
$content = "1. go to some where
    - go to the theatre
2. go to the cinema/show
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
            while (true) {
                $mark = array_pop($ulols);
                if ($mark == 'ol') {
                    $result .= '</ol>' . PHP_EOL;
                    $olnum--;
                } else if ($mark != null) {
                    $ulols[] = 'ul';
                    break;
                } else {
                    break;
                }
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

            while (true) {
                $mark = array_pop($ulols);
                if ($mark == 'ul') {
                    $result .= '</ul>' . PHP_EOL;
                    $ulnum--;
                } else if ($mark != null) {
                    $ulols[] = $mark;
                    break;
                } else {
                    break;
                }
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
