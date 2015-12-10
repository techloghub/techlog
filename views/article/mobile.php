<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php echo $params['title'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="baidu-site-verification" content="UF7rINUv36" />
    <meta name="applicable-device"content="pc,mobile">
	<script type="text/javascript" src="/resource/jquery/js/jquery-1.7.min.js"></script>
	<script type="text/javascript" src="/resource/jquery/js/jquery.alerts.js"></script>
	<script src="/resource/ace-master/build/src-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
    <link rel="icon" href="http://techlog.cn/images/icon.png">
	<link href="/resource/zeyu_blog/css/zeyu_blog.css" rel="stylesheet" type="text/css">
	<link href="/resource/zeyu_blog/css/mobile.css" rel="stylesheet" type="text/css">
    <style>
    .en-markup-crop-options {
        top: 18px !important;
        left: 50% !important;
        margin-left: -100px !important;
        width: 200px !important;
        border: 2px rgba(255,255,255,.38) solid !important;
        border-radius: 4px !important;
    }

    .en-markup-crop-options div div:first-of-type { margin-left: 0px !important;
    }

	h1[id] {
		color: #0047DD;
		margin-top: 2em;
		margin-bottom: 1em;
		font-size: 2em;
		border-bottom: 1px solid #eee;
	}
	h3 {
		color: #6800C9;
		font-size: 1em;
	}
	p, li, h1, h3 {
		word-wrap:break-word
	}
    </style>
</head>
<body>

<div class="pusher">
    <nav id="navbar" class="ui stripe">
        <div class="ui container inverted secondary menu">
			<a class="item useless" href="/">龙潭斋</a>
			<a class="item" href="/note">技术专题</a>
			<a class="item useless" href="/debin/category/1">专题解读</a>
			<a class="item" href="/debin/category/4">技术乱炖</a>
			<a class="item" style="height:37px" href="/debin/category/3">随笔轧志</a>
        </div>
    </nav>

    <div id="main">
        <div class="ui container stackable grid">
            <div class="articles eleven wide column">
                <div class="ui segment">
                    <div class="article">
					<div class="header"><h1 style="margin-top:40px; margin-bottom:40px; font-size:2em"><?php echo $params['title'] ?></h1></div>
					</div>
						<a class="ui red ribbon label" href="/category/downloads/" target="_blank"><i class="cloud download icon"></i><?php echo $params['inserttime'] ?></a>

                        <div class="article-content" style="margin-top:40px">
						<?php echo $params['contents'] ?>

                            <div class="ui divider"></div>
                            <div class="nearby">
								<?php foreach($params['tags'] as $tags) { ?>
									<a href="/debin/tag/<?php echo $tags['tag_id'] ?>" target="_blank"><?php echo $tags['tag_name'] ?></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="ui inverted vertical footer segment">
            <div class="ui container">
                <div class="ui stackable inverted divided equal height stackable grid">
                </div>
            </div>
        </div>
    </footer>
</div>
<script src="/resource/zeyu_blog/js/article.js"></script>
<script src="/resource/zeyu_blog/js/zeyu_blog.js" type="text/javascript" charset="utf-8"></script>
<script>
resize();
window.onresize = function() {
	resize();
}

function resize()
{
	var w = $('.article-content').width();
	if (w > $('.ui.container').width())
		w = $('.ui.container').width();
	if (typeof(CODE_DIVS) != 'undefined')
	{
		if (typeof(allId) == 'undefined')
			var allId = CODE_DIVS;
		for (var i in allId)
		{
			$('#'+allId[i]['id']).css('width', w);
		}
	}

	$('img').each(function() {
			if ($(this).width() >= w)
			{
				$(this).width(w);
			}
			else if ($(this).width() < $(this).parent().attr('alt'))
			{
				if ($(this).parent().attr('alt') > w)
					$(this).width(w);
				else
					$(this).width($(this).parent().attr('alt'));
			}
		}
	);
}
</script>

</body>
</html>
