<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Add back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], 2, array
(
	'faq' => array
	(
		'tables' => array('tl_faq_category', 'tl_faq'),
		'icon'   => 'bundles/contaofaq/icon.gif'
	)
));


/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 3, array
(
	'faq' => array
	(
		'faqlist'   => 'ModuleFaqList',
		'faqreader' => 'ModuleFaqReader',
		'faqpage'   => 'ModuleFaqPage'
	)
));


/**
 * Register hooks
 */
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('ModuleFaq', 'getSearchablePages');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('contao_faq.listener.insert_tags', 'onReplaceInsertTags');
$GLOBALS['TL_HOOKS']['addFileMetaInformationToRequest'][] = array('contao_faq.listener.file_meta_information', 'onAddFileMetaInformationToRequest');


/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'faqs';
$GLOBALS['TL_PERMISSIONS'][] = 'faqp';
