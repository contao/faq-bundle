<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

/**
 * Class ModuleFaqPage
 *
 * @property array $faq_categories
 */
class ModuleFaqPage extends Module
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_faqpage';

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
			$objTemplate->wildcard = '### ' . $GLOBALS['TL_LANG']['FMD']['faqpage'][0] . ' ###';
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

		global $objPage;

		$tags = array();
		$arrFaqs = array_fill_keys($this->faq_categories, array());

		// Add FAQs
		foreach ($objFaqs as $objFaq)
		{
			/** @var FaqModel $objFaq */
			$objTemp = (object) $objFaq->row();

			$objTemp->answer = StringUtil::encodeEmail($objFaq->answer);
			$objTemp->addImage = false;
			$objTemp->addBefore = false;

			// Add an image
			if ($objFaq->addImage)
			{
				$figure = System::getContainer()
					->get('contao.image.studio')
					->createFigureBuilder()
					->from($objFaq->singleSRC)
					->setSize($objFaq->size)
					->setOverwriteMetadata($objFaq->getOverwriteMetadata())
					->setLightboxGroupIdentifier('lightbox[' . substr(md5('mod_faqpage_' . $objFaq->id), 0, 6) . ']')
					->enableLightbox($objFaq->fullsize)
					->buildIfResourceExists();

				$figure?->applyLegacyTemplateData($objTemp, null, $objFaq->floating);
			}

			$objTemp->enclosure = array();

			// Add enclosure
			if ($objFaq->addEnclosure)
			{
				$this->addEnclosuresToTemplate($objTemp, $objFaq->row());
			}

			$strAuthor = '';

			if ($objAuthor = UserModel::findById($objFaq->author))
			{
				$strAuthor = $objAuthor->name;
			}

			$objTemp->info = sprintf($GLOBALS['TL_LANG']['MSC']['faqCreatedBy'], Date::parse($objPage->dateFormat, $objFaq->tstamp), $strAuthor);

			if (($objPid = FaqCategoryModel::findById($objFaq->pid)) && empty($arrFaqs[$objFaq->pid]))
			{
				$arrFaqs[$objFaq->pid] = $objPid->row();
			}

			$arrFaqs[$objFaq->pid]['items'][] = $objTemp;

			$tags[] = 'contao.db.tl_faq.' . $objFaq->id;
		}

		// Tag the FAQs (see #2137)
		if (System::getContainer()->has('fos_http_cache.http.symfony_response_tagger'))
		{
			$responseTagger = System::getContainer()->get('fos_http_cache.http.symfony_response_tagger');
			$responseTagger->addTags($tags);
		}

		$this->Template->faq = array_values(array_filter($arrFaqs));
		$this->Template->request = Environment::get('requestUri');
		$this->Template->topLink = $GLOBALS['TL_LANG']['MSC']['backToTop'];

		$this->Template->getSchemaOrgData = function () use ($objFaqs) {
			return ModuleFaq::getSchemaOrgData($objFaqs, '#/schema/faq/' . $this->id);
		};
	}
}
