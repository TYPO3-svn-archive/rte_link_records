<?php

require_once (PATH_t3lib.'interfaces/interface.t3lib_browselinkshook.php');

class tx_rte_link_records_rtehtmlarea_browse_links implements t3lib_browseLinksHook {
	
	protected $invokingObject;
	protected $conf;
	protected $mode;
	protected $act;
	protected $bparams;
	protected $expandPage;
	protected $isEnabled = FALSE;
	
	/**
	 * initializes the hook object
	 *
	 * @param	browse_links	parent browse_links object
	 * @param	array		additional parameters
	 * @return	void
	 */
	public function init($parentObject, $additionalParameters) {
		$invokingObjectClass = get_class($parentObject);
		if (((string) $parentObject->mode == 'rte') && ($invokingObjectClass == 'tx_rtehtmlarea_browse_links' || $invokingObjectClass == 'ux_tx_rtehtmlarea_browse_links')){
			$this->isEnabled = TRUE;
			$this->invokingObject =& $parentObject;
			$this->mode =& $this->invokingObject->mode;
			$this->act =& $this->invokingObject->act;
			$this->bparams =& $this->invokingObject->bparams;
			$this->expandPage = intval($_GET['expandPage']);
			$TSconfig = t3lib_BEfunc::getPagesTSconfig($this->expandPage);
			$this->conf = $TSconfig['rte_link_records.'];
			if ($this->isEnabled) {
				$this->invokingObject->anchorTypes[] = 'link_record';
//t3lib_utility_Debug::debug($_GET['expandPage']);				
			}
			//$GLOBALS['LANG']->includeLLFile('EXT:rte_link_records/locallang.xml');
		}
		
	}
	/**
	 * Adds new items to the currently allowed ones and returns them
	 * Replaces the 'file' item with the 'media' item
	 * Adds DAM upload tab
	 *
	 * @param	array	currently allowed items
	 * @return	array	currently allowed items plus added items
	 */
	public function addAllowedItems($currentlyAllowedItems) {
		$allowedItems =& $currentlyAllowedItems;
		if ($this->isEnabled) {
			$allowedItems[] = 'link_record';
			// Excluding items based on Page TSConfig
			$allowedItems = array_diff($allowedItems, t3lib_div::trimExplode(',',$this->browserRenderObj->modPageConfig['properties']['removeTabs'],1));
		}
		return $allowedItems;
	}
	/**
	 * Modifies the menu definition and returns it
	 * Adds definition of the 'media' menu item
	 *
	 * @param	array	menu definition
	 * @return	array	modified menu definition
	 */
	public function modifyMenuDefinition($menuDefinition) {
//t3lib_utility_Debug::debug($menuDefinition);			
		if ($this->isEnabled && in_array('link_record', $this->invokingObject->allowedItems)) {
			$menuDefinition['link_record']['isActive'] = $this->invokingObject->act == 'link_record';
			
			if($tab_name=$GLOBALS['BE_USER']->userTS['rte_link_records.']['tab_name']){
				$menuDefinition['link_record']['label'] = $tab_name;	
			}else{
				$menuDefinition['link_record']['label'] = $GLOBALS['LANG']->sL('LLL:EXT:rte_link_records/locallang.xml:default_tab_name',1);
			}
			$menuDefinition['link_record']['url'] = '#';
			$expandPage = '';
			if($GLOBALS['BE_USER']->userTS['rte_link_records.']['defaultExpandPage']){
				$expandPage = '&expandPage='.$GLOBALS['BE_USER']->userTS['rte_link_records.']['defaultExpandPage'];
			}
			$menuDefinition['link_record']['addParams'] = 'onclick="jumpToUrl(\''.htmlspecialchars('?act=link_record&mode='.$this->mode.$expandPage).'\');return false;"';
		}		
		return $menuDefinition;
	}
	/**
	 * Returns a new tab for the browse links wizard
	 * Returns the 'media' tab to the RTE link browser
	 *
	 * @param	string		current link selector action
	 * @return	string		a tab for the selected link action
	 */
	public function getTab($linkSelectorAction) {
		if ($this->isEnabled) {
			switch ($linkSelectorAction) {
				case 'link_record':
//t3lib_utility_Debug::debug($this->invokingObject->expandPage);
									
					$pagetree = t3lib_div::makeInstance('TBE_PageTree');
					//$pagetree->script='browse_links.php';
					$pagetree->ext_showPageId = $GLOBALS['BE_USER']->getTSConfigVal('options.pageTree.showPageIdWithTitle');
					$pagetree->ext_showNavTitle = $GLOBALS['BE_USER']->getTSConfigVal('options.pageTree.showNavTitle');
					$pagetree->addField('nav_title');
					$pagetree->ext_pArrPages = 0; 
					
					$tables=array();
					foreach($this->conf as $table_k=>$table_conf){
						$tables[]=str_replace('.','',$table_k);
					}
					
					
//t3lib_utility_Debug::debug($tables);				
					$dbmount='';
					// Outputting Temporary DB mount notice:
					if (intval($GLOBALS['BE_USER']->getSessionData('pageTree_temporaryMountPoint')))	{
						$link = '<a href="' . htmlspecialchars(t3lib_div::linkThisScript(array('setTempDBmount' => 0))) . '">' .
											$GLOBALS['LANG']->sl('LLL:EXT:lang/locallang_core.xml:labels.temporaryDBmount', 1) .
										'</a>';
						$flashMessage = t3lib_div::makeInstance('t3lib_FlashMessage',$link,'',t3lib_FlashMessage::INFO);
						$dbmount = $flashMessage->render();
					}
					//if(t3lib_div::testInt($this->expandPage) && $GLOBALS['BE_USER']->isInWebMount($this->expandPage)){
					if(t3lib_div::testInt($this->expandPage)){
//record:tt_news:18621 singlePID=336&amp;tx_ttnews[tt_news]={field:uid}&amp;tx_ttnews[mode]=single&amp;tx_ttnews[backPID]=24&amp;no_cache=1						
						$this->invokingObject->doc->JScodeArray[] = 'function link_record(theLink){							                 
							if (document.ltargetform.anchor_title) browse_links_setTitle(document.ltargetform.anchor_title.value);
							if (document.ltargetform.anchor_class) browse_links_setClass(document.ltargetform.anchor_class.value);
							if (document.ltargetform.ltarget) browse_links_setTarget(document.ltargetform.ltarget.value);
							plugin.createLink(theLink,cur_target,cur_class,cur_title,additionalValues);
							return false;
						}';							                 
													                 
//t3lib_utility_Debug::debug($this->invokingObject);					
						// Generate the record list:
						$dblist = t3lib_div::makeInstance('rte_link_records');
						//$dblist->script='browse_links.php';
						$dblist->backPath = $GLOBALS['BACK_PATH'];
						$dblist->thumbs = 0;
						
						$pageinfo = t3lib_BEfunc::readPageAccess($this->expandPage,$GLOBALS['BE_USER']->getPagePermsClause(1));
						$dblist->calcPerms = $GLOBALS['BE_USER']->calcPerms($pageinfo);
						$dblist->noControlPanels=1;
						$dblist->clickMenuEnabled=0;
						$dblist->tableList=implode(',',$tables);
						$dblist->tableParams = $this->conf;
						
//t3lib_utility_Debug::debug($dblist->tableParams);
//t3lib_utility_Debug::debug(t3lib_BEfunc::getPagesTSconfig($this->expandPage));
//t3lib_utility_Debug::debug($GLOBALS['BE_USER']->getTSConfigProp('rte_link_records.'));
//t3lib_utility_Debug::debug($GLOBALS['BE_USER']->getTSConfigProp('tx_rte_link_records'));
						
						$dblist->i6lGParams = array(
								'pointer' => $this->pointer,
								'act' => $this->act,
								'mode' => $this->mode,
								'curUrlInfo' => $this->curUrlInfo,
								'curUrlArray' => $this->curUrlArray,
								'P' => $this->P,
								'bparams' => $this->bparams,
								'RTEtsConfigParams' => $this->RTEtsConfigParams,
								'expandPage' => $this->expandPage,
								'expandFolder' => $this->expandFolder,
								'PM' => $this->PM
								);
				
						$dblist->start(
								$this->expandPage,
								t3lib_div::_GP('table'),
								t3lib_div::intInRange($this->pointer,0,100000),
								t3lib_div::_GP('search_field'),
								t3lib_div::_GP('search_levels'),
								t3lib_div::_GP('showLimit')
							);
						$dblist->setDispFields();
						$dblist->generateList();
						$dblist->writeBottom();
						
						return '<!--
									Wrapper table for page tree / record list:
								-->
								<table border="0" cellpadding="0" cellspacing="0" id="typo3-linkPages">
									<tr>
										<td class="c-wCell" valign="top">'.$this->invokingObject->barheader($GLOBALS['LANG']->getLL('pageTree').':').$dbmount.$pagetree->getBrowsableTree().'</td>
										<td class="c-wCell" valign="top">'.((count($tables))?$dblist->HTMLcode.$dblist->getSearchBox().$this->addAttributesForm():'').'</td>
									</tr>
								</table>';
					}
				break;
			}
		}
	} 
	/**
	 * Checks the current URL and determines what to do
	 * If the link was determined to be a file link, then set the action to 'media'
	 *
	 * @param	string		$href
	 * @param	string		$siteUrl
	 * @param	array		$info
	 * @return	array
	 */
	public function parseCurrentUrl($href, $siteUrl, $info) {
		/*
		if ($this->isEnabled && $info['act'] == 'file') {
			$info['act'] = 'media';
			unset($this->invokingObject->curUrlArray['external']);
		}
		*/

//t3lib_utility_Debug::debug($info);
//t3lib_utility_Debug::debug($href);
		//return $info;
	}
	/**
	 * Redefines same function from invoking object in order to invoke local verion of addTitleSelector()
	 *
	 * @return	string		the html code to be added to the form
	 */
	protected function addAttributesForm() {
		// Add target, class selector box, title and parameters fields:
		$ltarget = $this->invokingObject->addTargetSelector();
		$lclass = $this->invokingObject->addClassSelector();
		$ltitle = $this->invokingObject->addTitleSelector();
		if ($ltarget || $lclass || $ltitle) {
			return $this->invokingObject->wrapInForm($ltarget.$lclass.$ltitle);
		}
//t3lib_utility_Debug::debug($ltargetForm);
	}
}

