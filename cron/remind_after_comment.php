<?php
require_once(__DIR__.'/../app/register.php');

$params = array('eq' => array('reminded' => 0));
$comments = Repository::findFromComment($params);

foreach ($comments as $comment) {
    $tolist = array();
    if ($comment->get_nickname() != '博主') {
        $tolist[] = 'zeyu203@qq.com';
    }

    $name = '博主';
    if ($comment->get_reply() != 0) {
        $base_comment = Repository::findOneFromComment(
            array('eq' => array('article_id' => $comment->get_article_id(), 'floor' => $comment->get_reply())));
        if ($base_comment->get_nickname() != '博主') {
            if (!empty($base_comment->get_email())) {
                $tolist[] = $base_comment->get_email();
            } else if (!empty($base_comment->get_qq())) {
                $tolist[] = $base_comment->get_qq().'@qq.com';
            }
            $name = $base_comment->get_nickname();
        }
        $subject = '有人回复了您在 [techlog.cn] 上的评论';
        $content = '<p>'.$name.' 您好：</p><p>'.$comment->get_nickname()
            .' 回复了您在文章 《'.$article->get_title().'》 中的评论</p>'
            .'<p><a href="https://techlog.cn/article/list/'.$article->get_article_id().'">【点击查看详情】</a></p>';
    } else {
        $subject = '[techlog.cn] 新增了评论';
        $content = '<p>'.$name.' 您好：</p><p>'.$comment->get_nickname()
            .' 评论了您的文章 《'.$article->get_title().'》 </p>'
            .'<p><a href="https://techlog.cn/article/list/'.$article->get_article_id().'">【点击查看详情】</a></p>';
    }

    $article = Repository::findOneFromArticle(array('eq' => array('article_id' => $comment->get_article_id())));
    file_get_contents('https://techlog.cn/mail/list'
        .'?html=1&subject='.$subject.'&content='.$content.'&to='.implode(',', $tolist));
    $comment->set_reminded(1);
    Repository::persist($comment);
}