<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003 Dr. Pascal Gr�ttner (gruettner@gst-im.de)
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 *
 * Plugin 'Top Content' for the 'gst_topcontent' extension.
 *
 * @author    Dr. Pascal Gr�ttner <gruettner@gst-im.de> GST Informationsmanagement GmbH
 *
 */
class tx_gsttopcontent_pi1 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	var $cObj; // The backReference to the mother cObj object set at call time
	var $prefixId = 'tx_gsttopcontent_pi1'; // Same as class name
	var $scriptRelPath = 'pi1/class.tx_gsttopcontent_pi1.php'; // Path to this script relative to the extension dir.
	var $extKey = 'gst_topcontent'; // The extension key.
	var $templateCode; // HTML template content.
	var $deflinkIcon = 'gsttopcontent_defaultlink_icon.gif'; // Default icon for links.
	var $defTemplate = 'gsttopcontent_template.tmpl'; // Default template file.
	var $sqlLimitString = ''; // LIMIT part of the sql clause.
	var $browseFromTo = ''; // This displays 'm to n results of p'.
	var $browseLinks = ''; // This links to the (other) resultpages.


	/**
	 * Main function for plugin 'Top Content'.
	 *
	 * @param    string $content : content
	 * @param    array $conf : constants
	 * @return    string        content
	 */
	function main($content, $conf) {

		$GLOBALS['TSFE']->set_no_cache();

		// *********************************
		// *** Get the configuration values:
		// *********************************
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// Get the general settings.
		$this->config['startPid'] = GeneralUtility::intExplode(',', trim($this->conf['startPid']));
		$this->config['excludePids'] = GeneralUtility::intExplode(',', trim($this->conf['excludePids']));
		$this->config['excludePidsR'] = GeneralUtility::intExplode(',', trim($this->conf['excludePidsR']));
		$this->config['maxRecs'] = MathUtility::forceIntegerInRange($conf['maxRecs'], 0, 200);
		$this->config['ppRecs'] = MathUtility::forceIntegerInRange($conf['ppRecs'], 0, 200);
		$this->config['offsetRecs'] = MathUtility::forceIntegerInRange($conf['offsetRecs'], 0, 200);
		$this->config['allowedDokTypes'] = GeneralUtility::intExplode(',', trim($this->conf['allowedDokTypes']));
		$this->config['maxTextLength'] = MathUtility::forceIntegerInRange($conf['maxTextLength'], 0, 500);
		$this->config['andGotoNext'] = substr(trim($this->conf['andGotoNext']), 1, 1);
		$this->config['showTextEnd'] = trim(htmlspecialchars($this->conf['showTextEnd']));
		$this->config['showRecImg'] = trim($this->conf['showRecImg']);
		// Note: With imgTagAdd don't use htmlspecialchars, because "" may be required.
		$this->config['imgTagAdd'] = trim(strip_tags($this->conf['imgTagAdd']));
		$this->config['linkPageTitle'] = MathUtility::forceIntegerInRange($conf['linkPageTitle'], 0, 1);
		$this->config['linkTarget'] = trim(htmlspecialchars($this->conf['linkTarget']));
		// Note: With ATagParams don't use htmlspecialchars, because "" may be required.
		$this->config['ATagParams'] = trim(strip_tags($this->conf['ATagParams']));

		// Get the language settings.
		$this->config['dateFormat'] = trim($this->pi_getLL('dateFormat'));

		// Get the template settings.
		$this->config['templateFile'] = trim($this->conf['templateFile']);

		// Get the sql settings.
		$this->config['useTeaserAndBodytext'] = MathUtility::forceIntegerInRange($conf['useTeaserAndBodytext'], 0, 1);
		$this->config['onlyOnePerPage'] = MathUtility::forceIntegerInRange($conf['onlyOnePerPage'], 0, 1);
		$this->config['sqlOrderBy'] = trim($this->conf['sqlOrderBy']);
		$this->config['sqlTstampField'] = trim($this->conf['sqlTstampField']);
		$this->config['sqlTeaserField'] = trim($this->conf['sqlTeaserField']);
		$this->config['sqlAndWhere'] = trim($this->conf['sqlAndWhere']);

		// Get the format settings.
		$this->config['classTable'] = trim($this->conf['classTable']);
		$this->config['classTdTstamp'] = trim($this->conf['classTdTstamp']);
		$this->config['classTdHeader'] = trim($this->conf['classTdHeader']);
		$this->config['classTdLink'] = trim($this->conf['classTdLink']);
		$this->config['classTdBodytext'] = trim($this->conf['classTdBodytext']);
		$this->config['classTdBrowse'] = trim($this->conf['classTdBrowse']);

		// Get the standard wrap settings.
		$this->config['tstamp_stdWrap.'] = $this->conf['tstamp_stdWrap.'];
		$this->config['pagetitle_stdWrap.'] = $this->conf['pagetitle_stdWrap.'];
		$this->config['header_stdWrap.'] = $this->conf['header_stdWrap.'];
		$this->config['teaser_stdWrap.'] = $this->conf['teaser_stdWrap.'];
		$this->config['link_stdWrap.'] = $this->conf['link_stdWrap.'];

		// **************************
		// *** Get the HTML template:
		// **************************
		if (strlen($this->config['templateFile']) < 1) $this->config['templateFile'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey) . 'pi1/' . $this->defTemplate;
		$this->templateCode = $this->cObj->fileResource($this->config['templateFile']);
		// Get the subparts from the HTML template.
		if ($this->templateCode) {
			// Get the main table.
			$t = array();
			$t['total'] = $this->cObj->getSubpart($this->templateCode, '###TOPCONTENT_TABLE###');
			$t['tabitem'] = $this->cObj->getSubpart($t['total'], '###TABLE_ITEM###');
			$t['contitem'] = $this->cObj->getSubpart($t['total'], '###CONTENT_ITEM###');
			$t['browseitem'] = $this->cObj->getSubpart($t['total'], '###BROWSE_ITEM###');
		} else {
// ### For dubug purposes only ###: debug('No template code found!');
		}

		// *************************************
		// *** A startingpoint is really needed.:
		// *************************************
		// Collect all startingpoints.
		$startingpoints = array();
		$startingpoints[0] = $GLOBALS['TSFE']->id;


		// *********************************************************
		// *** The constant 'startPid' overrides the pluginlocation:
		// *********************************************************
		// If the variable is set, get all startingspoints that are included in that list.
		$countstartpids = count($this->config['startPid']);
		if ($countstartpids > 0 && $this->config['startPid'][0] > 0) {
			$startingpoints = array();
			// Go through all elements in the comma-separated list.
			for ($i = 0; $i < $countstartpids; $i++) {
				$startingpoints[$i] = $this->config['startPid'][$i];
			} // end for
		} // end if


		// *******************************************************************
		// *** Finally the startingpoint of the plugin overrides actual value:
		// *******************************************************************
		$pluginstartingpoints = array();
		$pluginstartingpoints = GeneralUtility::intExplode(',', $this->cObj->data['pages']);
		$countstartpids = count($pluginstartingpoints);
		if ($countstartpids > 0 && $pluginstartingpoints[0] > 0) {
			$startingpoints = array();
			// Go through all elements in the BLOB.
			for ($i = 0; $i < $countstartpids; $i++) {
				$startingpoints[$i] = $pluginstartingpoints[$i];
			} // end for
		} // end if


		// *******************************************
		// *** Two of the sql parameters are required:
		// *******************************************
		// If order by is not given, choose the tstamp.
		if (strlen($this->config['sqlOrderBy']) < 3) $this->config['sqlOrderBy'] = 'tt_content.tstamp DESC';
		// If a field for timestamp is not given, choose the tstamp.
		if (strlen($this->config['sqlTstampField']) < 3) $this->config['sqlTstampField'] = 'tt_content.tstamp';
		// If a field for the teaser text is not given, choose the bodytext.
		if (strlen($this->config['sqlTeaserField']) < 3) $this->config['sqlTeaserField'] = 'tt_content.bodytext';


		// ********************************************************
		// *** The tablename is required to avoid field mismatches:
		// ********************************************************
		if (!stristr($this->config['sqlTstampField'], '.')) $this->config['sqlTstampField'] = 'tt_content.' . $this->config['sqlTstampField'];
		if (!stristr($this->config['sqlTeaserField'], '.')) $this->config['sqlTeaserField'] = 'tt_content.' . $this->config['sqlTeaserField'];


		// ************************************************************
		// *** Select all pids that should be excluded from the plugin:
		// ************************************************************
		// Go through all pages that should be excluded recursively.
		while (list($k, $v) = each($this->config['excludePidsR'])) {
			if ($v > 0) {
				$excl_pids = array();
				$excl_pids = $this->GetAllPages($v);
				// Merge the result with the other pages array. (Note: \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge is not used here, because it loses values.)
				$this->config['excludePids'] = array_merge($this->config['excludePids'], $excl_pids);
			} // end if
		} // end while
		unset($excl_pids);
		reset($this->config['excludePids']);


		// *******************************************************************
		// *** Get the list of pids from where to select the content elements:
		// *******************************************************************
		$in_pids = array();
		reset($startingpoints);
		// Go through the startingpoints and collect pids.
		while (list($k, $v) = each($startingpoints)) {
			// Get pids from each startingpoint.
			$tmp_pids = array();
			$tmp_pids = $this->GetAllPages($v);
			// Merge results together.
			$in_pids = array_merge($in_pids, $tmp_pids);
		} // end while
		$in_pids = $this->RmvExcludedPages($in_pids, $this->config['excludePids']);
		$in_pid_list = implode(',', $in_pids);
		unset($in_pids);
		unset($this->config['excludePids']);
		unset($this->config['excludePidsR']);
		unset($this->config['allowedDokTypes']);

// ### For dubug purposes only ###: debug($in_pid_list);


		// ****************************
		// *** Check the teaser column:
		// ****************************
		$query = '
			SELECT ' . addslashes($this->config['sqlTeaserField']) . ' AS teaserfield
			FROM tt_content
			WHERE 1
			';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(addslashes($this->config['sqlTeaserField']), 'tt_content', 1);

		// If teaser field is not existing (i.e. named wrong), use bodytext field instead.
		if (!$res) $this->config['sqlTeaserField'] = 'tt_content.bodytext';

		// *********************
		// *** Do the sql query:
		// *********************
		$this->sqlLimitString = (0 + $this->config['offsetRecs']) . ',' . $this->config['maxRecs'];
		$syslanguage = MathUtility::forceIntegerInRange($GLOBALS['TSFE']->sys_language_uid, 0);

		$res_select = 'tt_content.uid AS uid, tt_content.pid AS pid, pages.title AS title, tt_content.header AS header, tt_content.bodytext AS bodytext, ' . addslashes($this->config['sqlTeaserField']) . ' AS teaserfield, ' . addslashes($this->config['sqlTstampField']) . ' AS tstamp';
		$res_from = 'tt_content' . ' INNER JOIN pages ON pages.uid = tt_content.pid';
		$res_where = 'tt_content.pid in (' . $in_pid_list . ') AND tt_content.sys_language_uid = ' . $syslanguage . ' ' . $this->config['sqlAndWhere'] . ' ' . $this->cObj->enableFields('tt_content');
		$res_orderby = addslashes($this->config['sqlOrderBy']);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($res_select, $res_from, $res_where, '', $res_orderby, $this->sqlLimitString);
		$resultdata = $this->GetPageDependendData($res, $this->config['onlyOnePerPage']);
		$rows = count($resultdata);


		// ***********************************************************
		// *** Do the sql query to determine the result split options:
		// ***********************************************************
		if ($this->config['ppRecs'] > 0 && $rows > 0) {
			// Do the page splitting.
			$ppGoto = 'pp' . $this->extKey . $this->cObj->data['uid'];
			$page_goto = MathUtility::forceIntegerInRange(GeneralUtility::_GP($ppGoto), 1);
			$startindex = $this->RecSplitter($rows, $this->config['ppRecs'], $this->config['maxRecs'], $page_goto);
//			$res = mysql_query($query . $this->sqlLimitString, TYPO3_db);
//			if($res) $rows = mysql_num_rows($res);
//			else $rows = 0;
			$resultdata = array_slice($resultdata, $startindex, $this->config['ppRecs']);

			// Format the page browser.
			$markerArray = array();
			$markerArray['###BROWSELINKS###'] = $this->browseLinks;
			$markerArray['###BROWSEFROMTO###'] = $this->browseFromTo;
			$markerArray['###CLASSTDBROWSE###'] = $this->config['classTdBrowse'];
			$browse_row = $this->cObj->substituteMarkerArrayCached($t['browseitem'], $markerArray, array(), array());
		} else {
			$browse_row = '';
		} // end if


		// **************************************
		// *** Format the beginning of the table:
		// **************************************
		$markerArray = array();
		$markerArray['###CLASSTABLE###'] = $this->config['classTable'];
		$table_row = $this->cObj->substituteMarkerArrayCached($t['tabitem'], $markerArray, array(), array());


		// *************************************
		// *** Get and format the query results:
		// *************************************
		$content_row = '';
		$markerArray = array();
		$markerArray['###CLASSTDTSTAMP###'] = $this->config['classTdTstamp'];
		$markerArray['###CLASSTDHEADER###'] = $this->config['classTdHeader'];
		$markerArray['###CLASSTDLINK###'] = $this->config['classTdLink'];
		$markerArray['###CLASSTDBODYTEXT###'] = $this->config['classTdBodytext'];
//if($rows) {
		if (count($resultdata > 0)) {
			//while($row = mysql_fetch_array($res))  {
			while (list($key, $row) = each($resultdata)) {
				// Datetime information.
				$markerArray['###TSTAMP###'] = $this->RenderTstamp($row['tstamp']);
				// Content pagetitle.
				if (strlen($row['title']) > 0) {
					$markerArray['###PAGETITLE###'] = $this->RenderPageTitle($row['title']);
					// Link to page if option is set.
					if ($this->config['linkPageTitle']) {
						$markerArray['###PAGETITLE###'] = $this->pi_linkToPage($markerArray['###PAGETITLE###'], $row['pid'], $this->config['linkTarget']);
						// Add additional <A>-tag parameters.
						$markerArray['###PAGETITLE###'] = $this->AddATagParams($markerArray['###PAGETITLE###']);
					} // end if
				} else {
					$markerArray['###PAGETITLE###'] = $this->RenderPageTitle(htmlspecialchars($this->pi_getLL('msgNoPageTitle')));
				} // end if
				// Content header.
				if (strlen($row['header']) > 0) $markerArray['###HEADER###'] = $this->RenderHeader($row['header']);
				else $markerArray['###HEADER###'] = $this->RenderHeader(htmlspecialchars($this->pi_getLL('msgNoTitle')));
				// Link to content element.
				$markerArray['###LINK###'] = $this->RenderLink($row['pid'], $row['uid'], $this->config['linkTarget']);
				// Get teasertext of the content.
				if (strlen($row['teaserfield']) > 0) {
					$teasertext = $row['teaserfield'];
				} else {
					if ($this->config['useTeaserAndBodytext']) {
						$teasertext = $row['bodytext'];
					} // end if
				} // end if
				// Render the teasertext, if field is not empty.
				if (strlen($teasertext) > 0) {
					// Remember maximum text length.
					$maxlength = $this->config['maxTextLength'];
					// Rest of the teaserfield (part above maxTextLength).
					$searchteaserpart = substr($teasertext, $maxlength);
					// In the rest of the teaserfield the position of the first searchcharacter (andGotoNext) is located.
					$searchpos = strpos($searchteaserpart, $this->config['andGotoNext']);
					// If character was found the maxTextLength is augmented by its position (note: position 0 is a special case that requires ===)..
					if ($searchpos || $searchpos === 0) $maxlength = $maxlength + $searchpos + 1;
					// Crop teaserfield.
					$teasertemp = substr($teasertext, 0, $maxlength);
					// Render teaserfield.
					$markerArray['###BODYTEXT###'] = $this->RenderTeaser($teasertemp . $this->config['showTextEnd']);
				} else {
					$markerArray['###BODYTEXT###'] = $this->RenderTeaser(htmlspecialchars($this->pi_getLL('msgNoBodytext')));
				} // end if
				// Add new item to $content_row.
				$content_row .= $this->cObj->substituteMarkerArrayCached($t['contitem'], $markerArray, array(), array());
			} // end while

		} else {
			// No records found: show message.
			$markerArray['###TSTAMP###'] = '';
			$markerArray['###PAGETITLE###'] = '';
			$markerArray['###HEADER###'] = $this->RenderHeader(htmlspecialchars($this->pi_getLL('msgNoRecords')));
			$markerArray['###LINK###'] = '';
			$markerArray['###BODYTEXT###'] = '';
			$content_row = $this->cObj->substituteMarkerArrayCached($t['contitem'], $markerArray, array(), array());
		} // end if


		// ******************
		// *** Format output:
		// ******************
		$subpartArray = array();
		$subpartArray['###CONTENT###'] = $content_row . $browse_row;
		$subpartArray['###TABLE###'] = $table_row;
		$content .= $this->cObj->substituteMarkerArrayCached($t['total'], array(), $subpartArray, array());

		// ******************
		// *** Return output:
		// ******************
		return $this->pi_wrapInBaseClass($content);

	} // end main


	/**
	 * GetAllPages
	 * Get all pages from the "pid_start" downwards by using recursive function calls.
	 *
	 * @param    integer $pid_start : pid to start from
	 * @return    array        list of pids representing the page tree from $pid_start on
	 */
	function GetAllPages($pid_start) {

		$result = array();
		// Check the page and add to result, if allowed by GetSinglePage().
		$pid = $this->GetSinglePage($pid_start);
		if ($pid > 0) array_push($result, $pid);
		// Get the array of subpages of the page.
		$pids_check = array();
		$pids_check = $GLOBALS['TSFE']->sys_page->getMenu($pid_start);
		// Go through the array of subpages.
		for ($i = 0; $i < count($pids_check); $i++) {
			while (list($k, $v) = each($pids_check)) {
				// Recursive call for each subpage. (Note: \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge is not used here because it breaks the recursive function call.)
				$result = array_merge($result, $this->GetAllPages($k));
			} // end while
		} // end for

		reset($result);
		return $result;

	} // end GetAllPages


	/**
	 * GetSinglePage
	 * Get a single page.
	 *
	 * @param    integer $pid : pid of the page
	 * @return    integer        If 'doktype'-check has been ok, the pid will be returned. Else the result is 0.
	 */
	function GetSinglePage($pid) {

		$result = $pid;
		$pids_check = array();
		$pids_check = $GLOBALS['TSFE']->sys_page->getPage($pid);
		// If the 'doktype's-array is filled with real values, do the 'doktype'-comparison.
		if ($this->config['allowedDokTypes'][0] > 0) {
			// Check the 'doktype' of the page and return pid if 'doktype' matches allowed 'doktype's.
			if (!in_array($pids_check['doktype'], $this->config['allowedDokTypes'])) $result = 0;
		} // end if
		return $result;

	} // end GetSinglePage


	/**
	 * RmvExcludedPages
	 * Remove pages from $pids_all that are listetd in $pids_exclude.
	 *
	 * @param    array $pids_all : complete pid list
	 * @param    array $pids_exclude : pids to exclude from class result
	 * @return    array        list of pids reduced by $pids_exclude
	 */
	function RmvExcludedPages($pids_all, $pids_exclude) {

		$result = array();

		// Go throug the array of excluded pages:
		while (list($k, $v) = each($pids_exclude)) {
			$key_check = array_search($v, $pids_all);
			// If value (pid) was found, remove it from array:
			if (strlen($key_check) > 0) unset($pids_all[$key_check]);
		} // end while

		reset($pids_all);
		$result = $pids_all;
		return $result;

	} // end RmvExcludedPages


	/**
	 * GetPageDependendData
	 * Get all data or only one record per page, depending on the users choice.
	 *
	 * @param        reference $ref_res : reference to the SQL query result ID
	 * @param        boolean $only_one_perpage : if set (1) only one content record per page will be fetched
	 * @return    array                result records
	 */
	function  GetPageDependendData(&$ref_res, $only_one_perpage) {

		$resultdata = array();
		// Check if only one record per page shall be included into the result.
		if ($only_one_perpage > 0) {
			// Go through the complete query result.
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($ref_res)) {
				// Do only add the first element of each page (=array-key). So we get this element according to the SQL parameters (sortorder etc.).
				if (!array_key_exists($row['pid'], $resultdata)) {
					$resultdata[$row['pid']] = $row;
				} // end if
			} // end while
			// Normal case: get all the result records of the query.
		} else {
			$rowcount = 0;
			// Go through the complete query result.
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($ref_res)) {
				// Add each element.
				$resultdata[$rowcount] = $row;
				$rowcount++;
			} // end while
		} // end if

		return $resultdata;

	} // end GetPageDependendData


	/**
	 * RecSplitter
	 * Split the result into blocks of $recs_pp per result page and
	 * return a HTML formated link to all result blocks.
	 *
	 * @param        integer $rec_count : recordcount (all records)
	 * @param        integer $recs_pp : display $recs_pp records per page
	 * @param        integer $recs_max : maximum amount of displayed records
	 * @param        integer $act_pagenumber : number of the result page to display
	 * @return    integer        the startindex, also sets some class properties ($this->browseLinks, $this->browseFromTo)
	 */
	function RecSplitter($rec_count, $recs_pp, $recs_max, $act_pagenumber = 1) {

		// Get language dependend variables.
		$ll_pagename = htmlspecialchars($this->pi_getLL('browsePage'));
		$ll_pageseparator = htmlspecialchars($this->pi_getLL('browseSeperator'));
		$ll_pageactwrap = htmlspecialchars($this->pi_getLL('browseActPageWrap'));
		$ll_pagefromto = htmlspecialchars($this->pi_getLL('browseFromTo'));

		// Split the recordset into blocks of $recs_pp.
		$page_count = $rec_count / $recs_pp;
		// Non-integer results have to converted to integer. (The modulo-value represents a package of less than $recs_pp datalines.)
		if (!is_int($page_count)) $page_count = intval($page_count) + 1;

		// There could be less records than maximum records.
		if ($rec_count < $recs_max) $recs_max = $rec_count;
		// Get the from/to values and redefine the LIMIT part of the sql query.
		$recs_from = $recs_pp * ($act_pagenumber - 1) + 1;
		$recs_to = $recs_pp * $act_pagenumber;
		// The record count of the last page sometimes has to be recalculated.
		if ($recs_to > $recs_max) {
			$recs_pp = $recs_pp - ($recs_to - $recs_max);
			$recs_to = $recs_max;
		} // end if
		$startindex = ($recs_from - 1) + $this->config['offsetRecs'];
		$this->sqlLimitString = 'LIMIT ' . $startindex . ',' . $recs_pp;

		// Format the from/to part of the HTML output.
		$this->browseFromTo = str_replace('###FROM###', $recs_from, str_replace('###TO###', $recs_to, str_replace('###COUNT###', $recs_max, $ll_pagefromto)));

		// Go through all split pages.
		$this->browseLinks = '';
		for ($i = 0; $i < $page_count; $i++) {
			// Build the raw link text.
			$page_number = $i + 1;
			$linktext = $ll_pagename . $page_number;
			// Start generating link.
			$linkto = 'index.php?';
			// Get link arguments that are actually used.
			$requesturl = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
			// If a query part can be found, this part is saved here.
			if (stristr($requesturl, '?')) {
				$requesturl = substr($requesturl, strpos($requesturl, '?') + 1);
			} else {
				$requesturl = '';
			} // end if
			// If plugins actual link is already present in the REQUEST_URL, do remove this part.
			if ($requesturl) {
				// The plugins string is always the last within the query parameters.
				$regsearchstring = '/pp' . $this->extKey . '.+/';
				$requesturl = preg_replace($regsearchstring, "", $requesturl);
			} // end if
			// If still arguments left (from other plugins or Typo3), add them to the link.
			if ($requesturl) $linkto .= $requesturl . '&';
			// Add link arguments required for the plugin.
			$linkto .= 'pp' . $this->extKey . $this->cObj->data['uid'] . '=' . $page_number . '&id=' . $this->cObj->data['pid'] . '#' . $this->cObj->data['uid'];
			// Generate link.
			$linkto = str_replace("&&", "&", $linkto);
			$linktext = $this->pi_linkToPage($linktext, $linkto);
			// If the page is the actual page, it can be wrapped with some other text (e.g. >>link<<).
			if ($page_number == $act_pagenumber) $linktext = str_replace('|', $linktext, $ll_pageactwrap);
			// Add the separator.
			$this->browseLinks .= $linktext . $ll_pageseparator;
		} // end for

		// The last separator has to be cropped.
		$this->browseLinks = substr($this->browseLinks, 0, strlen($this->browseLinks) - strlen($ll_pageseparator));
		// Return the startindex.
		return $startindex;

	} // end RecSplitter


	/**
	 * RenderTstamp
	 * Format the timestamp of one content element.
	 *
	 * @param    integer $tstamp : timestamp value from tt_content
	 * @return    string        rendered timestamp
	 */
	function RenderTstamp($tstamp) {

		// Avoid problems that may be caused by special characters that may have another meaning/function in HTML.
		$tstamp = htmlspecialchars($tstamp);
		// Format tstamp with strftime.
		$tstamp = strftime($this->config['dateFormat'], $tstamp);
		// Add the standard wrap properties.
		if (is_array($this->config['tstamp_stdWrap.'])) {
			$tstamp = $this->cObj->stdWrap($tstamp, $this->config['tstamp_stdWrap.']);
		} // end if
		return $tstamp;

	} // end RenderTstamp


	/**
	 * RenderPageTitle
	 * Format the pagetitle of one content element.
	 *
	 * @param    string $pagetitle : value of the field "title" in pages
	 * @return    string        rendered pagetitle
	 */
	function RenderPageTitle($pagetitle) {

		// Avoid problems that may be caused by special characters that may have another meaning/function in HTML.
		$pagetitle = htmlspecialchars($pagetitle);
		// Add the standard wrap properties.
		if (is_array($this->config['pagetitle_stdWrap.'])) {
			$pagetitle = $this->cObj->stdWrap($pagetitle, $this->config['pagetitle_stdWrap.']);
		} // end if
		return $pagetitle;

	} // end RenderPageTitle


	/**
	 * RenderHeader
	 * Format the headline of one content element.
	 *
	 * @param    string $header : value of the field "header" in tt_content
	 * @return    string        rendered header
	 */
	function RenderHeader($header) {

		// Avoid problems that may be caused by special characters that may have another meaning/function in HTML.
		$header = htmlspecialchars($header);
		// Add the standard wrap properties.
		if (is_array($this->config['header_stdWrap.'])) {
			$header = $this->cObj->stdWrap($header, $this->config['header_stdWrap.']);
		} // end if
		return $header;

	} // end RenderHeader


	/**
	 * RenderLink
	 * Format the link to one content element.
	 *
	 * @param    integer $pid : uid of the actual record
	 * @param    integer $uid : pid of the actual record
	 * @param    string $target : target for the link
	 * @return    string        rendered link
	 */
	function RenderLink($pid, $uid = 0, $target = '') {

		if (strlen($this->config['showRecImg']) > 4) {
			if ($this->config['showRecImg'] == $this->deflinkIcon) {
				$linkpath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey) . 'res/';
			} // end if
			$linkicon = $linkpath . $this->config['showRecImg'];
			// Finish image tag.
			$linkicon = '<img src="' . $linkicon . '" ' . $this->config['imgTagAdd'] . ' />';
			$contlink = $linkicon;
		} // end if
		$contlink = htmlspecialchars($this->pi_getLL('showRecText')) . '&nbsp;' . $linkicon;

		// Add the reference to content (if present) to pagelink. A variable, contUid, wil lbe added to the query string. Refer to the documentation to see an example of how to use contUid.
		$contlink = $this->pi_linkToPage($contlink, $pid, $target, array('contUid' => $uid));
		// Add additional <A>-tag parameters.
		$contlink = $this->AddATagParams($contlink);
		// Add the standard wrap properties.
		if (is_array($this->config['link_stdWrap.'])) {
			$contlink = $this->cObj->stdWrap($contlink, $this->config['link_stdWrap.']);
		} // end if

		// Return link.
		return $contlink;

	} // end RenderLink


	/**
	 * RenderTeaser
	 * Format the teaserfield of one content element.
	 *
	 * @param    string $teaserfield : value of the field "teaserfield" in tt_content
	 * @return    string        rendered teaserfield
	 */
	function RenderTeaser($teaserfield) {

		// Strip HTML/PHP-tags.
		$teaserfield = strip_tags($teaserfield);
		// Avoid problems that may be caused by special characters that may have another meaning/function in HTML.
		$teaserfield = htmlspecialchars($teaserfield);
		// After that we got a problem with &nbsp; which will then be '&amp;amp;nbsp;'. This has to be reconverted.
		$teaserfield = preg_replace('/&amp;amp;nbsp;/', '&nbsp;', $teaserfield);
		// Add the standard wrap properties.
		if (is_array($this->config['teaser_stdWrap.'])) {
			$teaserfield = $this->cObj->stdWrap($teaserfield, $this->config['teaser_stdWrap.']);
		} // end if
		return $teaserfield;

	} // end RenderTeaser


	/**
	 * AddATagParams
	 * Add <A<-tag parameters to link.
	 *
	 * @param    string $link : HTML link (complete <A>-tag)
	 * @return    string        <A>-tag with additional parameters
	 */
	function AddATagParams($link) {

		if (strlen($this->config['ATagParams']) > 1) {
			// Replace the inner part of the <A>-tag: Preserve it (\1) and add the ATagParams.
			$link = preg_replace('/<[aA]([^>]*)>/', '<A\1 ' . $this->config['ATagParams'] . '>', $link);
		} // end if
		return $link;

	} // end AddATagParams

}

?>