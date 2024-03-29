<?php
ini_set('display_errors','On'); // error_reporting(E_ALL);
/***************************************************************
*  Copyright notice
*
*  (c) 2011-2013 Johannes Binder <j.binder.x@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');

$includePaths = array(
    get_include_path(),
    t3lib_extMgm::extPath('jbx_appointment_booking').'src',
    t3lib_extMgm::extPath('jbx_appointment_booking').'lib',
    t3lib_extMgm::extPath('jbx_appointment_booking').'lib/zend_gdata/library/',
);
set_include_path(join(PATH_SEPARATOR, $includePaths));
session_start();

require_once('UserManagement.php');

require_once('phpmailer_lite/class.phpmailer-lite.php');
require_once('Zend/Loader.php');
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_Calendar');
Zend_Loader::loadClass('Zend_Http_Client');
require_once('CalendarFixed.php');

/**
 * Plugin 'Appointment Booking' for the 'jbx_appointment_booking' extension.
 *
 * @author    Johannes Binder <j.binder.x@gmail.com>
 * @package    TYPO3
 * @subpackage    tx_jbxappointmentbooking
 */
class tx_jbxappointmentbooking_pi1 extends tslib_pibase {
    var $prefixId      = 'tx_jbxappointmentbooking_pi1';        // Same as class name
    var $scriptRelPath = 'pi1/class.tx_jbxappointmentbooking_pi1.php';    // Path to this script relative to the extension dir.
    var $extKey        = 'jbx_appointment_booking';    // The extension key.

    var $defaultConf = array(
            'templateFileStep1' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_booking_type.html',
            'templateFileStep2' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_booking_month.html',
            'templateFileStep3' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_booking_slot.html',
            'templateFileStep4' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_user_login.html',
            'templateFileStep5' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_done.html',
            'templateFileStepCancel' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_cancel.html',
            'templateFileStepMonthSlot' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_booking_month_slot.html',
            'templateEmailSubscribeUser' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_email_subscribe_user.txt',
            'templateEmailSubscribeAdmin' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_email_subscribe_admin.txt',
            'templateEmailCancelUser' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_email_cancel_user.txt',
            'templateEmailCancelAdmin' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_email_cancel_admin.txt',
            'templateEmailRegisterAdmin' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_email_register_admin.txt',
            'calendarWeekdayNames' => 'Sun,Mon,Tue,Wed,Thu,Fri,Sat',
            'slotLength' => 75,
            'gcal_username' => '',
            'gcal_password' => '',
            'eventTitle' => '[appointment] ',
            'userPID' => 0,
            'userGroup' => 1,
            'mailFromEmail' => "test@test.test",
            'mailFromName' => "test",
            'mailSubjectSubscribeUser' => "Your appointment",
            'mailSubjectSubscribeAdmin' => "New appointment",
            'mailSubjectCancelUser' => "Your appointment",
            'mailSubjectCancelAdmin' => "Appointment cancelled",
            'mailSubjectRegisterAdmin' => "New user",
            'adminEmail' => "test@test.test",
            'types' => 'type1, type2, type3',
            'autoContinueAfterActions' => '', /*'selectType, select, selectSlot', */
            'mergeDateAndTimeSteps' => false,
            'calendarEventFeedURI' => 'https://www.google.com/calendar/feeds/default/private/full',
            'additionalRegisterUserData' => '', /*address, city, zip, telephone', */
            'requiredRegisterUserData' => 'username, password, email',
            'additionalFeedURIs' => '',
            'debug' => 0,
            'userManagementImpl' => 'PasswordUserManagement',
        );
    var $flexFormFields = array(
        'storagePid', 'slotLength', 'types',
    );

    var $templateFiles = array(
        'templateFileStep1', 'templateFileStep2', 'templateFileStep3', 'templateFileStep4', 'templateFileStep5', 'templateFileStepCancel',
        'templateEmailSubscribeUser', 'templateEmailSubscribeAdmin', 'templateEmailCancelUser', 'templateEmailCancelAdmin',
        'templateFileStepMonthSlot', 'templateEmailRegisterAdmin'
        );
    var $numSteps = 5;
    var $stepSlot = 3;
    var $seasonTableName = "tx_jbxappointmentbooking_season";
    var $slotTableName = "tx_jbxappointmentbooking_slot_range";
    var $sessionVars = array(
        'selected_d', 'selected_m', 'selected_y', 'selected_minute', 'selected_hour',
        'user', 'step', 'selected_type', 'appointment_id'
        );
    var $templateVars = array(
        'day', 'month', 'year', 'minute', 'hour', 'user', 'step', 'type', 'appointmentId'
        );
    var $requiredVars = array(
            1 => array('selected_type'),
            2 => array('selected_d', 'selected_m', 'selected_y'),
            3 => array('selected_minute', 'selected_hour'),
            4 => array('user'),
        );
    var $maxResultsPerFeed = 99999;

