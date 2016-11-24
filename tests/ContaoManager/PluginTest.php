<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\FaqBundle\Test\ContaoManager;

use Contao\FaqBundle\ContaoManager\Plugin;
use Contao\ManagerBundle\ContaoManager\Bundle\Config\BundleConfig;

/**
 * Tests the Plugin class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $plugin = new Plugin();

        $this->assertInstanceOf('Contao\FaqBundle\ContaoManager\Plugin', $plugin);
    }

    /**
     * Tests the getBundles() method.
     */
    public function testGetBundles()
    {
        $parser = $this->getMock('Contao\ManagerBundle\ContaoManager\Bundle\Parser\ParserInterface');

        /** @var BundleConfig $config */
        $config = (new Plugin())->getBundles($parser)[0];

        $this->assertInstanceOf('Contao\ManagerBundle\ContaoManager\Bundle\Config\BundleConfig', $config);
        $this->assertEquals('Contao\FaqBundle\ContaoFaqBundle', $config->getName());
        $this->assertEquals(['Contao\CoreBundle\ContaoCoreBundle'], $config->getLoadAfter());
        $this->assertEquals(['faq'], $config->getReplace());
    }
}
