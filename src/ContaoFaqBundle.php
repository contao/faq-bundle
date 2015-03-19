<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\FaqBundle;

use Contao\CoreBundle\DependencyInjection\Compiler\AddContaoResourcesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Configures the Contao FAQ bundle.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContaoFaqBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddContaoResourcesPass($this->getPath() . '/../contao'));
    }
}
