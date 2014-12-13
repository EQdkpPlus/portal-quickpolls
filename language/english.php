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

$lang = array(
	'quickpolls'					=> 'Quickpoll',
	'quickpolls_name'				=> 'Quickpoll',
	'quickpolls_desc'				=> 'Create a poll',
	'quickpolls_f_title'			=> 'Title of the poll',
	'quickpolls_f_question'			=> 'Description',
	'quickpolls_f_help_question'	=> 'Description or Question for the poll, shown under the title',
	'quickpolls_f_closedate'		=> 'Show until',
	'quickpolls_f_closedate_help'	=> 'After this date the results will be shown and no more votes are possible',
	'quickpolls_f_showresults'		=> 'Show Results-Link',
	'quickpolls_f_help_showresults'	=> 'Results-Link will be shown, independet from voting-status',
	'quickpolls_f_options'			=> 'Options',
	'quickpolls_f_help_options'		=> 'Insert one Option per row',
	'quickpolls_f_resetvotes' 		=> 'Reset votes',
	'quickpolls_vote'				=> 'Vote',
	'quickpolls_resuls'				=> 'Results',
);
?>