    var $tpl = null;
    var $db = null;

    /* @var $userManagement UserManagement */
    var $userManagement = null;

    var $eventCache = null;
    var $error = 0;
    var $types = array();
    var $autoContinueAfterActions = array();
    var $basicRegisterUserData = array('username', 'password', 'email', 'first_name', 'last_name');
    var $additionalRegisterUserData = array();
    var $requiredRegisterUserData = array();
    var $additionalFeedURIs = array();

    /**
     * The main method of the PlugIn
     *
     * @param    string        $content: The PlugIn content
     * @param    array        $conf: The PlugIn configuration
     * @return    The content that is displayed on the website
     */
    function main($content, $conf) {
        $this->conf = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        $this->pi_USER_INT_obj = 1;    // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
        $this->pi_initPIflexForm();

        $this->init();

        if (!$this->isActiveInstance()) $this->resetSession();

        $this->handleAction(t3lib_div::_GET('action'));
        $content = $this->performStep($_SESSION['step']);
        
        return $this->pi_wrapInBaseClass($content);
    }

    private function isActiveInstance() {
        return in_array($_SESSION['selected_type'], $this->types);
    }

    private function handleAction($action) {
        call_user_func(array($this, "action" . $action), t3lib_div::_GET('value'));
        if (in_array($action, $this->autoContinueAfterActions)) $this->actionNextStep(); 
    }

    private function performStep($step) {
        return call_user_func(array($this, "actionStep" . $step));
    }
    
    private function getBasicTplData() {
        $tpl_data = array();
        for ($i = 0; $i < count($this->templateVars); ++$i) {
            $tpl_data[$this->templateVars[$i]] = $_SESSION[$this->sessionVars[$i]];
        }
        $tpl_data['status'] = $this->error;
        $tpl_data['url'] = $this->pi_getPageLink($GLOBALS['TSFE']->id);
        return $tpl_data;
    }

    private function actionSelectType($type) {
        $this->resetSession();
        $_SESSION['selected_type'] = $type;
    }

    private function actionStep1() {
        $tpl_data = $this->getBasicTplData();
        $tpl_data['types'] = $this->types;
        $this->prepareTpl($tpl_data);
        return $this->tpl->display($this->conf['templateFileStep1']);
    }

    private function actionStepCancel() {
        $this->prepareTpl($this->getBasicTplData());
        
        if ($this->error == 0) {
            $this->sendEmail($_SESSION['user']['email'],
                $this->conf['mailSubjectCancelUser'], $this->conf['templateEmailCancelUser']);
            $this->sendEmail($this->conf['adminEmail'],
                $this->conf['mailSubjectCancelAdmin'], $this->conf['templateEmailCancelAdmin']);
        }

        $this->resetSession();

        return $this->tpl->display($this->conf['templateFileStepCancel']);
    }

    private function actionCancel() {
        $_SESSION['step'] = 'cancel';
        $event_content = explode("\n", $this->removeEvent(base64_decode(t3lib_div::_GET('id'))));
        if (count($event_content) < 2) {
            $this->error = 1;
        } else {
            $data = explode("|", unserialize(base64_decode($event_content[2])));
        }
        $data_keys = array('tmp', 'tmp', 'tmp', 'selected_type', 'selected_hour', 'selected_minute', 'selected_m', 'selected_d', 'selected_y');
        foreach ($data_keys as $key => $value) $_SESSION[$value] = $data[$key];
        unset($_SESSION['tmp']);
        $_SESSION['user'] = array('username' => $data[0], 'email' => $data[1], 'name'  => $data[2]);
    }

