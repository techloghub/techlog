<?php
use Component\Library\LunarHelper;
use PHPMailer\PHPMailer\PHPMailer;

class CalendarAlertService {
    public static function update_next_alert_time($id) {
        $date = date('Y-m-d H:i:s');
        $entity = Repository::findOneFromCalendarAlert(
            array('eq' => array('id' => $id)));
        if (empty($entity)) {
            echo 'id is wrong'.PHP_EOL;
        }

        $entity->set_alert_time($date);
        $next_time = LunarHelper::getNextAlert($entity);
        if ($next_time == '1970-01-01 08:00:00' || $entity->get_status() == 0) {
            $entity->set_status(2);
        }
        $entity->set_next_time($next_time);
        $ret = Repository::persist($entity);
        if ($ret == false)
        {
            return '更新失败, id: '.$id;
        }
        return '更新成功';
    }

    public static function send_mail($subject, $to, $content, $ishtml = 0, $altcontent = '') {
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
        if (!empty($altcontent)) {
            $mail->AltBody = $altcontent;
        }
        $mail->send();
    }
}
?>
