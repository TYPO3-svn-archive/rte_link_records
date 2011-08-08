<?php
/*
 * Created on 28.7.2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

class tx_rtelinkrecords_handler {
	function main($linktxt, $conf, $linkHandlerKeyword, $linkHandlerValue, $link_param, &$pObj) {
		$this->pObj=&$pObj;
		$linkHandler=t3lib_div::trimExplode(':', $linkHandlerValue);	
		$className=$linkHandler[0];
		$ext_config=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rte_link_records']);
		if($className && !class_exists($className)){
			foreach(t3lib_div::trimExplode('#####', $ext_config['classNamePaths']) as $row){
				$classConf=t3lib_div::trimExplode('=',$row);
				if($classConf[0]==$className){
					//require_once(PATH_site.$GLOBALS['TYPO3_LOADED_EXT']['tt_news']['siteRelPath'].'pi/class.tx_ttnews.php');
					if(file_exists(PATH_site.str_replace(PATH_site,'',$classConf[1]))){
						require_once(PATH_site.str_replace(PATH_site,'',$classConf[1]));
						break;
					}else{
						return $linktxt;
					}
				}
			}
		}
		$linkToPage=$linkHandler[1];
		$params=array();
		for($i=2;$i<count($linkHandler);$i+=2){$params[$linkHandler[$i]]=$linkHandler[$i+1];}
		$classname_object=t3lib_div::makeInstance($className);
		$classname_object->cObj = $this->pObj;
/*
t3lib_utility_Debug::debug($conf);
*/
		$link_param_array=t3lib_div::trimExplode(' ',$link_param);
		$link_param_array[0]=$classname_object->pi_linkTP_keepPIvars_url($params, 1, 1,$linkToPage);
		unset($conf['parameter.']);
		$conf['parameter']=implode(' ',$link_param_array);
//t3lib_utility_Debug::debug($params);	
		return $this->pObj->typoLink($linktxt, $conf);
	}
}
class tx_chrterecords_handler {
	function main($linktxt, $conf, $linkHandlerKeyword, $linkHandlerValue, $link_param, &$pObj) {
		$this->pObj = &$pObj;
		
		$lconf = array ();
		
		$linkHandlerValue = t3lib_div::trimExplode ( ':', $linkHandlerValue );
		$res = preg_match ( '/(singlePID=)(\d+)/', $link_param, $matches );
		$link_param = preg_replace ( '/singlePID=\d+/', '', $link_param );
		$link_param = t3lib_div::unQuoteFilenames ( $link_param, true );
		
		$localcObj = t3lib_div::makeInstance ( 'tslib_cObj' );
		$recordRow = $this->getRecordRow ( $linkHandlerValue [0], $linkHandlerValue [1] );
		$localcObj->start ( $recordRow, '' );
		
		$linkClass = trim ( $link_param [3] ); // Link class
		if ($linkClass == '-')
			$linkClass = ''; // The '-' character means 'no class'. Necessary in order to specify a title as fourth parameter without setting the target or class!
		$forceTarget = trim ( $link_param [2] ); // Target value
		$forceTitle = trim ( $link_param [4] ); // Title value
		
		if ($forceTarget == '-')
			$forceTarget = ''; // The '-' character means 'no target'. Necessary in order to specify a class as third parameter without setting the target!
		
			// Check, if the target is coded as a JS open window link:
		$JSwindowParts = array ();
		$JSwindowParams = '';
		$onClick = '';
		if ($forceTarget && ereg ( '^([0-9]+)x([0-9]+)(:(.*)|.*)$', $forceTarget, $JSwindowParts )) {
			// Take all pre-configured and inserted parameters and compile parameter list, including width+height:
			$JSwindow_tempParamsArr = t3lib_div::trimExplode ( ',', strtolower ( $conf ['JSwindow_params'] . ',' . $JSwindowParts [4] ), 1 );
			$JSwindow_paramsArr = array ();
			foreach ( $JSwindow_tempParamsArr as $JSv ) {
				list ( $JSp, $JSv ) = explode ( '=', $JSv );
				$JSwindow_paramsArr [$JSp] = $JSp . '=' . $JSv;
			}
			// Add width/height:
			$JSwindow_paramsArr ['width'] = 'width=' . $JSwindowParts [1];
			$JSwindow_paramsArr ['height'] = 'height=' . $JSwindowParts [2];
			
			// Imploding into string:
			$JSwindowParams = implode ( ',', $JSwindow_paramsArr );
			$forceTarget = ''; // Resetting the target since we will use onClick.
		}
		
		if ($forceTitle) {
			$title = $forceTitle;
		}
		
		if ($JSwindowParams) {
			
			// Rendering the tag.
			$finalTagParts ['url'] = $localcObj->lastTypoLinkUrl;
			$finalTagParts ['targetParams'] = $targetPart;
			$finalTagParts ['TYPE'] = 'page';
			
			// Create TARGET-attribute only if the right doctype is used
			if (! t3lib_div::inList ( 'xhtml_strict,xhtml_11,xhtml_2', $GLOBALS ['TSFE']->xhtmlDoctype )) {
				$target = ' target="FEopenLink"';
			} else {
				$target = '';
			}
			
			$lconf ['target'] = $target;
			
			// Title tag
			if ($link_param [4]) {
				$lconf ['title'] = $link_param [4];
			}
			
			// Class
			if ($linkClass) {
				$lconf ['ATagParams'] = 'class=' . $linkClass;
			}
			
			$lconf ['parameter'] = $matches [2];
			$lconf ['additionalParams'] = $link_param [1];
			$lconf ['additionalParams.'] ['insertData'] = 1;
			
			// Rendering the tag.
			$finalTagParts ['url'] = $localcObj->typoLink_URL ( $lconf );
			#                        $finalTagParts['targetParams']=$targetPart;
			$finalTagParts ['TYPE'] = 'page';
			
			$onClick = "vHWin=window.open('" . $GLOBALS ['TSFE']->baseUrlWrap ( $finalTagParts ['url'] ) . "','FEopenLink','" . $JSwindowParams . "');vHWin.focus();return false;";
			$res = '<a href="' . htmlspecialchars ( $finalTagParts ['url'] ) . '"' . $target . ' onclick="' . htmlspecialchars ( $onClick ) . '"' . ($title ? ' title="' . $title . '"' : '') . ($linkClass ? ' class="' . $linkClass . '"' : '') . $finalTagParts ['aTagParams'] . '>';
			
			if ($lconf ['ATagBeforeWrap']) {
				return $res . $localcObj->wrap ( $linktxt, $lconf ['wrap'] ) . '</a>';
			} else {
				return $localcObj->wrap ( $res . $linktxt . '</a>', $lconf ['wrap'] );
			}
		
		}
		
		// Internal target:
		if ($link_param [2] != '-') {
			$lconf ['target'] = $link_param [2];
		}
		
		// Title tag
		if ($link_param [4]) {
			$lconf ['title'] = $link_param [4];
		}
		
		// Class
		if ($linkClass) {
			$lconf ['ATagParams'] = 'class=' . $linkClass;
		}
		
		$lconf ['parameter'] = $matches [2];
		$lconf ['additionalParams'] = $link_param [1];
		$lconf ['additionalParams.'] ['insertData'] = 1;
		
		return $localcObj->typoLink ( $linktxt, $lconf );
	
	}
	function getRecordRow($table,$uid) {
		return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, 'uid='.intval($uid).$this->pObj->enableFields($table), '', ''));
	}
}
?>