    private function containsEvent($start, $end) {
        if (is_null($this->eventCache)) {
            $this->rebuildEventCache();
        }

        foreach ($this->eventCache as $event) {
            foreach ($event->when as $when) {
                $cur_start_time = strtotime($when->startTime);
                $cur_end_time = strtotime($when->endTime);
                if ($cur_start_time > $start && $cur_start_time < $end) return true;
                if ($cur_start_time == $start && $cur_start_time == $end) return true;
                if ($cur_start_time <= $start && $cur_end_time > $start) return true;
                if ($cur_end_time > $start && $cur_end_time < $end) return true;
            }
        }
        return false;
    }

    private function getHttpClient() {
        $client = null;
        try {
            $client = Zend_Gdata_ClientLogin::getHttpClient(
                $this->conf['gcal_username'], $this->conf['gcal_password'], Zend_Gdata_Calendar::AUTH_SERVICE_NAME);
        } catch (Zend_Gdata_App_HttpException $e) {
            $this->error = 11;
            $client = null;
        }
        return $client;
    }
    
    private function rebuildEventCache() {
        $client = $this->getHttpClient();
        if ($client == null) return;
 
        $gcal = new Zend_Gdata_Calendar($client);
        $month = ($_SESSION['step'] == 1) ? $_SESSION['date_m'] : $_SESSION['selected_m'];
        $year = ($_SESSION['step'] == 1) ? $_SESSION['date_y'] : $_SESSION['selected_y'];
        $start_time = mktime(0, 0, 0, $month, 1, $year);
        $end_time = mktime(0, 0, 0, $month + 1, 1, $year);
        try {
            $eventFeed = $this->getEventFeed($gcal, $this->conf['calendarEventFeedURI'], $start_time, $end_time);
            foreach ($this->additionalFeedURIs as $uri) {
                if (empty($uri)) continue;
                $additionalEventFeed = $this->getEventFeed($gcal, $uri, $start_time, $end_time);
                foreach ($additionalEventFeed as $event) $eventFeed->addEntry($event);
            }
        } catch (Zend_Gdata_App_Exception $e) {
            return false;
        }
        $this->eventCache = $eventFeed;
    }

    private function getEventFeed(&$gcal, $uri, $start_time, $end_time) {
        $query = $gcal->newEventQuery($uri);
        $query->setFutureevents('true');
        $query->setUser(null);
        $query->setVisibility(null);
        $query->setProjection(null);
        $query->setStartMin(date("c", $start_time));
        $query->setStartMax(date("c", $end_time));
        $query->setMaxResults($this->maxResultsPerFeed);
        return $gcal->getCalendarEventFeed($query);
    }

    private function clearEventCache() {
        $this->eventCache = null;
    }

    private function removeEvent($id) {
        $client = $this->getHttpClient();
        if ($client == null) return;
        $gcal = new Zend_Gdata_Calendar_Fixed($client);

        try {
            $event = $gcal->getCalendarEventEntry($id);
            $content = $event->getContent()->getText();
            $gcal->delete($id);
        } catch (Zend_Gdata_App_Exception $e) {
            return null;
        }
        return $content;
    }

    private function addEvent($start, $end) {
        $client = $this->getHttpClient();
        if ($client == null) return;
        $gcal = new Zend_Gdata_Calendar($client);

        $title = htmlentities($this->conf['eventTitle'] . $_SESSION['user']['username']);
        $start = date(DATE_ATOM, $start);
        $end = date(DATE_ATOM, $end);
        $description = "{$_SESSION['user']['username']} ({$_SESSION['user']['name']}, " .
            "{$_SESSION['user']['uid']}), {$_SESSION['user']['email']}, {$_SESSION['user']['telephone']}\n{$_SESSION['selected_type']}";
        $data = array($_SESSION['user']['username'], $_SESSION['user']['email'], $_SESSION['user']['name'],
            $_SESSION['selected_type'], $_SESSION['selected_hour'], $_SESSION['selected_minute'],
            $_SESSION['selected_m'], $_SESSION['selected_d'], $_SESSION['selected_y']);
        $description .= "\n" . base64_encode(serialize(implode("|", $data)));

        try {
            $event = $gcal->newEventEntry();
            $event->title = $gcal->newTitle($title);
            $when = $gcal->newWhen();
            $when->startTime = $start;
            $when->endTime = $end;
            $event->when = array($when);
            $content = $gcal->newContent($description);
            $event->content = $content;
            $new_event = $gcal->insertEvent($event, $this->conf['calendarEventFeedURI']);
        } catch (Zend_Gdata_App_Exception $e) {
            return false;
        }
        $_SESSION['appointment_id'] = base64_encode($new_event->getLink('edit')->href);
        return true;
    }

