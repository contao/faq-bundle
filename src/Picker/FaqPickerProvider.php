<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\FaqBundle\Picker;

use Contao\CoreBundle\DependencyInjection\Attribute\AsPickerProvider;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\Picker\AbstractInsertTagPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;
use Contao\FaqCategoryModel;
use Contao\FaqModel;
use Knp\Menu\FactoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsPickerProvider(priority: 64)]
class FaqPickerProvider extends AbstractInsertTagPickerProvider implements DcaPickerProviderInterface, FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @internal
     */
    public function __construct(
        FactoryInterface $menuFactory,
        RouterInterface $router,
        TranslatorInterface|null $translator,
        private readonly Security $security,
    ) {
        parent::__construct($menuFactory, $router, $translator);
    }

    public function getName(): string
    {
        return 'faqPicker';
    }

    public function supportsContext(string $context): bool
    {
        return 'link' === $context && $this->security->isGranted('contao_user.modules', 'faq');
    }

    public function supportsValue(PickerConfig $config): bool
    {
        return $this->isMatchingInsertTag($config);
    }

    public function getDcaTable(PickerConfig|null $config = null): string
    {
        return 'tl_faq';
    }

    public function getDcaAttributes(PickerConfig $config): array
    {
        $attributes = ['fieldType' => 'radio'];

        if ($this->supportsValue($config)) {
            $attributes['value'] = $this->getInsertTagValue($config);

            if ($flags = $this->getInsertTagFlags($config)) {
                $attributes['flags'] = $flags;
            }
        }

        return $attributes;
    }

    public function convertDcaValue(PickerConfig $config, mixed $value): string
    {
        return sprintf($this->getInsertTag($config), $value);
    }

    protected function getRouteParameters(PickerConfig|null $config = null): array
    {
        $params = ['do' => 'faq'];

        if (!$config?->getValue() || !$this->supportsValue($config)) {
            return $params;
        }

        if (null !== ($faqId = $this->getFaqCategoryId($this->getInsertTagValue($config)))) {
            $params['table'] = 'tl_faq';
            $params['id'] = $faqId;
        }

        return $params;
    }

    protected function getDefaultInsertTag(): string
    {
        return '{{faq_url::%s}}';
    }

    private function getFaqCategoryId(int|string $id): int|null
    {
        $faqAdapter = $this->framework->getAdapter(FaqModel::class);

        if (!$faqModel = $faqAdapter->findById($id)) {
            return null;
        }

        if (!$faqCategory = $this->framework->getAdapter(FaqCategoryModel::class)->findById($faqModel->pid)) {
            return null;
        }

        return (int) $faqCategory->id;
    }
}
