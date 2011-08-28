<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_jbxappointmentbooking_pi1.php', '_pi1', 'list_type', 0);

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_jbxappointmentbooking_season=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_jbxappointmentbooking_slot_range=1
');
?>
