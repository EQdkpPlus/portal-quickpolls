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
	'quickpolls'				=> 'Schnellumfrage',
	'quickpolls_name'			=> 'Schnellumfrage',
	'quickpolls_desc'			=> 'Erstelle eine Umfrage',
	'quickpolls_f_title'			=> 'Titel der Umfrage',
	'quickpolls_f_question'			=> 'Beschreibung',
	'quickpolls_f_question_help'		=> 'Beschreibung oder Frage für die Schnellumfrage, die unter dem Titel angezeigt werden soll',
	'quickpolls_f_closedate'		=> 'Anzeigen bis',
	'quickpolls_f_help_closedate'		=> 'Nach diesem Datum werden nur noch die Ergebnisse angezeigt, eine Abstimmung ist nicht mehr möglich.',
	'quickpolls_f_showresults'		=> 'Ergebnis-Link anzeigen',
	'quickpolls_f_help_showresults'		=> 'Ergebnis-Link wird angezeigt, unabhängig vom Abstimm-Status',
        'quickpolls_f_showstatistics'           => 'Zeige Statistiken unter den Ergebnissen',
        'quickpolls_f_help_showstatistics'      => 'Beinhaltet die Anzahl an Abstimmungen und, bei Mehrfachauswahl, die Anzahl der Teilnehmer (ohne Gäste)',
	'quickpolls_f_options'			=> 'Optionen',
	'quickpolls_f_help_options'		=> 'Eine Option für die Schnellumfrage pro Zeile eintragen',
	'quickpolls_f_resetvotes'		=> 'Abstimmungen zurücksetzen',
	'quickpolls_f_multiple'			=> 'Mehrfachauswahl erlauben',
	'quickpolls_vote'			=> 'Abstimmen',
	'quickpolls_resuls'			=> 'Ergebnisse',
	'quickpolls_total_votes'		=> 'Stimmen gesamt',
	'quickpolls_participants'		=> 'Teilnehmer (ohne Gäste)',
);
?>
