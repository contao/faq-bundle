<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\FaqBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\FaqModel;
use Contao\StringUtil;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
#[AsHook('replaceInsertTags')]
class InsertTagsListener
{
    private const SUPPORTED_TAGS = [
        'faq',
        'faq_open',
        'faq_url',
        'faq_title',
    ];

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    /**
     * Replaces the FAQ insert tags.
     */
    public function onReplaceInsertTags(string $tag, bool $useCache, mixed $cacheValue, array $flags): string|false
    {
        $elements = explode('::', $tag);
        $key = strtolower($elements[0]);

        if (!\in_array($key, self::SUPPORTED_TAGS, true)) {
            return false;
        }

        $this->framework->initialize();

        if (!$faq = $this->framework->getAdapter(FaqModel::class)->findByIdOrAlias($elements[1])) {
            return '';
        }

        $url = '';

        if ('faq_title' !== $key) {
            $absolute = \in_array('absolute', \array_slice($elements, 2), true) || \in_array('absolute', $flags, true);

            try {
                $url = $this->urlGenerator->generate($faq, [], $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH);
            } catch (ExceptionInterface) {
                return '';
            }
        }

        return $this->generateReplacement($faq, $key, $url, \in_array('blank', \array_slice($elements, 2), true));
    }

    private function generateReplacement(FaqModel $faq, string $key, string $url, bool $blank): string|false
    {
        return match ($key) {
            'faq' => sprintf(
                '<a href="%s" title="%s"%s>%s</a>',
                $url ?: './',
                StringUtil::specialcharsAttribute($faq->question),
                $blank ? ' target="_blank" rel="noreferrer noopener"' : '',
                $faq->question,
            ),
            'faq_open' => sprintf(
                '<a href="%s" title="%s"%s>',
                $url ?: './',
                StringUtil::specialcharsAttribute($faq->question),
                $blank ? ' target="_blank" rel="noreferrer noopener"' : '',
            ),
            'faq_url' => $url ?: './',
            'faq_title' => StringUtil::specialcharsAttribute($faq->question),
            default => false,
        };
    }
}
