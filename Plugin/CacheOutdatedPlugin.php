<?php
/**
 * Copyright (C) 2026 Benjamin Rosenberger <bensch.rosenberger@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @copyright 2026 Benjamin Rosenberger
 * @author bensch.rosenberger@gmail.com
 * @license MIT
 * @link https://brocode.at
 */
declare(strict_types=1);

namespace Brocode\AdminhtmlCacheEviction\Plugin;

use Magento\AdminNotification\Model\System\Message\CacheOutdated;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;

/**
 * Appends quick-action links to the cache-invalidated system notification so
 * admins can act without navigating away to Cache Management.
 *
 * Each link is gated by the same ACL resource the corresponding controller uses,
 * so it never surfaces an action the current admin user cannot perform.
 * Plain <a href> links are used — no inline JS, no CSP violations.
 */
class CacheOutdatedPlugin
{
    public function __construct(
        private readonly AuthorizationInterface $authorization,
        private readonly UrlInterface $urlBuilder,
        private readonly Escaper $escaper
    ) {
    }

    public function afterGetText(CacheOutdated $subject, string $result): string
    {
        $links = $this->buildLinks();

        if (!$links) {
            return $result;
        }

        return $result . ' ' . __('Quick actions:') . ' ' . implode(' | ', $links);
    }

    /**
     * @return string[]
     */
    private function buildLinks(): array
    {
        $links = [];

        if ($this->authorization->isAllowed('Magento_Backend::refresh_cache_type')) {
            $links[] = $this->link(
                $this->urlBuilder->getUrl('brocode_cacheeviction/cache/refresh'),
                (string) __('Refresh Invalidated Caches')
            );
        }

        if ($this->authorization->isAllowed('Magento_Backend::flush_magento_cache')) {
            $links[] = $this->link(
                $this->urlBuilder->getUrl('adminhtml/cache/flushSystem'),
                (string) __('Flush Magento Cache')
            );
        }

        if ($this->authorization->isAllowed('Magento_Backend::flush_cache_storage')) {
            $links[] = $this->link(
                $this->urlBuilder->getUrl('adminhtml/cache/flushAll'),
                (string) __('Flush Cache Storage')
            );
        }

        return $links;
    }

    private function link(string $url, string $label): string
    {
        return '<a href="' . $this->escaper->escapeUrl($url) . '">'
            . $this->escaper->escapeHtml($label)
            . '</a>';
    }
}
