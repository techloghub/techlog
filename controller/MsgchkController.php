<?php
class MsgchkController {
	public function loginActionAjax()
	{
		$config = file_get_contents(APP_PATH.'/config.json');
		$config = json_decode($config, true);

		$input = $_POST;

		$result = array('code'=>1, 'msg'=>'用户不存在');
		if (isset($input['username'])
			&& isset($input['password'])
			&& $input['username'] == $config['admin']['user']
			&& md5($input['password']) == $config['admin']['pwd']
		) {
			$result['code'] = 0;
			$result['msg'] = '登录成功';
			setcookie('LoginInfo', $config['admin']['logininfo'], time()+1800, '/');
		}

		return json_encode($result);
	}
}
?>
