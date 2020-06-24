<?php
namespace Schachbulle\ContaoNewslinklistBundle\ContentElements;

class NewsLinkliste extends \ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_newslinklist';

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		// Array mit News-ID
		$newslist = unserialize($this->newslinklist);
		$newscount = 0;

		if(is_array($newslist))
		{
			// Alle News laden
			foreach($newslist as $newsid)
			{
				// Nachricht aus tl_news laden
				$objNews = $this->Database->prepare("SELECT * FROM tl_news WHERE id=?")
				                          ->limit(1)
				                          ->execute($newsid);
				// Veröffentlichungsstatus ermitteln
				if((!$objNews->start || $objNews->start < time()) && (!$objNews->stop || $objNews->stop > time()) && $objNews->published) $published = true;
				else $published = false;

				if($objNews->numRows == 1 && $published)
				{
					// Jetzt das dazugehörende Nachrichtenarchiv laden
					$objNewsArchive = $this->Database->prepare("SELECT id, jumpTo FROM tl_news_archive WHERE id=?")
					                                 ->limit(1)
					                                 ->execute($objNews->pid);
					if($objNewsArchive->numRows == 1)
					{
						// Jetzt die Weiterleitungsseite laden
						$objNewsPage = $this->Database->prepare("SELECT id, alias, title, pageTitle FROM tl_page WHERE id=?")
						                              ->limit(1)
						                              ->execute($objNewsArchive->jumpTo);
						if($objNewsPage->numRows == 1)
						{
							// Alle Daten zur Nachricht gefunden
							// Nachrichtenlink generieren
							if($GLOBALS['TL_CONFIG']['useAutoItem'])
								$temp = ampersand($this->generateFrontendUrl($objNewsPage->row(), '/' . ((strlen($objNews->alias) && !$GLOBALS['TL_CONFIG']['disableAlias']) ? $objNews->alias : $objNews->id)));
							else
								$temp = ampersand($this->generateFrontendUrl($objNewsPage->row(), '/items/' . ((strlen($objNews->alias) && !$GLOBALS['TL_CONFIG']['disableAlias']) ? $objNews->alias : $objNews->id)));
							// Werte übertragen
							$newslink[] = $temp;
							$newsheadline[] = $objNews->headline;
							$newssubheadline[] = $objNews->subheadline;
							$newsdatetime[] = date($GLOBALS['TL_CONFIG']['datimFormat'], $objNews->date);
							$newsdate[] = date($GLOBALS['TL_CONFIG']['dateFormat'], $objNews->date);
							$newsunixtime[] = $objNews->date;
							if($objNews->teaser != '')
							{
								$temp = \StringUtil::toHtml5($objNews->teaser);
								$newsteaser[] = \StringUtil::encodeEmail($temp);
							}
							else $newsteaser[] = '';
						}
					}
				}
			}
		}

		if(is_array($newslink))
		{
			// Linkliste sortieren, wenn es Einträge gibt
			if($this->sortOrder == 'ascending')
				array_multisort($newsunixtime,SORT_NUMERIC,SORT_ASC,$newsdate,$newsdatetime,$newslink,$newsheadline,$newssubheadline,$newsteaser);
			else
				array_multisort($newsunixtime,SORT_NUMERIC,SORT_DESC,$newsdate,$newsdatetime,$newslink,$newsheadline,$newssubheadline,$newsteaser);
		}

		// Template und Variablen zuweisen und ausgeben
		if(!$this->newslinklist_tpl) $this->newslinklist_tpl = $this->strTemplate;
		$this->Template = new \FrontendTemplate($this->newslinklist_tpl);
		$this->Template->link = $newslink;
		$this->Template->newsHeadline = $newsheadline;
		$this->Template->subHeadline = $newssubheadline;
		$this->Template->date = $newsdate;
		$this->Template->datetime = $newsdatetime;
		$this->Template->unixtime = $newsunixtime;
		$this->Template->teaser = $newsteaser;
	}

}
