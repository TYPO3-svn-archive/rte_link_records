<?php
/*
 * Created on 29.7.2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class tx_rtelinkrecords {
	/**
     * Builds a record list of any table
     * @param        array        $params:  Contains fieldName and fieldValue.
     * @param        obj        $pObj:  Objet
     * @return        string        HTML output
     */
    function recordList($params, $pObj) {
        /* Pull the current fieldname and value from constants */
        $fieldName = $params['fieldName'];
        $fieldValue = $params['fieldValue'];

        // get the configuration
        $conf = $this->getConf($fieldName, $pObj);

        $table         = $conf['table'];
        $where         = ($conf['where']!='') ? $conf['where'] : '1=1';
        $orderBy     = $conf['orderBy'];
        $limit         = $conf['limit'];        
        

        if ($table == '') {
            return 'Table "'.$table.'" doesn\'t exit';
        }
        
        /* Construct the SQL query */
        $res = $GLOBALS['TYPO3_DB']->exec_selectQuery('*', $table, $where.t3lib_beFunc::deleteClause($table), '', $orderBy, $limit);
        
        /* Build the HTML select tag */
        $content = array();
        $content[] = '<select name="'. $fieldName .'">';
        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $label = t3lib_beFunc::getRecordTitle($table, $row);
            
            /* If the current user matches the field value, mark it as default */
            if ($row['uid'] == $fieldValue) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }
            
            /* Build the option tag */
            $content[] = '<option value="'. $row['uid'] .'" '. $selected .'>'.$label.'</option>';
        }
        $content[] = '</select>';
        
        return implode(chr(10), $content);
    }
    
    
    /**
     * Builds an input form that also includes the link popup wizard.
     * @param        array        $params:  Contains fieldName and fieldValue.
     * @param        obj        $pObj:  Objet
     * @return        string        HTML output
     */
    function page($params, $pObj) {
        /* Pull the current fieldname and value from constants */
        $fieldName = $params['fieldName'];
        $fieldValue = $params['fieldValue'];


        // get the configuration
        $conf = $this->getConf($fieldName, $pObj);
        $formName = ($conf['formName']!='') ? $conf['formName'] : 'editForm';
        
        $input = '<input name="'. $fieldName .'" value="'. $fieldValue .'" />';
        
        /* @todo     Don't hardcode the inclusion of the wizard this way.  Use more backend APIs. */
        $wizard = '<a href="#" onclick="this.blur(); vHWin=window.open(\'../../../../typo3/browse_links.php?mode=wizard&amp;P[field]='. $fieldName .'&amp;P[formName]='.$formName.'&amp;P[itemName]='. $fieldName .'&amp;P[fieldChangeFunc][typo3form.fieldGet]=null&amp;P[fieldChangeFunc][TBE_EDITOR_fieldChanged]=null\',\'popUpID478be36b64\',\'height=300,width=500,status=0,menubar=0,scrollbars=1\'); vHWin.focus(); return false;">
                                <img src="../../../../typo3/sysext/t3skin/icons/gfx/link_popup.gif" width="16" height="15" border="0" alt="Link" title="Link" />
                            </a>';
        
        return $input.$wizard;
    }

    /**
     * Show an image, mainly for helping people (manual, ...)
     * @param        array        $params:  Contains fieldName and fieldValue.
     * @param        obj        $pObj:  Objet
     * @return        string        HTML output
     */
    function image($params, $pObj) {
        /* Pull the current fieldname and value from constants */
        $fieldName = $params['fieldName'];
        $fieldValue = $params['fieldValue'];


        // get the configuration
        $conf = $this->getConf($fieldName, $pObj);
        $src = $conf['file'];
        
        $image = '<img src="../../../../'.$src.'" />';
    
        return $image;
    }
    
    
    /**
     * Show an iframe
     * @param        array        $params:  Contains fieldName and fieldValue.
     * @param        obj        $pObj:  Objet
     * @return        string        HTML output
     */
    function iframe($params, $pObj) {
        /* Pull the current fieldname and value from constants */
        $fieldName = $params['fieldName'];
        $fieldValue = $params['fieldValue'];


        // get the configuration
        $conf = $this->getConf($fieldName, $pObj);
        $settings = '';
        
        $conf['src'] = ($conf['https']==1) ? 'https://'.$conf['src'] : 'http://'.$conf['src'];
        unset ($conf['https']);

        
        foreach ($conf as $key=>$value) {
            $settings.= $key.'="'.$value.'" ';
        }
                
        $iframe = '<iframe '.$settings.' ></iframe>';
    
        return $iframe;
    }    
    
    
    /**
     * Show an image, mainly for helping people (manual, ...)
     * @param        array        $params:  Contains fieldName and fieldValue.
     * @param        obj        $pObj:  Objet
     * @return        string        HTML output
     */
    function html($params, $pObj) {
        /* Pull the current fieldname and value from constants */
        $fieldName = $params['fieldName'];
        $fieldValue = $params['fieldValue'];


        // get the configuration
        $conf = $this->getConf($fieldName, $pObj);
        
        $search = array('#58#', '#59#', '#44#');
        $replace = array(':', ';', ',');
        $code = str_replace($search, $replace, $conf['code']);
    
        return $code;
    }


    /**
     * Builds an textarea
     * @param        array        $params:  Contains fieldName and fieldValue.
     * @param        obj        $pObj:  Objet
     * @return        string        HTML output
     */
    function textarea($params, $pObj) {
        /* Pull the current fieldname and value from constants */
        $fieldName = $params['fieldName'];
        $fieldValue = str_replace('#####', chr(10), $params['fieldValue']);

        // get the configuration
        $conf = $this->getConf($fieldName, $pObj);
        $key = substr($fieldName, 5,-1);
        $formName = ($conf['formName']!='') ? $conf['formName'] : 'tsStyleConfigForm';
        $css='';
        unset($conf['formName']);
        foreach($conf as $key=>$value){$css .= $key.':'.$value.'; ';}
        if($css!=''){$css = ' style="'.$css.'" ';}
//t3lib_utility_Debug::debug($conf);
//t3lib_utility_Debug::debug(str_replace(chr(10),'##########',$_POST['data'][$key]));
/*
tx_ttnews=typo3conf/ext/tt_news/pi/class.tx_ttnews.php
tx_tendshop_pi1=typo3conf/ext/tend_shop/pi/class.tx_tendshop_pi1.php
*/
        $field = '	<textarea '.$css.' id="field'.$key.'" name="'. $fieldName .'" cols="60" rows="10">'. $fieldValue .'</textarea>
        	        <script type="text/javascript">
			            window.onload=function(){
			                var el = document.getElementsByName("'.$formName.'");
			                Event.observe(el[0], "submit", changeContent, false);
			            }
			            function changeContent() {
							var val = document.getElementById("field'.$key.'");
			                str2 = val.value;  
			                while(str2.indexOf("\n") != -1) { 
			                    str2 = str2.replace("\n", "#####"); 
			                }
			                val.value = str2;
			                
			            }
			        </script>';
        return $field;
    }
    
    /**
     * Get the possible configuration of a single field
     * @param        string        $fieldName:  Name of the field
     * @param        obj        $pObj:  Objet
     * @return        array        configuration
     */
    function getConf($fieldName, $pObj) {
        $key = substr($fieldName, 5,-1);
        $tempConf = '';
        $realConf = array();

        // get the complete line from constants and find the key 'settings'
        $conf = t3lib_div::trimExplode(';', $pObj->flatSetup[$key.'..']);
        foreach ($conf as $key=>$value) {
            if (strpos($value, 'settings') !== false) {
                $tempConf = $value;
            }
        }
        
        // if settings are found, split them accordingly
        if ($tempConf!='') {
            $tempConf = substr($tempConf, 9);
            
            $tempConf = t3lib_div::trimExplode(',', $tempConf);
            
            foreach ($tempConf as $key) {
                $split = t3lib_div::trimExplode(':', $key);
                $realConf[$split[0]] = $split[1];
            }
            
        }
        
        return $realConf;
    }
	/*
	function textarea($params, $pObj){
		return '<textarea name="data[classNamePaths]"></textarea>';
	}
	*/
}
?>
