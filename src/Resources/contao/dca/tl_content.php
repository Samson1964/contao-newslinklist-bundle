<?php

/**
 * Palettes
 */
// Palette je nach Nachrichten-Zeitspanne anpassen
if($GLOBALS['TL_CONFIG']['newslinklist_span'])
	$GLOBALS['TL_DCA']['tl_content']['palettes']['newslinklist'] = '{type_legend},type,headline;{nll_legend},sortOrder,newslinklist_stopdate,newslinklist_tpl,newslinklist;{protected_legend:hide},protected;{expert_legend:hide},guest,cssID,space;{invisible_legend:hide},invisible,start,stop';
else
	$GLOBALS['TL_DCA']['tl_content']['palettes']['newslinklist'] = '{type_legend},type,headline;{nll_legend},sortOrder,newslinklist_tpl,newslinklist;{protected_legend:hide},protected;{expert_legend:hide},guest,cssID,space;{invisible_legend:hide},invisible,start,stop';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['newslinklist_stopdate'] = array
(
	'exclude'              => true,
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['newslinklist_stopdate'],
	'inputType'            => 'text',
	'eval'                 => array('rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'w50 clr wizard', 'submitOnChange'=>true),
	'load_callback'        => array(array('tl_content_newslinklist', 'getStartstopinfo')),
	'sql'                  => "varchar(10) NOT NULL default ''"
);

// Template zuweisen

$GLOBALS['TL_DCA']['tl_content']['fields']['newslinklist_tpl'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['newslinklist_tpl'],
	'exclude'              => true,
	'inputType'            => 'select',
	'options_callback'     => array('tl_content_newslinklist', 'getNewslinklistTemplates'),
	'eval'                 => array('tl_class'=>'w50'),
	'sql'                  => "varchar(32) NOT NULL default ''"
); 

// Nachrichtenliste anzeigen
// Fix 'class'=>'clr' nach 'tl_class'=>'long clr', sh. https://github.com/contao/core/issues/8584

$GLOBALS['TL_DCA']['tl_content']['fields']['newslinklist'] = array
(
	'label'                => &$GLOBALS['TL_LANG']['tl_content']['newslinklist'],
	'exclude'              => true,
	'options_callback'     => array('tl_content_newslinklist', 'getNewslinklist'),
	'inputType'            => 'checkboxWizard',
	'eval'                 => array('mandatory'=>false, 'multiple'=>true, 'tl_class'=>'clr long'),
	'sql'                  => "blob NULL", 
);

class tl_content_newslinklist extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	public function getNewslinklistTemplates()
	{
		return $this->getTemplateGroup('newslinklist_');
	} 

	public function getStartstopinfo($varValue, DataContainer $dc)
	{
		if($dc->activeRecord)
		{ 
			//echo "<pre>";
			//print_r($GLOBALS['TL_DCA'][$dc->table]['fields']['newslinklist_start']); 
			//echo "</pre>";
			// Start- und Stopwert global speichern
			if($varValue) 
				$varValue = strtotime("today", $varValue) + (3600 * 24) - 1;
			else
				$varValue = strtotime("today", time()) + (3600 * 24) - 1;

			$GLOBALS['NEWSLINKLIST']['stop'] = $varValue;
			$GLOBALS['NEWSLINKLIST']['start'] = strtotime("today", $varValue - (3600 * 24 * 30 * $GLOBALS['TL_CONFIG']['newslinklist_span']));

			$bis = date($GLOBALS['TL_CONFIG']['dateFormat'], $varValue);
			$von = date($GLOBALS['TL_CONFIG']['dateFormat'], $GLOBALS['NEWSLINKLIST']['start']);

			$GLOBALS['TL_DCA'][$dc->table]['fields']['newslinklist_stopdate']['label'][0] = "Bis Nachrichtendatum (Von: $von)";
			$GLOBALS['TL_DCA'][$dc->table]['fields']['newslinklist_stopdate']['label'][1] = "Nur Nachrichten vom $von bis $bis werden angezeigt!";
		}
		return $varValue;
	}

	public function getNewslinklist(DataContainer $dc)
	{
		// Erlaubte Archivliste laden
		($GLOBALS['TL_CONFIG']['newslinklist_archive']) ? $newsarchive = unserialize($GLOBALS['TL_CONFIG']['newslinklist_archive']) : $newsarchive = array();

		// Nachrichtenarchive laden und zuordnen
		$objNewsArchive = $this->Database->prepare("SELECT id, title FROM tl_news_archive")
		                                 ->execute();
		while($objNewsArchive->next())
		{
			if(in_array($objNewsArchive->id, $newsarchive))
			{
				$NewsArchiv[$objNewsArchive->id] = $objNewsArchive->title;
			}
		}
		
		$von = $GLOBALS['NEWSLINKLIST']['start'];
		$bis = $GLOBALS['NEWSLINKLIST']['stop'];
		
		$array = array();
		if($von && $bis)
		{
			// Zeitraum vorgegeben, aber die bereits gespeicherten Einträge müssen auch enthalten sein!
			// Gespeicherte Datensätze abrufen und in Abfrage einfügen
			$gespeichert = unserialize($dc->activeRecord->newslinklist);
			$oder = '';
			if(is_array($gespeichert))
			{
				foreach($gespeichert as $item)
				{
					$oder .= ' OR id = '.$item;
				}
			}
			// Datenbankabfrage vornehmen
			$objNews = $this->Database->prepare("SELECT id, pid, headline, date, published, start, stop FROM tl_news WHERE (date > ? AND date < ?) ".$oder." ORDER BY date DESC")->execute($von, $bis);
		}
		else $objNews = $this->Database->prepare("SELECT id, pid, headline, date, published, start, stop FROM tl_news ORDER BY date DESC")->execute();
		while($objNews->next())
		{
			// Veröffentlichungsstatus ermitteln
			if((!$objNews->start || $objNews->start < time()) && (!$objNews->stop || $objNews->stop > time()) && $objNews->published) $published = true;
			else $published = false;
			
			if($NewsArchiv[$objNews->pid])
			{
				if($published) $array[$objNews->id] =  date($GLOBALS['TL_CONFIG']['datimFormat'], $objNews->date).' <b>'.$objNews->headline.'</b> ['.$NewsArchiv[$objNews->pid].']';
				else $array[$objNews->id] =  date($GLOBALS['TL_CONFIG']['datimFormat'], $objNews->date).' <i>'.$objNews->headline.'</i> ('.$GLOBALS['TL_LANG']['tl_content']['nll_unpublished'].') ['.$NewsArchiv[$objNews->pid].']';
			}
		}
		return $array;

	}

}

?>
