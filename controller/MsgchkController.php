<?php
class MsgchkController {
	public function loginActionAjax()
	{
		$config = file_get_contents(CONF_PATH.'/config.json');
		$config = json_decode($config, true);
		$authcode = RedisRepository::getAuthcode();

		$input = $_POST;

		$result = array('code'=>1, 'msg'=>'用户不存在');
		if (isset($input['username'])
			&& isset($input['password'])
			&& isset($input['authcode'])
			&& !empty($authcode)
			&& $input['username'] == $config['admin']['user']
			&& $input['authcode'] == $authcode
			&& md5($input['password']) == $config['admin']['pwd']
		) {
			$result['code'] = 0;
			$result['msg'] = '登录成功';
			setcookie('LoginInfo', $config['admin']['logininfo'], time()+1800, '/');
			setcookie('unick', base64_encode('博主'), time()+1800, '/');
			setcookie('uemad', base64_encode('zeyu203@qq.com'), time()+1800, '/');

			$config['authcode'] = '';
			RedisRepository::setAuthcode('');
		}

		return $result;
	}
}
?>
