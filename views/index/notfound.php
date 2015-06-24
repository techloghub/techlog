<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script type="text/javascript" src="/resource/bootstrap/js/jquery.js"></script>
	<script type="text/javascript" src="/resource/jquery/js/jquery.alerts.js"></script>
	<script type="text/javascript" src="/resource/bootstrap/js/bootstrap.min.js"></script>
	<link href="/resource/bootstrap/css/docs.css" rel="stylesheet" type="text/css">
	<link href="/resource/bootstrap/css/bootstrap-responsive.css" rel="stylesheet" type="text/css">
	<link href="/resource/bootstrap/css/bootstrap-theme.css" rel="stylesheet" type="text/css">
	<link href="/resource/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href="/resource/bootstrap/css/prettify.css" rel="stylesheet" type="text/css">
	<link href="/resource/bootstrap/css/github.min.css" rel="stylesheet" type="text/css">
	<link href="/resource/jquery/css/jquery.alerts.css" rel="stylesheet" type="text/css">
	<link href="/resource/jquery/css/jquery.ui.css" rel="stylesheet" type="text/css">
	<link rel="shortcut icon" href="images/icon.png">
<title>龙潭斋</title>
<body>
<script language="javascript" type="text/javascript">
jAlert
(
	'<?php echo $params['msg'] ?>',
	'提示',
	function()
	{
		window.location.href="<?php echo isset($params['url']) ? $params['url'] : '/' ?>";
	}
);
</script>
</body>
</html>
