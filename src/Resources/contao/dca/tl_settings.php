<?php

/**
 * palettes
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{nll_legend:hide},newslinklist_archive,newslinklist_span';

/**
 * fields
 */

// Liste der zu berücksichtigenden Nachrichtenarchive
$GLOBALS['TL_DCA']['tl_settings']['fields']['newslinklist_archive'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_settings']['newslinklist_archive'],
	'options_callback' => array('tl_settings_newslinklist', 'getNewsarchive'),
	'inputType'        => 'checkboxWizard',
	'eval'             => array('tl_class'=>'long', 'multiple'=>true)
);

// Zeitspanne in Monaten, die für die Anzeige der Nachrichtenliste verwendet wird
$GLOBALS['TL_DCA']['tl_settings']['fields']['newslinklist_span'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_settings']['newslinklist_span'],
	'inputType'     => 'text',
	'eval'          => array('tl_class'=>'w50')
);

class tl_settings_newslinklist extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	public function getNewsarchive(DataContainer $dc)
	{
		$array = array();
		$objNews = $this->Database->prepare("SELECT id, title FROM tl_news_archive ORDER BY title ASC")->execute();
		while($objNews->next())
		{
			$array[$objNews->id] = $objNews->title;
		}
		return $array;

	}

}


?>
