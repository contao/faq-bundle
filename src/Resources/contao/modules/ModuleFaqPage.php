<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Patchwork\Utf8;

/**
 * Class ModuleFaqPage
 *
 * @property array $faq_categories
 *
 * @author Leo Feyer <https://github.com/leofeyer>
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
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['faqpage'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		$this->faq_categories = StringUtil::deserialize($this->faq_categories);

		// Return if there are no categories
		if (empty($this->faq_categories) || !\is_array($this->faq_categories))
		{
			return '';
		}

		return parent::generate();
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		$objFaq = FaqModel::findPublishedByPids($this->faq_categories);

		if ($objFaq === null)
		{
			$this->Template->faq = array();

			return;
		}

		/** @var PageModel $objPage */
		global $objPage;

		$arrFaqs = array_fill_keys($this->faq_categories, array());
		$rootDir = System::getContainer()->getParameter('kernel.project_dir');

		// Add FAQs
		while ($objFaq->next())
		{
			/** @var FaqModel $objFaq */
			$objTemp = (object) $objFaq->row();

			// Clean the RTE output
			$objTemp->answer = StringUtil::toHtml5($objFaq->answer);

			$objTemp->answer = StringUtil::encodeEmail($objTemp->answer);
			$objTemp->addImage = false;

			// Add an image
			if ($objFaq->addImage && $objFaq->singleSRC != '')
			{
				$objModel = FilesModel::findByUuid($objFaq->singleSRC);

				if ($objModel !== null && is_file($rootDir . '/' . $objModel->path))
				{
					// Do not override the field now that we have a model registry (see #6303)
					$arrFaq = $objFaq->row();
					$arrFaq['singleSRC'] = $objModel->path;
					$strLightboxId = 'lightbox[' . substr(md5('mod_faqpage_' . $objFaq->id), 0, 6) . ']'; // see #5810

					$this->addImageToTemplate($objTemp, $arrFaq, null, $strLightboxId, $objModel);
				}
			}

			$objTemp->enclosure = array();

			// Add enclosure
			if ($objFaq->addEnclosure)
			{
				$this->addEnclosuresToTemplate($objTemp, $objFaq->row());
			}

			/** @var UserModel $objAuthor */
			$objAuthor = $objFaq->getRelated('author');
			$objTemp->info = sprintf($GLOBALS['TL_LANG']['MSC']['faqCreatedBy'], Date::parse($objPage->dateFormat, $objFaq->tstamp), $objAuthor->name);

			/** @var FaqCategoryModel $objPid */
			$objPid = $objFaq->getRelated('pid');

			// Order by PID
			$arrFaqs[$objFaq->pid]['items'][] = $objTemp;
			$arrFaqs[$objFaq->pid]['headline'] = $objPid->headline;
			$arrFaqs[$objFaq->pid]['title'] = $objPid->title;
		}

		$arrFaqs = array_values(array_filter($arrFaqs));
		$limit_i = \count($arrFaqs) - 1;

		// Add classes first, last, even and odd
		for ($i=0; $i<=$limit_i; $i++)
		{
			$class = (($i == 0) ? 'first ' : '') . (($i == $limit_i) ? 'last ' : '') . (($i%2 == 0) ? 'even' : 'odd');
			$arrFaqs[$i]['class'] = trim($class);
			$limit_j = \count($arrFaqs[$i]['items']) - 1;

			for ($j=0; $j<=$limit_j; $j++)
			{
				$class = (($j == 0) ? 'first ' : '') . (($j == $limit_j) ? 'last ' : '') . (($j%2 == 0) ? 'even' : 'odd');
				$arrFaqs[$i]['items'][$j]->class = trim($class);
			}
		}

		$this->Template->faq = $arrFaqs;
		$this->Template->request = Environment::get('indexFreeRequest');
		$this->Template->topLink = $GLOBALS['TL_LANG']['MSC']['backToTop'];
	}
}

class_alias(ModuleFaqPage::class, 'ModuleFaqPage');