    private function actionStep5() {
        $start = mktime($_SESSION['selected_hour'], $_SESSION['selected_minute'],
            0, $_SESSION['selected_m'], $_SESSION['selected_d'], $_SESSION['selected_y']);
        $end = mktime($_SESSION['selected_hour'], $_SESSION['selected_minute'] + $this->conf['slotLength'],
            0, $_SESSION['selected_m'], $_SESSION['selected_d'], $_SESSION['selected_y']);
        $this->clearEventCache();
        if ($this->containsEvent($start, $end)) $status = 2;
        else $status = $this->addEvent($start, $end) ? 0 : 1;

        $this->error = ($this->error == 0) ? $status : $this->error;
        $tpl_data = $this->getBasicTplData();
        $tpl_data['siteRootUrl'] = $this->getSiteRootUrl();
        $tpl_data['user'] = $_SESSION['user'];
        $this->prepareTpl($tpl_data);

        if ($status == 0) {
            $this->sendEmail($_SESSION['user']['email'],
                $this->conf['mailSubjectSubscribeUser'], $this->conf['templateEmailSubscribeUser']);
            $this->sendEmail($this->conf['adminEmail'],
                $this->conf['mailSubjectSubscribeAdmin'], $this->conf['templateEmailSubscribeAdmin']);
        }

        $this->resetSession();

        return $this->tpl->display($this->conf['templateFileStep5']);
    }

    private function getSiteRootUrl() {
        $serverAddress = (($_SERVER["HTTPS"] == "on") ? 'https' : 'http') . '://';
        if ($_SERVER["SERVER_PORT"] != "80") {
            $serverAddress .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
        } else {
            $serverAddress .= $_SERVER["SERVER_NAME"];
        }
        $serverAddress .= str_replace("index.php", "", $_SERVER['PHP_SELF']);
        return $serverAddress;
    }

    private function resetSession()
    {
        foreach ($this->sessionVars as $var) {
            unset($_SESSION[$var]);
        }
        $_SESSION['step'] = 1;
        $this->clearEventCache();
        $this->userManagement->reset();
    }

    function sendEmail($email, $subject, $template) {
        $mail = new PHPMailerLite();
        $mail->SetFrom($this->conf['mailFromEmail'], $this->conf['mailFromName']);
        $mail->AddAddress($email);
        $mail->WordWrap = 50;
        $mail->IsHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $this->tpl->display($template);
        if (!$mail->Send()) return false;
        return true;
    }
    
    private function actionLogin(&$data) {
        $data = array_merge(t3lib_div::_POST(), $data);
        $result = $this->userManagement->loginUser($data);
        if ($result->getUser() != null) $_SESSION['user'] = $result->getUser();
        if ($result->getError() >= 0) $this->error = $result->getError();
        //this->errorDesc = $result->getErrorString(); // TODO: log?
        if ($result->getRedirect()) t3lib_utility_Http::redirect($this->pi_getPageLink($GLOBALS['TSFE']->id));
        foreach ($result->getTemplateVars() as $key => $value) {
            $this->tpl->assign($key, $value);
        }
    }

