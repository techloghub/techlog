<?php

use PHPMailer\PHPMailer\PHPMailer;

class MailController
{
    /**
     * @param array $query_params
     * @return array
     */
    public function listAction($query_params) {
        try {
            $subject = $_REQUEST['subject'];
            $to = $_REQUEST['to'];
            $content = $_REQUEST['content'];
            $ishtml = $_REQUEST['html'];

            $config = file_get_contents(CONF_PATH . '/config.json');
            $config = json_decode($config, true);
            $config = $config['mail'];

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['smtpsecure'];
            $mail->Port = $config['port'];

            $mail->setFrom($config['username'], $config['nickname']);
            $tolist = explode(',', $to);
            foreach ($tolist as $to) {
                $mail->addAddress($to);
            }
            $mail->isHTML(intval($ishtml) == 1);
            $mail->Subject = $subject;
            $mail->Body = $content;
            if (isset($_REQUEST['altcontent'])) {
                $mail->AltBody = $_REQUEST['altcontent'];
            }
            $mail->send();
            return array('code' => 0, 'msg' => '发送成功');
        } catch (Exception $e) {
            return array('code' => -1, 'msg' => '发送失败 - '.var_export($e, true));
        }
    }
}
