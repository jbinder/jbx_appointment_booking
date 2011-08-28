<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_jbxappointmentbooking_season'] = array (
	'ctrl' => $TCA['tx_jbxappointmentbooking_season']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,from_day,from_month,until_day,until_month,title'
	),
	'feInterface' => $TCA['tx_jbxappointmentbooking_season']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_jbxappointmentbooking_season',
				'foreign_table_where' => 'AND tx_jbxappointmentbooking_season.pid=###CURRENT_PID### AND tx_jbxappointmentbooking_season.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'from_day' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:jbx_appointment_booking/locallang_db.xml:tx_jbxappointmentbooking_season.from_day',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'from_month' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:jbx_appointment_booking/locallang_db.xml:tx_jbxappointmentbooking_season.from_month',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'until_day' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:jbx_appointment_booking/locallang_db.xml:tx_jbxappointmentbooking_season.until_day',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'until_month' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:jbx_appointment_booking/locallang_db.xml:tx_jbxappointmentbooking_season.until_month',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:jbx_appointment_booking/locallang_db.xml:tx_jbxappointmentbooking_season.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, from_day, from_month, until_day, until_month, title;;;;2-2-2')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);



$TCA['tx_jbxappointmentbooking_slot_range'] = array (
	'ctrl' => $TCA['tx_jbxappointmentbooking_slot_range']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,weekday,from_hour,from_minute,to_hour,to_minute,title,season'
	),
	'feInterface' => $TCA['tx_jbxappointmentbooking_slot_range']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_jbxappointmentbooking_slot_range',
				'foreign_table_where' => 'AND tx_jbxappointmentbooking_slot_range.pid=###CURRENT_PID### AND tx_jbxappointmentbooking_slot_range.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'weekday' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:jbx_appointment_booking/locallang_db.xml:tx_jbxappointmentbooking_slot_range.weekday',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'from_hour' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:jbx_appointment_booking/locallang_db.xml:tx_jbxappointmentbooking_slot_range.from_hour',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'from_minute' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:jbx_appointment_booking/locallang_db.xml:tx_jbxappointmentbooking_slot_range.from_minute',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'to_hour' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:jbx_appointment_booking/locallang_db.xml:tx_jbxappointmentbooking_slot_range.to_hour',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'to_minute' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:jbx_appointment_booking/locallang_db.xml:tx_jbxappointmentbooking_slot_range.to_minute',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:jbx_appointment_booking/locallang_db.xml:tx_jbxappointmentbooking_slot_range.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'season' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:jbx_appointment_booking/locallang_db.xml:tx_jbxappointmentbooking_slot_range.season',		
			'config' => array (
				'type' => 'select',	
				'foreign_table' => 'tx_jbxappointmentbooking_season',	
				'foreign_table_where' => 'ORDER BY tx_jbxappointmentbooking_season.uid',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, weekday, from_hour, from_minute, to_hour, to_minute, title;;;;2-2-2, season;;;;3-3-3')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>