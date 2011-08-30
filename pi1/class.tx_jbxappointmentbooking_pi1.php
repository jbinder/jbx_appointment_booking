<?php
ini_set('display_errors','On'); // error_reporting(E_ALL);
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Johannes Binder <j.binder.x@gmail.com>
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

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . "/../lib/zend_gdata/library/");
session_start();

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
            'templateFileStep1' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_booking_month.html',
            'templateFileStep2' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_booking_slot.html',
            'templateFileStep3' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_user_login.html',
            'templateFileStep4' => 'EXT:jbx_appointment_booking/tpl/jbx_appointment_done.html',
            'calendarWeekdayNames' => 'Sun,Mon,Tue,Wed,Thu,Fri,Sat',
            'slotLength' => 75,
            'gcal_username' => '',
            'gcal_password' => '',
            'event_title' => '[appointment] ',
        );

    var $templateFiles = array('templateFileStep1', 'templateFileStep2', 'templateFileStep3', 'templateFileStep4');
    var $numSteps = 4;
    var $seasonTableName = "tx_jbxappointmentbooking_season";
    var $slotTableName = "tx_jbxappointmentbooking_slot_range";

    var $tpl = null;
    var $db = null;

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

        $this->init();

        $this->handleAction(t3lib_div::_GET('action'));
        $content = $this->performStep($_SESSION['step']);

        return $this->pi_wrapInBaseClass($content);
    }

    private function handleAction($action) {
        call_user_func(array($this, "action" . $action), t3lib_div::_GET('value'));
    }

    private function performStep($step) {
        return call_user_func(array($this, "actionStep" . $step));
    }

    private function addEvent() {
        require_once 'Zend/Loader.php';
        Zend_Loader::loadClass('Zend_Gdata');
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
        Zend_Loader::loadClass('Zend_Gdata_Calendar');
        Zend_Loader::loadClass('Zend_Http_Client');

        $client = Zend_Gdata_ClientLogin::getHttpClient(
            $this->conf['gcal_username'], $this->conf['gcal_password'], Zend_Gdata_Calendar::AUTH_SERVICE_NAME);
        $gcal = new Zend_Gdata_Calendar($client);

        $title = htmlentities($this->conf['event_title'] . $GLOBALS["TSFE"]->fe_user->user['username']);
        $start = date(DATE_ATOM, mktime($_SESSION['selected_hour'], $_SESSION['selected_minute'],
            0, $_SESSION['selected_m'], $_SESSION['selected_d'], $_SESSION['selected_y']));
        $end = date(DATE_ATOM, mktime($_SESSION['selected_hour'], $_SESSION['selected_minute'] + $this->conf['slotLength'],
            0, $_SESSION['selected_m'], $_SESSION['selected_d'], $_SESSION['selected_y']));
        $description = $GLOBALS["TSFE"]->fe_user->user['username'] . ": " . $GLOBALS["TSFE"]->fe_user->user['email'];

        try {
            $event = $gcal->newEventEntry();
            $event->title = $gcal->newTitle($title);
            $when = $gcal->newWhen();
            $when->startTime = $start;
            $when->endTime = $end;
            $event->when = array($when);
            $content = $gcal->newContent($description);
            $event->content = $content;
            $gcal->insertEvent($event);
        } catch (Zend_Gdata_App_Exception $e) {
            return false;
        }
        return true;
    }

    private function actionStep4() {
        $status = $this->addEvent() ? "0" : "1";

        $tpl_data = array(
            'url' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
            'month' => $_SESSION['selected_m'],
            'year' => $_SESSION['selected_y'],
            'day' => $_SESSION['selected_d'],
            'minute' => $_SESSION['selected_minute'],
            'hour' => $_SESSION['selected_hour'],
            'status' => $status,
        );
        $this->prepareTpl($tpl_data);

        $this->resetSession();

        return $this->tpl->display($this->conf['templateFileStep4']);
    }

    private function resetSession()
    {
        unset($_SESSION['selected_d']);
        unset($_SESSION['selected_m']);
        unset($_SESSION['selected_y']);
        unset($_SESSION['selected_minute']);
        unset($_SESSION['selected_hour']);
        $_SESSION['step'] = 1;
    }

    private function actionStep3() {
        if ($GLOBALS["TSFE"]->fe_user->user["uid"] > 0) return $this->actionStep4();
        
        $tpl_data = array(
            'url' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
            'month' => $_SESSION['selected_m'],
            'year' => $_SESSION['selected_y'],
            'day' => $_SESSION['selected_d'],
            'minute' => $_SESSION['selected_minute'],
            'hour' => $_SESSION['selected_hour'],
        );
        $this->prepareTpl($tpl_data);

        return $this->tpl->display($this->conf['templateFileStep3']);
    }

    private function actionSelectSlot($time) {
        list($_SESSION['selected_hour'], $_SESSION['selected_minute']) = explode("-", $time);
    }

    private function getSlots() {
        $month = $_SESSION['selected_m'];
        $day = $_SESSION['selected_d'];
        $year = $_SESSION['selected_y'];
        $weekday = date('N', mktime(0, 0, 0, $month, $day, $year));
        $season_id = $this->getSeason($month, $day);
        $slot_ranges = $this->getSlotRanges($season_id, $weekday);
        return $this->calcSlotEntries($slot_ranges);
    }

    private function calcSlotEntries($slot_ranges) {
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
                    $slots[] = $item;
                }
                $cur = $next;
            }
        }
        return $slots;
    }

    private function getSlotRanges($season_id, $weekday) {
        $slot_ranges = array();
        $res = $this->db->exec_SELECTquery("*", $this->slotTableName,
            "hidden = 0 and deleted = 0 and " .
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
        $res = $this->db->exec_SELECTquery("*", $this->seasonTableName,
            "hidden = 0 and deleted = 0 and " .
            "(from_month < $month and until_month > $month) or " .
            "((from_month = $month or until_month = $month) and from_day <= $day and until_day >= $day)"
        );
        $row = $this->db->sql_fetch_assoc($res);
        $this->db->sql_free_result($res);
        return $row['uid'];
    }

    private function actionStep2()
    {
        $slots = $this->getSlots();
        
        $tpl_data = array(
            'url' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
            'month' => $_SESSION['selected_m'],
            'year' => $_SESSION['selected_y'],
            'day' => $_SESSION['selected_d'],
            'timeSlots' => $slots,
        );
        $this->prepareTpl($tpl_data);
        
        return $this->tpl->display($this->conf['templateFileStep2']);
    }

    private function actionPrevStep() {
        --$_SESSION['step'];
        if ($_SESSION['step'] < 1) $_SESSION['step'] = 1;
    }

    private function actionNextStep() {
        ++$_SESSION['step'];
        if ($_SESSION['step'] > $this->numSteps) $_SESSION['step'] = $this->numSteps;
    }

    private function actionPrevMonth() {
        --$_SESSION['date_m'];
        if ($_SESSION['date_m'] < 1) {
            $_SESSION['date_m'] = 12;
            --$_SESSION['date_y'];
        }
    }

    private function actionNextMonth() {
        ++$_SESSION['date_m'];
        if ($_SESSION['date_m'] > 12) {
            $_SESSION['date_m'] = 1;
            ++$_SESSION['date_y'];
        }
    }

    private function actionSelect($day) {
        $_SESSION['selected_d'] = $day;
        $_SESSION['selected_m'] = $_SESSION['date_m'];
        $_SESSION['selected_y'] = $_SESSION['date_y'];
        unset($_SESSION['selected_hour']);
        unset($_SESSION['selected_minute']);
    }

    private function actionStep1()
    {
        $days = array_merge(
            $this->getFillerDays($_SESSION['date_m'], $_SESSION['date_y']),
            $this->getMonthDays($_SESSION['date_m'], $_SESSION['date_y'], $_SESSION['date_d'])
        );

        $tpl_data = array(
            'url' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
            'month' => $_SESSION['date_m'],
            'year' => $_SESSION['date_y'],
            'days' => $days,
            'weekdayNames' => explode(',', $this->conf['calendarWeekdayNames']),
        );
        $this->prepareTpl($tpl_data);

        return $this->tpl->display($this->conf['templateFileStep1']);
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
            if (($date_m < $cur_m) || ($date_m == $cur_m && $i <= $date_d)) $status = 0;
            if ($i == $_SESSION['selected_d'] && $date_m == $_SESSION['selected_m'] && $date_y == $_SESSION['selected_y']) $status = 4;
            $index = date('N', mktime(0, 0, 0, $date_m, $i, $date_y));
            $days[] = array('nr' => $i, 'status' => $status, 'index' => $index);
        }
        return $days;
    }

    private function getFillerDays($date_m, $date_y)
    {
        $days = array();
        $first_weekday_of_month = date('N', mktime(0, 0, 0, $date_m, 1, $date_y));
        for ($i = 0; $i < $first_weekday_of_month; ++$i) {
            $days[] = array('nr' => 0, 'status' => 3, 'index' => $first_weekday_of_month - $i);
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
        foreach ($this->templateFiles as $templateFile) $this->cleanUpTemplateFilePath($templateFile);
        if (!isset($this->conf['storagePid'])) $this->conf['storagePid'] = $GLOBALS["TSFE"]->id;

        $this->tpl = tx_smarty::smarty();
        $this->db = $GLOBALS['TYPO3_DB'];

        if (!isset($_SESSION['date_d'])) $_SESSION['date_d'] = date("d");
        if (!isset($_SESSION['date_m'])) $_SESSION['date_m'] = date("m");
        if (!isset($_SESSION['date_y'])) $_SESSION['date_y'] = date("Y");
        if (!isset($_SESSION['step'])) $_SESSION['step'] = 1;
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jbx_appointment_booking/pi1/class.tx_jbxappointmentbooking_pi1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jbx_appointment_booking/pi1/class.tx_jbxappointmentbooking_pi1.php']);
}

?>
