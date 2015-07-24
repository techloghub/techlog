<?php
class SqlRepository
{
	static function getArticleCountByTagId($tag_id)
	{
		$pdo = Repository::getInstance();
		$tag_id = intval($tag_id);
		$sql = 'select count(*) as count from'
			.' (select 1 from article_tag_relation'
			.' where tag_id = '.$tag_id.' group by article_id) as A';
		$stmt = $pdo->query($sql);
		$ret = $stmt->fetch();
		return isset($ret['count']) ? $ret['count'] : false;
	}

	static function getArticlesByTagId($tag_id, $range)
	{
		$pdo = Repository::getInstance();
		$sql = 'select article.*'
			.' from article, article_tag_relation, tags'
			.' where article.article_id = article_tag_relation.article_id'
			.' and tags.tag_id = article_tag_relation.tag_id'
			.' and tags.tag_id = '.intval($tag_id)
			.' order by inserttime desc'
			.' limit '.intval($range[0]).', '.intval($range[1]);

		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$ret = $stmt->fetchAll(PDO::FETCH_CLASS, 'ArticleModel');
		return $ret;
	}

	static function getRandTags()
	{
		$pdo = Repository::getInstance();
		$sql = 'select * from `tags`'
			.' where tag_id >= (select floor(max(tag_id) * rand()) from tags)'
			.' order by tag_id limit 20';

		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$ret = $stmt->fetchAll(PDO::FETCH_CLASS, 'TagsModel');
		return $ret;
	}
}
?>