class rte_link_records extends localRecordList {
	var $tableParams;
	
	/**
	 * Returns the title (based on $code) of a record (from table $table) with the proper link around (that is for "pages"-records a link to the level of that record...)
	 *
	 * @param	string		Table name
	 * @param	integer		UID (not used here)
	 * @param	string		Title string
	 * @param	array		Records array (from table name)
	 * @return	string
	 */
	function linkWrapItems($table,$uid,$code,$row)	{
		if(!$code){
			$code = '<i>['.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.no_title',1).']</i>';
		}else{
			$code = htmlspecialchars(t3lib_div::fixed_lgd_cs($code,$this->fixedL));
		}
		/*
		$titleCol = $TCA[$table]['ctrl']['label'];
		$title = $row[$titleCol];
		$ficon = t3lib_iconWorks::getIcon($table,$row);
		*/
//t3lib_utility_Debug::debug($this->tableParams);
//t3lib_utility_Debug::debug($table);
//t3lib_utility_Debug::debug($row);

		$params='';
		if(is_array($this->tableParams[$table.'.']['parameters.'])){
			foreach($this->tableParams[$table.'.']['parameters.'] as $k=>$v){
				if(preg_match('/^{\$.*}$/', $v)){
					$params.=':'.$k.':'.$row[str_replace(array('{$','}'),'',$v)];
				}else{
					$params.=':'.$k.':'.$v;
				}
			}
		}
		$linkToPage=$this->tableParams[$table.'.']['page'];
		if($page=$this->tableParams[$table.'.']['page.'][$row['pid']]){$linkToPage=$page;}
//if($table=='tt_news'){t3lib_utility_Debug::debug($this->tableParams[$table.'.']['page.'][$row['pid']]);}
		return '<a href="#" onclick="'.htmlspecialchars('return link_record(\'link_record:'.$this->tableParams[$table.'.']['classname'].':'.intval($linkToPage).$params.'\')').'">'.$code.'</a>';
	}
}
?>