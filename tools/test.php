<?php
$content = "    1. go to some where
- go to the theatre
- 123
2. go to the cinema/show

1. go to
";

echo turn_markdown_to_techlog($content).PHP_EOL;

function turn_markdown_to_techlog($content) {
    $lines = explode("\n", $content);
    $result = '';
	$olpattern = "/^(?<num>\d+)\. (?<text>.*$)/i";
    $ulpattern = "/^\- (?<text>.*$)/i";
    $lastolulnum = -1;
    $ulols = array();
	$olinfos = array();
    foreach ($lines as $line) {
        $line = str_replace("    ", "\t", $line);

        if (preg_match($ulpattern, trim($line), $olinfos) > 0) {
            $result .= treat_olul($line, '<ul>', $lastolulnum, $olinfos, $ulols);
        } else if (preg_match($olpattern, trim($line), $olinfos) > 0) {
            $result .= treat_olul($line, '<ol>', $lastolulnum, $olinfos, $ulols);
        } else {
            $result .= close_olul($ulols, $lastolulnum);

            if (substr($line, 0, 2) == '# ') {
                $result .= '<h1>'.md_trans(substr($line, 2)).PHP_EOL;
            } else if (substr($line, 0, 3) == '## ') {
                $result .= '<h3>'.md_trans(substr($line, 3)).PHP_EOL;
            } else if (substr($line, 0, 4) == '### ') {
                $result .= '<h5>'.md_trans(substr($line, 4)).PHP_EOL;
            }
        }
    }
    $result .= close_olul($ulols, $lastolulnum);
    return $result;
}

function close_olul(&$ulols, &$lastolulnum) {
    $result = '';
    while (sizeof($ulols) > 0) {
        $mark = array_pop($ulols);
        $result .= $mark == '<ol>' ? '</ol>' : '</ul>';
        $result .= PHP_EOL;
    }
    $lastolulnum = 0;
    return $result;
}

function treat_olul($line, $isul, &$lastolulnum, $olinfos, &$ulols) {
    $result = '';
    // find first index which is not tab
    for ($i = 0; $i < strlen($line); ++$i) {
        if ($line[$i] != "\t") {
            break;
        }
    }
    $temp_lastnum = $i;
    $need_label = false;
    if ($i > $lastolulnum) {
        // Increased indentation
        while ($i > $lastolulnum) {
            $result .= $isul.PHP_EOL;
            $ulols[] = $isul;
            $i--;
        }
    } else if ($i < $lastolulnum) {
        // Reduced indentation
        while ($i <= $lastolulnum && !empty($ulols)) {
            // close last label
            $mark = array_pop($ulols);
            $result .= $mark == '<ol>' ? '</ol>' : '</ul>';
            $result .= PHP_EOL;
            $i++;
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
    $result .= md_trans(trim($olinfos['text'])).PHP_EOL;
    $lastolulnum = $temp_lastnum;
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
