<?php

########################################################################
# Extension Manager/Repository config file for ext "gst_topcontent".
#
# Auto generated 29-01-2011 13:29
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Top Content',
	'description' => 'Display (teaser) of the most actual records from tt_content including a link to the original content element. Many easy to use parameters allow individual configuration.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_content',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Pascal Grüttner',
	'author_email' => 'gruettner@gst-im.de',
	'author_company' => 'GST Informationsmanagement GmbH',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.2.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '3.5.0-0.0.0',
			'php' => '3.0.0-0.0.0',
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:18:{s:12:"ext_icon.gif";s:4:"1c4a";s:17:"ext_localconf.php";s:4:"051d";s:15:"ext_php_api.dat";s:4:"51f0";s:14:"ext_tables.php";s:4:"e4aa";s:14:"ext_tables.sql";s:4:"be3d";s:28:"ext_typoscript_constants.txt";s:4:"9e05";s:28:"ext_typoscript_editorcfg.txt";s:4:"f139";s:24:"ext_typoscript_setup.txt";s:4:"e427";s:13:"locallang.php";s:4:"3ea7";s:16:"locallang_db.php";s:4:"9997";s:14:"doc/manual.sxw";s:4:"838d";s:14:"pi1/ce_wiz.gif";s:4:"3c07";s:34:"pi1/class.tx_gsttopcontent_pi1.php";s:4:"52cb";s:42:"pi1/class.tx_gsttopcontent_pi1_wizicon.php";s:4:"15fa";s:13:"pi1/clear.gif";s:4:"cc11";s:31:"pi1/gsttopcontent_template.tmpl";s:4:"43ef";s:17:"pi1/locallang.php";s:4:"0ffd";s:38:"res/gsttopcontent_defaultlink_icon.gif";s:4:"9cd8";}',
	'suggests' => array(
	),
);

?>