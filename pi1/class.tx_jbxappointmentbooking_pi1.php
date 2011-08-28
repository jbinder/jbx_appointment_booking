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
            'calendarWeekdayNames' => 'Sun,Mon,Tue,Wed,Thu,Fri,Sat',
        );

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

        switch (t3lib_div::_GET('action')) {
            case "prevmonth":
                $this->actionPrevMonth();
                break;
            case "nextmonth":
                $this->actionNextMonth();
                break;
            case "select":
                $this->actionSelect(t3lib_div::_GET('value'));
                break;
            case "nextstep":
                $this->actionNextStep();
                break;
            case "prevstep":
                $this->actionPrevStep();
                break;
        }
        
        switch ($_SESSION['step']) {
            case 1:
                $content = $this->actionStep1();
                break;
            case 2:
                $content = $this->actionStep2();
                break;
        }

        return $this->pi_wrapInBaseClass($content);
    }

    private function actionStep2()
    {
        $tpl_data = array(
            'url' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
            'month' => $_SESSION['selected_m'],
            'year' => $_SESSION['selected_y'],
            'day' => $_SESSION['selected_d'],
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
        if ($_SESSION['step'] > 2) $_SESSION['step'] = 2;
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
        $templateFiles = array('templateFileStep1', 'templateFileStep2', 'templateFileStep3');
        foreach ($templateFiles as $templateFile) $this->cleanUpTemplateFilePath($templateFile);
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
