<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Catalog;

use Generated\Shared\Transfer\ProductConcreteCriteriaFilterTransfer;
use Spryker\Client\Kernel\AbstractClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method \Spryker\Client\Catalog\CatalogFactory getFactory()
 */
class CatalogClient extends AbstractClient implements CatalogClientInterface
{
    /**
     * @uses \Spryker\Client\Catalog\Plugin\Elasticsearch\ResultFormatter\RawCatalogSearchResultFormatterPlugin::NAME
     */
    protected const string CATALOG_SEARCH_PRODUCTS_KEY = 'products';

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $searchString
     * @param array<string, mixed> $requestParameters
     *
     * @return array
     */
    public function catalogSearch($searchString, array $requestParameters = [])
    {
        $searchQuery = $this
            ->getFactory()
            ->createCatalogSearchQuery($searchString);

        $searchQuery = $this
            ->getFactory()
            ->getSearchClient()
            ->expandQuery(
                $searchQuery,
                $this->getFactory()->getCatalogSearchQueryExpanderPlugins($searchQuery),
                $requestParameters,
            );

        $resultFormatters = $this
            ->getFactory()
            ->getCatalogSearchResultFormatters($searchQuery);

        return $this
            ->getFactory()
            ->getSearchClient()
            ->search($searchQuery, $resultFormatters, $requestParameters);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $searchString
     * @param array<string, mixed> $requestParameters
     *
     * @return array<string, mixed>
     */
    public function catalogSuggestSearch($searchString, array $requestParameters = [])
    {
        $searchQuery = $this
            ->getFactory()
            ->createSuggestSearchQuery($searchString);

        $searchQuery = $this
            ->getFactory()
            ->getSearchClient()
            ->expandQuery($searchQuery, $this->getFactory()->getSuggestionQueryExpanderPlugins($searchQuery), $requestParameters);

        $resultFormatters = $this
            ->getFactory()
            ->getSuggestionResultFormatters($searchQuery);

        $result = $this
            ->getFactory()
            ->getSearchClient()
            ->search($searchQuery, $resultFormatters, $requestParameters);

        if (!$this->getFactory()->getConfig()->isProductConcreteSearchInStorageEnabled()) {
            return $result;
        }

        foreach ($this->getFactory()->getProductConcreteSuggestionEnricherPlugins() as $enricherPlugin) {
            $result = $enricherPlugin->enrichSuggestSearchResultWithCompletion($result, $searchString);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array<string, string> $searchStrings
     * @param array<string, mixed> $requestParameters
     *
     * @return array<string, mixed>
     */
    public function catalogSuggestMultiSearch(array $searchStrings, array $requestParameters = []): array
    {
        return $this->getFactory()->createSuggestMultiSearcher()->search($searchStrings, $requestParameters);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string
     */
    public function getCatalogViewMode(Request $request)
    {
        return $this->getFactory()
            ->createCatalogViewModePersistence()
            ->getViewMode($request);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $mode
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setCatalogViewMode($mode, Response $response)
    {
        return $this->getFactory()
            ->createCatalogViewModePersistence()
            ->setViewMode($mode, $response);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $searchString
     * @param array<string, mixed> $requestParameters
     *
     * @return int
     */
    public function catalogSearchCount(string $searchString, array $requestParameters): int
    {
        $searchClient = $this
            ->getFactory()
            ->getSearchClient();
        $searchQuery = $this
            ->getFactory()
            ->createCatalogSearchQuery($searchString);
        $searchQuery = $searchClient
            ->expandQuery($searchQuery, $this->getFactory()->getCatalogSearchCountQueryExpanderPlugins($searchQuery), $requestParameters);
        $searchResult = $searchClient->search($searchQuery, [], $requestParameters);

        foreach ($this->getFactory()->getSearchResultCountPlugins() as $searchResultCountPlugin) {
            $totalCount = $searchResultCountPlugin->findTotalCount($searchResult);

            if ($totalCount !== null) {
                return $totalCount;
            }
        }

        return is_object($searchResult) && method_exists($searchResult, 'getTotalHits') ? $searchResult->getTotalHits() : 0;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductConcreteCriteriaFilterTransfer $productConcreteCriteriaFilterTransfer
     *
     * @return \Elastica\ResultSet|array
     */
    public function searchProductConcretesByFullText(ProductConcreteCriteriaFilterTransfer $productConcreteCriteriaFilterTransfer)
    {
        if ($this->getFactory()->getConfig()->isProductConcreteSearchInStorageEnabled()) {
            return $this->executeProductConcreteSearchPlugins($productConcreteCriteriaFilterTransfer);
        }

        $searchQuery = $this->getFactory()->createProductConcreteCatalogSearchQuery((string)$productConcreteCriteriaFilterTransfer->getSearchString());
        $requestParameters = $productConcreteCriteriaFilterTransfer->getRequestParams();

        if ($productConcreteCriteriaFilterTransfer->getLimit() !== null) {
            $requestParameters[$this->getFactory()->getConfig()->getItemsPerPageParameterName()] = $productConcreteCriteriaFilterTransfer->getLimit();
        }

        $searchQuery = $this
            ->getFactory()
            ->getSearchClient()
            ->expandQuery(
                $searchQuery,
                $this->getFactory()->getProductConcreteCatalogSearchQueryExpanderPlugins($searchQuery),
                $requestParameters,
            );
        $resultFormatters = $this
            ->getFactory()
            ->getProductConcreteCatalogSearchResultFormatters($searchQuery);

        return $this
            ->getFactory()
            ->getSearchClient()
            ->search($searchQuery, $resultFormatters, $requestParameters);
    }

    protected function executeProductConcreteSearchPlugins(ProductConcreteCriteriaFilterTransfer $productConcreteCriteriaFilterTransfer): array
    {
        foreach ($this->getFactory()->getProductConcreteStorageSearchPlugins() as $plugin) {
            if (!$plugin->isApplicable($productConcreteCriteriaFilterTransfer)) {
                continue;
            }

            $skuResult = $plugin->searchProductConcretes($productConcreteCriteriaFilterTransfer);

            if ($skuResult) {
                return $skuResult;
            }

            $requestParameters = (array)$productConcreteCriteriaFilterTransfer->getRequestParams();

            if ($productConcreteCriteriaFilterTransfer->getLimit() !== null) {
                $requestParameters[$this->getFactory()->getConfig()->getItemsPerPageParameterName()] = $productConcreteCriteriaFilterTransfer->getLimit();
            }

            $catalogSearchResults = $this->catalogSearch((string)$productConcreteCriteriaFilterTransfer->getSearchString(), $requestParameters);
            $abstractSearchResults = $catalogSearchResults[static::CATALOG_SEARCH_PRODUCTS_KEY] ?? [];

            return $plugin->searchProductConcretesByAbstractSearchResults($abstractSearchResults);
        }

        return [];
    }
}
