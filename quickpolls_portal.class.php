<?php
/*	Project:	EQdkp-Plus
 *	Package:	Quick polls Portal Module
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2015 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class quickpolls_portal extends portal_generic {

	protected static $path		= 'quickpolls';
	protected static $data		= array(
		'name'			=> 'Quickpolls Module',
		'version'		=> '0.3.0',
		'author'		=> 'GodMod',
		'icon'			=> 'fa-tasks',
		'contact'		=> EQDKP_PROJECT_URL,
		'description'	=> 'Create a poll on your EQdkp Plus',
		'lang_prefix'	=> 'quickpolls_',
		'multiple'		=> true,
	);
	protected static $positions = array('middle', 'left1', 'left2', 'right', 'bottom');
	protected $settings	= array(
		'title'	=> array(
			'type'		=> 'text',
			'size'		=> '40',
		),
		'question'	=> array(
			'type'		=> 'text',
			'size'		=> '40',
		),
		'closedate'	=> array(
			'type'		=> 'datepicker',
			'allow_empty' => true,
		),
		'showresults'	=> array(
			'type'		=> 'radio',
		),
		'multiple' => array(
			'type'		=> 'radio',
		),
		'showstatistics' => array(
			'type'		=> 'radio',
		),
		'options'	=> array(
			'type'		=> 'textarea',
			'rows'		=> 10,
			'cols'		=> 40,
		),
		'resetvotes'	=> array(
			'type'		=> 'radio',
			'help'		=> '',
		),
	);
	protected static $install	= array(
		'autoenable'		=> '0',
		'defaultposition'	=> 'right',
		'defaultnumber'		=> '4',
	);
	
	protected static $sqls		= array(
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
	
	protected static $apiLevel = 20;
	
	private $blnShowResults = false;

	public function output() {
		//reset votes
		if ($this->config('resetvotes')){
			$this->reset_votes();
			$this->set_config('resetvotes', 0);
		}
	
		if($this->config('title')){
			$this->header = sanitize($this->config('title'));
		}
		$this->tpl->add_css("
			.quickpolls_radio label{
			   display: block;
			   margin-bottom: -10px;
			 }
		");
		
		$myout = '<div>'.sanitize($this->config('question')).'</div><br />';

		if ($this->in->exists('quickpolls_'.$this->id)){
			$blnResult = $this->performVote();
			if ($blnResult){
				$myout .= $this->showResults();
			} else {
				$myout .= $this->showForm();
				
			}
		} else {
			if (($this->config('closedate') > 0 && ($this->config('closedate') < $this->time->time)) || (($this->in->get('quickpolls_results', 0)==$this->id) && $this->config('showresults')) || ($this->userVoted())){
				$myout .= $this->showResults();
			} else {
				$myout .= $this->showForm();
			}
		}
		
		if ($this->config('showresults') && !$this->blnShowResults){
			$myout .= '<br /><div><a href="'.$this->SID.'&amp;quickpolls_results='.$this->id.'">'.$this->user->lang('quickpolls_resuls').'</a></div>';
		}
		return $myout;
	}
	
	private function reset_votes(){
		$this->db->prepare("DELETE FROM __quickpolls WHERE id=?")->execute($this->id);
		$this->db->prepare("DELETE FROM __quickpolls_votes WHERE poll_id=?")->execute($this->id);
	}
	
	private function showResults(){
		$this->blnShowResults = true;
		$arrOptions = explode("\n", $this->config('options'));
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

		if($this->config('showstatistics'))
		{
			$myout .= '<div class="quickpolls_stats"><p>'.$this->user->lang('quickpolls_total_votes').': '.$count.'</p>';
			$this->tpl->add_css('.quickpolls_stats p{ padding-top: 5px }');
			if($this->config('multiple'))
			{
				$myout .= '<p>'.$this->user->lang('quickpolls_participants').': '.$this->getNumberOfUsersVoted().'</p>';
			}
			$myout .= '</div>';
		}

		return $myout;
	}
	
	private function showForm(){
		$arrTmpOptions = explode("\n", $this->config('options'));
		$arrOptions = array();
		foreach($arrTmpOptions as $key => $value){
			if (trim($value) == '') continue;
			$arrOptions[$key] = $value;
		}

		if($this->config('multiple')){
			$fields = new hcheckbox('quickpolls_'.$this->id, array('options' => $arrOptions, 'value' => 'none'));
		} else {
			$fields = new hradio('quickpolls_'.$this->id, array('options' => $arrOptions, 'value' => 'none'));
		}
		
		$myout = '
		<form action="" method="post">
				<div class="quickpolls_radio">'.$fields.'</div>
				<input type="hidden" name="'.$this->user->csrfPostToken().'" value="'.$this->user->csrfPostToken().'"/>
				<button type="submit"><i class="fa fa-check-square-o"></i> '.$this->user->lang('quickpolls_vote').'</button>
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

					if($this->config('multiple')){
						$arrSelected = $this->in->getArray('quickpolls_'.$this->id, 'int');

						foreach($arrSelected as $intSelected){
							if (isset($arrVoteResult[$intSelected])){
								$arrVoteResult[$intSelected] = $arrVoteResult[$intSelected] + 1;
							} else {
								$arrVoteResult[$intSelected] = 1;
							}
						}
					} else {
						$intSelected = $this->in->get('quickpolls_'.$this->id, 0);
						if (isset($arrVoteResult[$intSelected])){
							$arrVoteResult[$intSelected] = $arrVoteResult[$intSelected] + 1;
						} else {
							$arrVoteResult[$intSelected] = 1;
						}
					}

					//Update
					$this->db->prepare("UPDATE __quickpolls :p WHERE id=?")->set(array(
						'tstamp' => $this->time->time,
						'results' => serialize($arrVoteResult),
					))->execute($this->id);
				} else {
					$arrOptions = explode("\n", $this->config('options'));
					$arrVoteResult = array();
					foreach ($arrOptions as $key=>$value){
						$arrVoteResult[$key] = 0;
					}
					//Increase Vote
					if($this->config('multiple')){
						$arrSelected = $this->in->getArray('quickpolls_'.$this->id, 'int');
						foreach($arrSelected as $intSelected){
							$arrVoteResult[$intSelected] = 1;
						}
						
					} else {
						$intSelected = $this->in->get('quickpolls_'.$this->id, 0);
						$arrVoteResult[$intSelected] = 1;
					}
					
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
	
	private function getNumberOfUsersVoted()
	{
		$objQuery = $this->db->prepare("SELECT count(*) as count FROM __quickpolls_votes WHERE poll_id=?")->execute($this->id);
		if ($objQuery)
		{
			$query = $objQuery->fetchAssoc();	
			return $query['count'];
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
}
?>
