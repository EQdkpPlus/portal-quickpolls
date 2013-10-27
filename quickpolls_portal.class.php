<?php
 /*
 * Project:		EQdkp-Plus
 * License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:		2008
 * Date:		$Date: 2012-05-01 13:28:27 +0200 (Di, 01. Mai 2012) $
 * -----------------------------------------------------------------------
 * @author		$Author: hoofy_leon $
 * @copyright	2006-2011 EQdkp-Plus Developer Team
 * @link		http://eqdkp-plus.com
 * @package		eqdkp-plus
 * @version		$Rev: 11769 $
 * 
 * $Id: mycontent_portal.class.php 11769 2012-05-01 11:28:27Z hoofy_leon $
 */

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class quickpolls_portal extends portal_generic {
	public static function __shortcuts() {
		$shortcuts = array('core', 'config', 'db', 'pdc', 'html', 'in', 'tpl', 'user', 'env', 'time', 'jquery', 'db');
		return array_merge(parent::$shortcuts, $shortcuts);
	}

	protected $path		= 'quickpolls';
	protected $data		= array(
		'name'			=> 'Quickpolls Module',
		'version'		=> '0.1.1',
		'author'		=> 'GodMod',
		'contact'		=> EQDKP_PROJECT_URL,
		'description'	=> 'Create a poll on your EQdkp Plus',
	);
	protected $positions = array('middle', 'left1', 'left2', 'right', 'bottom');
	protected $settings	= array(
		'pk_quickpolls_title'	=> array(
			'name'		=> 'pk_quickpolls_title',
			'language'	=> 'pk_quickpolls_title',
			'property'	=> 'text',
			'size'		=> '40',
		),
		'pk_quickpolls_question'	=> array(
			'name'		=> 'pk_quickpolls_question',
			'language'	=> 'pk_quickpolls_question',
			'property'	=> 'text',
			'size'		=> '40',
			'help'		=> 'pk_quickpolls_question_help',
		),
		'pk_quickpolls_closedate'	=> array(
			'name'		=> 'pk_quickpolls_closedate',
			'language'	=> 'pk_quickpolls_closedate',
			'property'	=> 'datepicker',
			'help'		=> 'pk_quickpolls_closedate_help',
			'allow_empty' => true,
		),
		'pk_quickpolls_showresults'	=> array(
			'name'		=> 'pk_quickpolls_showresults',
			'language'	=> 'pk_quickpolls_showresults',
			'property'	=> 'radio',
			'help'		=> 'pk_quickpolls_showresults_help',
		),
		'pk_quickpolls_options'	=> array(
			'name'		=> 'pk_quickpolls_options',
			'language'	=> 'pk_quickpolls_options',
			'property'	=> 'textarea',
			'help'		=> 'pk_quickpolls_options_help',
			'rows'		=> 10,
			'cols'		=> 40,
		),
		'pk_quickpolls_resetvotes'	=> array(
			'name'		=> 'pk_quickpolls_resetvotes',
			'language'	=> 'pk_quickpolls_resetvotes',
			'property'	=> 'radio',
			'help'		=> '',
		),
	);
	protected $install	= array(
		'autoenable'		=> '0',
		'defaultposition'	=> 'right',
		'defaultnumber'		=> '4',
	);
	
	protected $multiple = true;
	
	protected $sqls		= array(
		"DROP TABLE IF EXISTS __quickpolls_votes;",
		"CREATE TABLE `__quickpolls_votes` (
		  `poll_id` int(10) unsigned NOT NULL default '0',
		  `user_id` int(10) unsigned NOT NULL default '0',
		  KEY `poll_id` (`poll_id`),
		  KEY `user_id` (`user_id`)
		) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;",
		"DROP TABLE IF EXISTS __quickpolls;",
		"CREATE TABLE `__quickpolls` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `tstamp` int(10) unsigned NOT NULL default '0',
		  `results` text NULL,
		  PRIMARY KEY  (`id`)
		) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;",
	);
	
	private $blnShowResults = false;

	public function output() {
		//reset votes
		if ($this->config('pk_quickpolls_resetvotes')){
			$this->reset_votes();
			$this->set_config('pk_quickpolls_resetvotes', 0);
		}
	
		if($this->config('pk_quickpolls_title')){
			$this->header = sanitize($this->config('pk_quickpolls_title'));
		}
		$this->tpl->add_css("
			.quickpolls_radio label{
			   display: block;
			   margin-bottom: -10px;
			 }
		");
		
		$myout = '<div>'.sanitize($this->config('pk_quickpolls_question')).'</div><br />';

		if ($this->in->exists('quickpolls_'.$this->id)){
			$blnResult = $this->performVote();
			if ($blnResult){
				$myout .= $this->showResults();
			} else {
				$myout .= $this->showForm();
				
			}
		} else {
			if (($this->config('pk_quickpolls_closedate') > 0 && ($this->config('pk_quickpolls_closedate') < $this->time->time)) || (($this->in->get('quickpolls_results', 0)==$this->id) && $this->config('pk_quickpolls_showresults')) || ($this->userVoted())){
				$myout .= $this->showResults();
			} else {
				$myout .= $this->showForm();
			}
		}
		
		if ($this->config('pk_quickpolls_showresults') && !$this->blnShowResults){
			$myout .= '<br /><div><a href="'.$this->SID.'&amp;quickpolls_results='.$this->id.'">'.$this->user->lang('pk_quickpolls_resuls').'</a></div>';
		}
		return $myout;
	}
	
	private function reset_votes(){
		$this->db->prepare("DELETE FROM __quickpolls WHERE id=?")->execute($this->id);
		$this->db->prepare("DELETE FROM __quickpolls_votes WHERE poll_id=?")->execute($this->id);
	}
	
	private function showResults(){
		$this->blnShowResults = true;
		$arrOptions = explode("\n", $this->config('pk_quickpolls_options'));
		$myout = "";
		//Get Results
		$count = 0;
		$objQuery = $this->db->prepare("SELECT * FROM __quickpolls WHERE id=?")->execute($this->id);
		if ($objQuery){
			$arrResult = $objQuery->fetchAssoc();
			if ($objQuery->numRows > 0){
				$arrVoteResult = unserialize($arrResult['results']);
				foreach ($arrVoteResult as $key=>$value){
					$count += $value;
				}		
			} else {
				foreach ($arrOptions as $key=>$value){
					$arrVoteResult[$key] = 0;
				}
			}		
		}
		
		foreach ($arrOptions as $key => $value){
			if (trim($value) == '') continue; 
			$optionCount = (isset($arrVoteResult[$key])) ? $arrVoteResult[$key] : 0;
			$optionProcent = ($count == 0) ? 0 : round(($optionCount / $count)*100);
			$myout .= $this->jquery->progressbar('quickpolls_'.$this->id.'_'.$key, $optionProcent, array('text' => trim($value).': '.$optionCount.' (%percentage%)', 'txtalign' => 'left'));
		}
		

		return $myout;
	}
	
	private function showForm(){
		$arrTmpOptions = explode("\n", $this->config('pk_quickpolls_options'));
		foreach($arrTmpOptions as $key => $value){
			if (trim($value) == '') continue;
			$arrOptions[$key] = $value;
		}
		
		$myout = '
		<form action="" method="post">
				<div class="quickpolls_radio">'.$this->html->RadioBox('quickpolls_'.$this->id, $arrOptions, 'none').'</div>
				<input type="hidden" name="'.$this->user->csrfPostToken().'" value="'.$this->user->csrfPostToken().'"/>
				<button type="submit"><i class="fa fa-check-square-o"></i> '.$this->user->lang('pk_quickpolls_vote').'</button>
		</form>
		';
		return $myout;
	}
	
	private function performVote(){
		if (!$this->userVoted()){
			//Get Results
			$objQuery = $this->db->prepare("SELECT * FROM __quickpolls WHERE id=?")->execute($this->id);
			if ($objQuery) {
				$arrResult = $objQuery->fetchAssoc();
				if ($objQuery->numRows){
					$arrVoteResult = unserialize($arrResult['results']);
					//Increase Vote
					$intSelected = $this->in->get('quickpolls_'.$this->id, 0);
					if (isset($arrVoteResult[$intSelected])){
						$arrVoteResult[$intSelected] = $arrVoteResult[$intSelected] + 1;
					} else {
						$arrVoteResult[$intSelected] = 1;
					}
					
					//Update
					$this->db->prepare("UPDATE __quickpolls :p WHERE id=?")->set(array(
						'tstamp' => $this->time->time,
						'results' => serialize($arrVoteResult),
					))->execute($this->id);
				} else {
					$arrOptions = explode("\n", $this->config('pk_quickpolls_options'));
					$arrVoteResult = array();
					foreach ($arrOptions as $key=>$value){
						$arrVoteResult[$key] = 0;
					}
					//Increase Vote
					$intSelected = $this->in->get('quickpolls_'.$this->id, 0);
					$arrVoteResult[$intSelected] = $arrVoteResult[$intSelected] + 1;
					
					//Insert
					$this->db->prepare("INSERT INTO __quickpolls :p")->set(array(
						'id'	=> $this->id,
						'tstamp' => $this->time->time,
						'results' => serialize($arrVoteResult),
					))->execute();	
				}		
			}

			$this->recordUserVote();
			return true;
		}
		return false;
	}
	
	private function recordUserVote(){
		if ($this->user->is_signedin()){
			$this->db->prepare("INSERT INTO __quickpolls_votes :p")->set(
				array(
					'poll_id' => $this->id,
					'user_id' => $this->user->id,
				)
			)->execute();
		}
	}
	
	private function userVoted(){
		if ($this->user->is_signedin()){
			$objQuery = $this->db->prepare("SELECT * FROM __quickpolls_votes WHERE poll_id=? AND user_id=?")->execute($this->id, $this->user->id);
			if($objQuery){
				if ($objQuery->numRows) return true;
			}
		}
		return false;
	}
	
	public function reset() {
		$this->pdc->del('portal.module.quickpolls');
	}
}
?>