    private function actionRegister() {
        $_POST['telephone'] = str_replace(" ", "", $_POST['telephone']);
        $this->tpl->assign("errorreg", 1);
        $fields = array_merge($this->additionalRegisterUserData, $this->basicRegisterUserData);
        $data = array();
        $post = $this->getEscapedPost();
        foreach ($fields as $field) {
            if (empty($field)) {
                unset($fields[$field]);
                continue;
            }
            $value = $post[$field];
            if (empty($value) && in_array($field, $this->requiredRegisterUserData)) {
                $this->error = 2;
                $this->tpl->assign("errorfield", implode(", ", $this->requiredRegisterUserData));
                $this->tpl->assign($field . "_error", 1);
            }
            $data[$field] = $value;
        }
        if ($this->error == 2) return;
        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error = 5;
            return;
        }
        $res = $this->db->exec_SELECTquery("*", 'fe_users',
            "deleted = 0 and (username = '" . $data["username"] . "' or email = '" . $data['email'] . "')"
        );
        if ($this->db->sql_num_rows($res) > 0) {
            $this->error = 4;
            return;
        }
        $data['pid'] = $this->conf['userPID'];
        $data['usergroup'] = $this->conf['userGroup'];
        $data['name'] = $data['first_name'] . " " . $data['last_name'];
        $data['tstamp'] = time();
        $res = $this->db->exec_INSERTquery('fe_users', $data);
        if (!$res) {
            $this->error = 3;
            return;
        } else {
            $data['uid'] = $this->db->sql_insert_id();
            $this->tpl->assign("user", $data);
            $this->sendEmail($this->conf['adminEmail'],
                $this->conf['mailSubjectRegisterAdmin'], $this->conf['templateEmailRegisterAdmin']);
        }
        $this->tpl->assign("errorreg", 0);
        $this->actionLogin($data);
    }

    private function actionStep4() {
        if ($_SESSION['user']['uid'] > 0) return $this->actionStep5();
        
        $tpl_data = $this->getBasicTplData();
        $tpl_data['data'] = $this->getEscapedPost();
        $this->prepareTpl($tpl_data);

        return $this->tpl->display($this->conf['templateFileStep4']);
    }

    private function getEscapedPost() {
        $data = array();
        foreach (t3lib_div::_POST() as $key => $value) {
            $data[$key] = mysql_real_escape_string($value);
        }
        return $data;
    }

    private function actionSelectSlot($time) {
        list($_SESSION['selected_hour'], $_SESSION['selected_minute']) = explode("-", $time);
    }

    private function getSlots($month, $day, $year) {
        $weekday = date('N', mktime(0, 0, 0, $month, $day, $year));
        $season_id = $this->getSeason($month, $day);
        $slot_ranges = $this->getSlotRanges($season_id, $weekday);
        return $this->calcSlotEntries($slot_ranges, $month, $day, $year);
    }

    private function calcSlotEntries($slot_ranges, $month, $day, $year) {
        $slots = array();
        $cur = 0;
        $next = 0;
        foreach ($slot_ranges as $slot_entry) {
            $start = $this->toMinutes($slot_entry['from_hour'], $slot_entry['from_minute']);
            $end = $this->toMinutes($slot_entry['to_hour'], $slot_entry['to_minute']);
            while ($next <= $end) {
                if ($cur < $start) $cur = $start;
                $next = $cur + $this->conf['slotLength'];
                if ($next <= $end) {
                    $item = array(
                        'from_hour' => floor($cur / 60),
                        'from_minute' => $cur % 60,
                        'to_hour' => floor($next / 60),
                        'to_minute' => $next % 60,
                        'status' => 2,
                    );
                    if ($item['from_hour'] == $_SESSION['selected_hour'] && $item['from_minute'] == $_SESSION['selected_minute']) {
                        $item['status'] = 4;
                    }
                    $start_time = mktime($item['from_hour'], $item['from_minute'], 0, $month, $day, $year);
                    $end_time = mktime($item['to_hour'], $item['to_minute'], 0, $month, $day, $year);
                    if ($this->containsEvent($start_time, $end_time)) $item['status'] = 1;
                    $slots[] = $item;
                }
                $cur = $next;
            }
        }
        return $slots;
    }

    private function getSlotRanges($season_id, $weekday) {
        $query = (!empty($this->conf['storagePid'])) ? " pid = {$this->conf['storagePid']} and " : "";
        $slot_ranges = array();
        $res = $this->db->exec_SELECTquery("*", $this->slotTableName,
            "hidden = 0 and deleted = 0 and $query " .
            "season = $season_id and weekday = $weekday",
            "",
            "from_hour, from_minute"
        );
        while ($row = $this->db->sql_fetch_assoc($res)){
            $slot_ranges[] = $row;
        }
        $this->db->sql_free_result($res);
        return $slot_ranges;
    }

    private function toMinutes($hours, $minutes) {
        return $hours * 60 + $minutes;
    }

    private function getSeason($month, $day)
    {
        $query = (!empty($this->conf['storagePid'])) ? "pid = {$this->conf['storagePid']} and " : "";
        $res = $this->db->exec_SELECTquery("*", $this->seasonTableName,
            "hidden = 0 and deleted = 0 and $query " .
            "((from_month < $month and until_month > $month) or " .
            "((from_month = $month or until_month = $month)) and from_day <= $day and until_day >= $day)"
        );
        $row = $this->db->sql_fetch_assoc($res);
        $this->db->sql_free_result($res);
        return $row['uid'];
    }

    private function actionStep3()
    {
        if ($_SESSION['date_m'] != $_SESSION['selected_m'] || $_SESSION['date_y'] != $_SESSION['selected_y']) {
            $this->clearEventCache();
        }
        $slots = $this->getSlots($_SESSION['selected_m'], $_SESSION['selected_d'], $_SESSION['selected_y']);
        
        $tpl_data = $this->getBasicTplData();
        $tpl_data['timeSlots'] = $slots;
        $this->prepareTpl($tpl_data);
        
        return $this->tpl->display($this->conf['templateFileStep3']);
    }

    private function actionPrevStep() {
        --$_SESSION['step'];
        if ($this->conf['mergeDateAndTimeSteps'] && $_SESSION['step'] == $this->stepSlot) --$_SESSION['step'];
        if ($_SESSION['step'] < 1) $_SESSION['step'] = 1;
    }
    
    private function checkRequired($step) {
        foreach ($this->requiredVars[$step] as $requiredVar) {
            if ($requiredVar == "password") continue;
            if (!isset($_SESSION[$requiredVar])) {
                return false;
            }
        }
        return true;
    }

    private function actionNextStep() {
        if (!$this->checkRequired($_SESSION['step'])) {
            $this->error = 9;
            return;
        }
        if ($this->conf['mergeDateAndTimeSteps'] && $_SESSION['step'] == ($this->stepSlot - 1)) {
            if (!$this->checkRequired($_SESSION['step'] + 1)) {
                $this->error = 10;
                return;
            }
        }
        ++$_SESSION['step'];
        if ($this->conf['mergeDateAndTimeSteps'] && $_SESSION['step'] == $this->stepSlot) ++$_SESSION['step'];
        if ($_SESSION['step'] > $this->numSteps) $_SESSION['step'] = $this->numSteps;
    }

    private function actionPrevMonth() {
        --$_SESSION['date_m'];
        if ($_SESSION['date_m'] < 1) {
            $_SESSION['date_m'] = 12;
            --$_SESSION['date_y'];
        }
        $this->clearEventCache();
    }

    private function actionNextMonth() {
        ++$_SESSION['date_m'];
        if ($_SESSION['date_m'] > 12) {
            $_SESSION['date_m'] = 1;
            ++$_SESSION['date_y'];
        }
        $this->clearEventCache();
    }

    private function actionSelect($day) {
        $_SESSION['selected_d'] = $day;
        $_SESSION['selected_m'] = $_SESSION['date_m'];
        $_SESSION['selected_y'] = $_SESSION['date_y'];
        unset($_SESSION['selected_hour']);
        unset($_SESSION['selected_minute']);
    }

    private function actionStep2()
    {
        $days = array_merge(
            $this->getFillerDays($_SESSION['date_m'], $_SESSION['date_y']),
            $this->getMonthDays($_SESSION['date_m'], $_SESSION['date_y'], $_SESSION['date_d'])
        );

        $tpl_data = $this->getBasicTplData();
        $tpl_data['days'] = $days;
        $tpl_data['weekdayNames'] = explode(',', $this->conf['calendarWeekdayNames']);
        $tpl_data['curMonth'] = $_SESSION['date_m'];
        $tpl_data['curYear'] = $_SESSION['date_y'];
        
        if ($this->conf['mergeDateAndTimeSteps']) {
            if ($_SESSION['date_m'] != $_SESSION['selected_m'] || $_SESSION['date_y'] != $_SESSION['selected_y']) {
                $this->clearEventCache();
            }
            
            $tpl_data['timeSlots'] = $this->getSlots(
                $_SESSION['selected_m'], $_SESSION['selected_d'], $_SESSION['selected_y']);
        }

        $this->prepareTpl($tpl_data);

        return ($this->conf['mergeDateAndTimeSteps']) ? 
            $this->tpl->display($this->conf['templateFileStepMonthSlot']) : 
            $this->tpl->display($this->conf['templateFileStep2']);
    }

    private function prepareTpl($tpl_data) {
        foreach ($tpl_data as $key => $value) $this->tpl->assign($key, $value);
    }

    private function getMonthDays($date_m, $date_y, $date_d)
    {
        $days = array();
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $date_m, $date_y);
        for ($i = 1; $i <= $days_in_month; ++$i) {
            $status = 2;
            $cur_m = date("m");
            $cur_y = date("Y");
            $cur_d = date("j");
            if (($date_y < $cur_y) || ($date_y == $cur_y && $date_m < $cur_m) || ($date_y == $cur_y && $date_m == $cur_m && $i <= $date_d)) $status = 0;
            if ($date_y == $cur_y && $date_m == $cur_m && $i <= $cur_d) $status = 0;
            else if ($i == $_SESSION['selected_d'] && $date_m == $_SESSION['selected_m'] && $date_y == $_SESSION['selected_y']) $status = 4;
            else {
                $slots = $this->getSlots($date_m, $i, $date_y);
                if (count($slots) < 1) $status = 0;
                else if (!$this->checkForFreeSlots($slots)) $status = 0;
            }
            $index = date('N', mktime(0, 0, 0, $date_m, $i, $date_y));
            $days[] = array('nr' => $i, 'status' => $status, 'index' => $index);
        }
        return $days;
    }

    private function checkForFreeSlots($slots) {
        foreach ($slots as $slot) {
            if ($slot['status'] != 1) return true;
        }
        return false;
    }

    private function getFillerDays($date_m, $date_y)
    {
        $days = array();
        $first_weekday_of_month = date('N', mktime(0, 0, 0, $date_m, 1, $date_y));
        for ($i = 0; $i < $first_weekday_of_month; ++$i) {
            $days[] = array('nr' => 0, 'status' => 3, 'index' => $i);
        }
        return ($days);
    }

    function cleanUpTemplateFilePath($templateFile) {
        if (strpos($this->conf[$templateFile], "EXT:") !== false) {
            $this->conf[$templateFile] = PATH_site . $GLOBALS['TSFE']->tmpl->getFileName($this->conf[$templateFile]);
        }
    }

    function init() {
        foreach ($this->defaultConf as $key => $val) {
            if (empty($this->conf[$key])) $this->conf[$key] = $this->defaultConf[$key];
        }
        foreach ($this->flexFormFields as $field) {
            $val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $field);
            if (!empty($val)) $this->conf[$field] = $val;
        }
        foreach ($this->templateFiles as $templateFile) $this->cleanUpTemplateFilePath($templateFile);

        $this->tpl = tx_smarty::smarty();
        $this->db = $GLOBALS['TYPO3_DB'];

        if (!isset($_SESSION['date_d'])) $_SESSION['date_d'] = date("d");
        if (!isset($_SESSION['date_m'])) $_SESSION['date_m'] = date("m");
        if (!isset($_SESSION['date_y'])) $_SESSION['date_y'] = date("Y");
        if (!isset($_SESSION['step'])) $_SESSION['step'] = 1;

        $this->types = $this->explodeTrimmed($this->conf['types']);
        $this->autoContinueAfterActions = $this->explodeTrimmed($this->conf['autoContinueAfterActions']);
        $this->additionalRegisterUserData = $this->explodeTrimmed($this->conf['additionalRegisterUserData']);
        $this->requiredRegisterUserData = $this->explodeTrimmed($this->conf['requiredRegisterUserData']);
        $this->additionalFeedURIs = $this->explodeTrimmed($this->conf['additionalFeedURIs']);

        if ($GLOBALS["TSFE"]->fe_user->user["uid"] > 0) {
            $_SESSION['user'] = $GLOBALS["TSFE"]->fe_user->user;
        } else {
            unset($_SESSION['user']);
        }

        $userManagementClassName = $this->conf['userManagementImpl'];
        require_once($userManagementClassName.".php");
        $this->userManagement = new $userManagementClassName();
        $this->userManagement->init($this->db, $this->conf);
    }
    
    private function explodeTrimmed($dataStr) {
        $data = explode(",", $dataStr);
        for ($i = 0; $i < count($data); ++$i) {
            $data[$i] = trim($data[$i]);
        }
        return $data;
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jbx_appointment_booking/pi1/class.tx_jbxappointmentbooking_pi1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jbx_appointment_booking/pi1/class.tx_jbxappointmentbooking_pi1.php']);
}

?>
