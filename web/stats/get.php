<?php

// location MUST BE either a relative url to a file, relative to this
// script (get.php) OR an absolute url (this should be avoided).

// Usage: replace
// <a href="../pub/fat-recover-0.1.tar.gz">
// by
// <a href="../stats/get.php?location=../pub/fat-recover-0.1.tar.gz">

// DO NOT OUTPUT SOMETHING BEFORE header();

function remove_dotdot($path)
{
    while (is_integer($n = strpos($path,"/..")))
    {
        // extrat left & right part of the search string
        $gauche = substr($path, 0, $n);
        $droit  = substr($path, $n+strlen("/.."));

        // remove last / component, like "/stats"
        $gauche = substr($gauche, 0, - strlen(strrchr($gauche,"/")));

        $path = $gauche . $droit;
    }

    return $path;
}

if (isset($location))
{
    header("Location: $location");

    // Convert $_SERVER['PHP_SELF']
    $url = parse_url($_SERVER['PHP_SELF']);
    $path = dirname($url['path']) . "/" . $location;

    // change $_SERVER['PHP_SELF']
    $_SERVER['PHP_SELF'] = remove_dotdot($path);
}

require "stats.php";
?>
<html>
<head>
<title> Download error </title>
</head>

<body>
<p>

This script needs the <b>location</b> parameter in order to work.

</body>
</html>
