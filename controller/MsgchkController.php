<?php
class MsgchkController {
	public function loginActionAjax()
	{
		$conf = file_get_contents('/etc/zeyu203/techlog.conf');
		$conf = unserialize(base64_decode($conf));

		$input = $_POST;

		$result = array('code'=>1, 'msg'=>'用户不存在');
		if (isset($input['username'])
			&& isset($input['password'])
			&& $input['username'] == $conf['admin']['user']
			&& md5($input['password']) == $conf['admin']['pwd']
		) {
			$result['code'] = 0;
			$result['msg'] = '登录成功';
			setcookie('LoginInfo', $conf['admin']['logininfo'], time()+1800, '/');
		}

		echo json_encode($result);
	}
}
?>
