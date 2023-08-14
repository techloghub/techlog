<?php
require_once(__DIR__.'/../app/register.php');
\date_default_timezone_set('PRC');

$date = date('Y-m-d H:i:s');
$params = array('le' => array('next_time' => $date), 'ne' => array('status' => '2'));
$calendars = Repository::findFromCalendarAlert($params);

var_dump($calendars);

foreach ($calendars as $calendar) {
    if ($calendar->get_alert_time() < $calendar->get_next_time()) {
        $subject = '[日历提醒] '.$calendar->get_name();
        $content = '<p>有新的日历提醒了</p><p>内容：</p>'.'<p><blockquote>'.$calendar->get_name().'</blockquote></p>';
        if (!empty($calendar->get_remark())) {
            $content .= '<p>备注：</p><p><blockquote>' . $calendar->get_remark() . '</blockquote></p>';
        }
        CalendarAlertService::send_mail($subject, 'zeyu203@qq.com', $content);
    }
    CalendarAlertService::update_next_alert_time($calendar->get_id());
}
?>
