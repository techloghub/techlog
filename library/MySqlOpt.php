<?php
class MySqlOptV2
{
	private $pdo = null;
	private function __construct() { }

	public static function getInstance()
	{
		if (empty($this->pdo))
		{
			$conf = file_get_contents('/etc/zeyu203/techlog.conf');
			$conf = unserialize(base64_decode($conf));
			$conf = $conf['database'];

			$this->pdo = new PDO(
				'mysql:host='.$conf['host'].';dbname='.$conf['db'],
				$conf['user'],
				$conf['pwd']
				array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)
			);
		}
		return $this;
	}

	public static function query($sql, $params = array())
	{
		if (empty($params))
			$params = array();
		
		$obj = $dbh->prepare($sql);
		foreach ($params as $key=>$value)
			$obj->bindParam(':'.$key, $value);
		$obj->execute();
		return $obj;
	}
}
?>
