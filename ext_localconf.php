<?php

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// get extension setup
$ext_config=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rte_link_records']);

// Hooks for links
$TYPO3_CONF_VARS['SC_OPTIONS']['ext/rtehtmlarea/mod3/class.tx_rtehtmlarea_browse_links.php']['browseLinksHook'][] = t3lib_extMgm::extPath($_EXTKEY).'class.tx_rte_link_records_rtehtmlarea_browse_links.php:&tx_rte_link_records_rtehtmlarea_browse_links';
// Link handler for ch_rterecords backward compatibility
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler']['record'] = t3lib_extMgm::extPath($_EXTKEY).'class.tx_rtelinkrecords_handler.php:&tx_chrterecords_handler';
// Link handler for rte_link_records
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler']['link_record'] = t3lib_extMgm::extPath($_EXTKEY).'class.tx_rtelinkrecords_handler.php:&tx_rtelinkrecords_handler';

if ($ext_config['tsconfig']==='default'){
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:rte_link_records/tsconfig/default.txt">');
}
?>
