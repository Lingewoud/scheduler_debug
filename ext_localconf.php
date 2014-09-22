<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
	}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['schedulerdebug'] = array('EXT:scheduler_debug/cli/tx_schedulerdebug.php', '_CLI_lowlevel');
?>
