<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Symfony\Component\Routing\Exception\ExceptionInterface;

/**
 * Class ModuleFaqList
 *
 * @property array $faq_categories
 * @property int   $faq_readerModule
 */
class ModuleFaqList extends Module
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_faqlist';

	/**
	 * Display a wildcard in the back end
	 *
	 * @return string
	 */
	public function generate()
	{
		$request = System::getContainer()->get('request_stack')->getCurrentRequest();

		if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request))
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . $GLOBALS['TL_LANG']['FMD']['faqlist'][0] . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = StringUtil::specialcharsUrl(System::getContainer()->get('router')->generate('contao_backend', array('do'=>'themes', 'table'=>'tl_module', 'act'=>'edit', 'id'=>$this->id)));

			return $objTemplate->parse();
		}

		$this->faq_categories = StringUtil::deserialize($this->faq_categories);

		// Return if there are no categories
		if (empty($this->faq_categories) || !\is_array($this->faq_categories))
		{
			return '';
		}

		// Show the FAQ reader if an item has been selected
		if ($this->faq_readerModule > 0 && Input::get('auto_item') !== null)
		{
			return $this->getFrontendModule($this->faq_readerModule, $this->strColumn);
		}

		// Tag the FAQ categories (see #2137)
		if (System::getContainer()->has('fos_http_cache.http.symfony_response_tagger'))
		{
			$responseTagger = System::getContainer()->get('fos_http_cache.http.symfony_response_tagger');
			$responseTagger->addTags(array_map(static function ($id) { return 'contao.db.tl_faq_category.' . $id; }, $this->faq_categories));
		}

		return parent::generate();
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		$objFaqs = FaqModel::findPublishedByPids($this->faq_categories);

		if ($objFaqs === null)
		{
			$this->Template->faq = array();

			return;
		}

		$tags = array();
		$arrFaq = array_fill_keys($this->faq_categories, array());

		// Add FAQs
		while ($objFaqs->next())
		{
			$objFaq = $objFaqs->current();

			$arrTemp = $objFaq->row();
			$arrTemp['title'] = StringUtil::specialchars($objFaq->question, true);
			$arrTemp['href'] = $this->generateFaqLink($objFaq);

			if (($objPid = FaqCategoryModel::findById($objFaq->pid)) && empty($arrFaq[$objFaq->pid]))
			{
				$arrFaq[$objFaq->pid] = $objPid->row();
			}

			$arrFaq[$objFaq->pid]['items'][] = $arrTemp;

			$tags[] = 'contao.db.tl_faq.' . $objFaq->id;
		}

		// Tag the FAQs (see #2137)
		if (System::getContainer()->has('fos_http_cache.http.symfony_response_tagger'))
		{
			$responseTagger = System::getContainer()->get('fos_http_cache.http.symfony_response_tagger');
			$responseTagger->addTags($tags);
		}

		$this->Template->faq = array_values(array_filter($arrFaq));
	}

	/**
	 * Create links and remember pages that have been processed
	 *
	 * @param FaqModel $objFaq
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	protected function generateFaqLink($objFaq)
	{
		// A jumpTo page is not mandatory for FAQ categories (see #6226) but required for the FAQ list module
		if (($objCategory = FaqCategoryModel::findById($objFaq->pid)) && $objCategory->jumpTo < 1)
		{
			throw new \Exception('FAQ categories without redirect page cannot be used in an FAQ list');
		}

		try
		{
			$url = System::getContainer()->get('contao.routing.content_url_generator')->generate($objFaq);
		}
		catch (ExceptionInterface)
		{
			$url = Environment::get('requestUri');
		}

		return StringUtil::ampersand($url);
	}
}
