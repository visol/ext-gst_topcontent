<?php
if (!defined("TYPO3_MODE")) die ("Access denied.");


$tempColumns = Array(
	"tx_gsttopcontent_abstract" => Array(
		"exclude" => 0,
		"label" => "LLL:EXT:gst_topcontent/Resources/Private/Language/locallang_db.xlf:tt_content.tx_gsttopcontent_abstract",
		"config" => Array(
			"type" => "text",
			"cols" => "60",
			"rows" => "4",
		)
	),
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("tt_content", $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes("tt_content", "tx_gsttopcontent_abstract;;;;1-1-1");


$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY . "_pi1"] = "layout,select_key";


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(Array("LLL:EXT:gst_topcontent/Resources/Private/Language/locallang_db.xlf:tt_content.list_type", $_EXTKEY . "_pi1"), "list_type");


if (TYPO3_MODE == "BE") $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_gsttopcontent_pi1_wizicon"] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . "pi1/class.tx_gsttopcontent_pi1_wizicon.php";


?>