<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php echo $params['title'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="baidu-site-verification" content="UF7rINUv36" />
    <meta name="applicable-device"content="pc,mobile">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">
	<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
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
		font-size: 2em;
	}
	h3 {
		color: #6800C9;
		font-size: 1em;
	}
	p, li, h1, h3 {
		word-wrap:break-word;
		margin-top: 1em;
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

							<div class="page-header">
								<h1 id="comment">添加评论</h1>
							</div>
							<div>
								<form>
									<p style="line-height:0.5em; margin-top:1.5em">QQ 号或 email 请至少填写一项，以便站长联系您，谢谢！</p>
									<p style="margin-bottom:1.5em; line-height:0.5em">本站不会公布任何隐私数据</p>
									<div class="input-group input-group-lg">
										<span class="input-group-addon">起个昵称</span>
										<input type="text" id="nickname" class="form-control" placeholder="昵称" aria-describedby="sizing-addon1">
									</div>
									<div class="input-group input-group-lg" style="margin-top: 1em">
										<span class="input-group-addon">QQ 号码</span>
										<input type="text" id="qq" class="form-control" placeholder="qq" aria-describedby="sizing-addon1">
									</div>
									<div class="input-group input-group-lg" style="margin-top: 1em">
										<span class="input-group-addon">电子邮箱</span>
										<input type="text" id="email" class="form-control" placeholder="email" aria-describedby="sizing-addon1">
									</div>
									<div class="input-group input-group-lg" style="margin-top: 1em">
										<span class="input-group-addon" id="email">评论内容</span>
										<input type="text" class="form-control" placeholder="评论内容" id="comment_content" aria-describedby="sizing-addon1">
									</div>
									<div class="input-group input-group-lg" style="margin-top: 1em">
										<input type="hidden" style="display:none" name="article_id" id="article_id" value="<?php echo $params['article_id'] ?>"/>
										<button type="button" onclick="mobile_comment()" id="submit_button" class="btn btn-primary btn-lg" style="float: right">提交</button>
									</div>
								</form>
							</div>

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
<script src="/resource/zeyu_blog/js/zeyu_blog.js" type="text/javascript" charset="utf-8"></script>
<script>
function isEmail(email) {                                                        
    var regu = "^(([0-9a-zA-Z]+)|([0-9a-zA-Z]+[_.0-9a-zA-Z-]*))@([a-zA-Z0-9-]+[.])+([a-zA-                           Z]{2}|net|com|gov|mil|org|edu|int|name|asia)$";
    var re = new RegExp(regu);                                                   
    return email.search(re) != -1;                                               
}

function mobile_comment() {
	var article_id = $('#article_id').val();
    var nickname = $('#nickname').val();
    var qq = $('#qq').val();
    var email = $('#email').val();
    var content = $('#comment_content').val();
	
	if (isNaN(article_id)) {
		$.alert({
			title: '提示',
			content: '评论功能正在维护',
		});
        return false;                                                            
    }                                                                            
    if (nickname == "") {
		$.alert({
			title: '提示',
			content: '请输入昵称',
		});
        return false;
    }
    if (qq != "" && isNaN(qq)) {
		$.alert({
			title: '提示',
			content: '请输入正确的QQ号',
		});
        return false;
    }                                                                            
    if (email != "" && !isEmail(email)) {
		$.alert({
			title: '提示',
			content: '请输入正确的 Email 地址',
		});
        return false;
	}                                                                            
    if (email == "" && qq == "") {
		$.alert({
			title: '提示',
			content: 'QQ 号码和 Email 地址请至少填写一个，谢谢',
		});
        return false;                                                           
    }                                                                            
    if (content == "") {
		$.alert({
			title: '提示',
			content: '请输入评论内容，谢谢！',
		});
        return false;		
    }                                                                            
    var params = {                                                               
        'qq'         : qq,                                                       
        'email'      : email,                                                    
        'content'    : content,                                                  
        'nickname'   : nickname,                                                 
        'article_id' : article_id,
        'replyfloor' : '',
		'reply'		 : 0
    }
	
	$('#submit_button').attr('disabled', 'disabled')
    $.ajax({                                                                        
		'url'   : '/article/comment',                                        
		'type'  : 'post',                                                    
		'data'  : params,                                                    
		'dataType'  : 'json',                                                
		'error' : function (jqXHR, textStatus, errorThrown) {                
			var errMsg = errorThrown == 'Forbidden' ? '没权限呢!' : '亲，服务器忙呢!';
			$.confirm({
				title: '提示',
				content: errorThrown,
				buttons: {
					confirm: {
						text: '确定',
						btnClass: 'btn-blue',
						action: function () {
							location.reload();
						}
					}
				}
			});
		},                                                                   
		'success' : function (data) {                                        
			if (data['code'] == 0) {
				$.confirm({
					title: '提示',
					content: data['msg'],
					buttons: {
						confirm: {
							text: '确定',
							btnClass: 'btn-blue',
							action: function () {
								location.reload();
							}
						}
					}
				});
			} else {
				$.alert({
					title: '提示',
					content: data['msg'],
				});
			}                                                                
		}                                                                    
	});     
}

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
