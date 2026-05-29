<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Catalog;

use Generated\Shared\Transfer\PaginationConfigTransfer;
use Spryker\Client\Kernel\AbstractBundleConfig;

class CatalogConfig extends AbstractBundleConfig
{
    /**
     * @deprecated Use {@link \Spryker\Client\Catalog\CatalogConfig::ELASTICSEARCH_FULL_TEXT_BOOSTED_BOOSTING_VALUE} instead.
     *
     * @uses \Spryker\Shared\ProductPageSearch\ProductPageSearchConstants::FULL_TEXT_BOOSTED_BOOSTING_VALUE
     *
     * @var string
     */
    protected const FULL_TEXT_BOOSTED_BOOSTING_VALUE = 'FULL_TEXT_BOOSTED_BOOSTING_VALUE';

    /**
     * @var string
     */
    protected const PAGINATION_PARAMETER_NAME_PAGE = 'page';

    /**
     * @var string
     */
    protected const PAGINATION_ITEMS_PER_PAGE_PARAMETER_NAME = 'ipp';

    /**
     * @var int
     */
    protected const PAGINATION_DEFAULT_ITEMS_PER_PAGE = 10;

    /**
     * @var array<int>
     */
    protected const PAGINATION_VALID_ITEMS_PER_PAGE = [
        10,
    ];

    /**
     * @var array<int>
     */
    protected const PAGINATION_CATALOG_SEARCH_VALID_ITEMS_PER_PAGE = [12, 24, 36];

    /**
     * @var int
     */
    protected const PAGINATION_CATALOG_SEARCH_DEFAULT_ITEMS_PER_PAGE = 10;

    /**
     * @uses \Spryker\Shared\SearchElasticsearch\SearchElasticsearchConstants::FULL_TEXT_BOOSTED_BOOSTING_VALUE
     *
     * @var string
     */
    protected const ELASTICSEARCH_FULL_TEXT_BOOSTED_BOOSTING_VALUE = 'SEARCH_ELASTICSEARCH:FULL_TEXT_BOOSTED_BOOSTING_VALUE';

    /**
     * @uses \Spryker\Shared\ProductPageSearch\ProductPageSearchConstants::PRODUCT_CONCRETE_SEARCH_IN_STORAGE_ENABLED
     */
    protected const string PRODUCT_CONCRETE_SEARCH_IN_STORAGE_ENABLED = 'ProductPageSearch:PRODUCT_CONCRETE_SEARCH_IN_STORAGE_ENABLED';

    /**
     * @api
     *
     * @deprecated Use {@link \Spryker\Client\Catalog\CatalogConfig::getElasticsearchFullTextBoostedBoostingValue()} instead.
     *
     * @return int
     */
    public function getFullTextBoostedBoostingValue(): int
    {
        return $this->get(static::FULL_TEXT_BOOSTED_BOOSTING_VALUE);
    }

    /**
     * @api
     *
     * @return string
     */
    public function getItemsPerPageParameterName(): string
    {
        return static::PAGINATION_ITEMS_PER_PAGE_PARAMETER_NAME;
    }

    /**
     * @api
     *
     * @return \Generated\Shared\Transfer\PaginationConfigTransfer
     */
    public function getPaginationConfig(): PaginationConfigTransfer
    {
        $paginationConfigTransfer = new PaginationConfigTransfer();
        $paginationConfigTransfer
            ->setParameterName(static::PAGINATION_PARAMETER_NAME_PAGE)
            ->setItemsPerPageParameterName(static::PAGINATION_ITEMS_PER_PAGE_PARAMETER_NAME)
            ->setDefaultItemsPerPage(static::PAGINATION_DEFAULT_ITEMS_PER_PAGE)
            ->setValidItemsPerPageOptions(static::PAGINATION_VALID_ITEMS_PER_PAGE);

        return $paginationConfigTransfer;
    }

    /**
     * @api
     *
     * @return \Generated\Shared\Transfer\PaginationConfigTransfer
     */
    public function getCatalogSearchPaginationConfigTransfer(): PaginationConfigTransfer
    {
        $paginationConfigTransfer = (new PaginationConfigTransfer())
            ->setParameterName(static::PAGINATION_PARAMETER_NAME_PAGE)
            ->setItemsPerPageParameterName(static::PAGINATION_ITEMS_PER_PAGE_PARAMETER_NAME)
            ->setDefaultItemsPerPage(static::PAGINATION_CATALOG_SEARCH_DEFAULT_ITEMS_PER_PAGE)
            ->setValidItemsPerPageOptions(static::PAGINATION_CATALOG_SEARCH_VALID_ITEMS_PER_PAGE);

        return $paginationConfigTransfer;
    }

    /**
     * @api
     *
     * @return int
     */
    public function getElasticsearchFullTextBoostedBoostingValue(): int
    {
        return $this->get(
            static::ELASTICSEARCH_FULL_TEXT_BOOSTED_BOOSTING_VALUE,
            $this->get(static::FULL_TEXT_BOOSTED_BOOSTING_VALUE, 1),
        );
    }

    /**
     * Specification:
     * - When enabled, concrete product search is served from Redis (product storage) instead of OpenSearch.
     * - The search performs a direct SKU lookup first, then falls back to abstract name search.
     * - Disabled by default; enable only after product-concrete storage data has been fully published.
     *
     * @api
     *
     * @return bool
     */
    public function isProductConcreteSearchInStorageEnabled(): bool
    {
        return $this->get(static::PRODUCT_CONCRETE_SEARCH_IN_STORAGE_ENABLED, false);
    }
}
