<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Catalog\Plugin\Elasticsearch\ResultFormatter;

use Algolia\AlgoliaSearch\Iterators\ObjectIterator;
use Generated\Shared\Search\PageIndexMap;
use Generated\Shared\Transfer\SearchResultTransfer;
use Spryker\Client\Search\Plugin\Elasticsearch\ResultFormatter\AbstractElasticsearchResultFormatterPlugin;

/**
 * @method \Spryker\Client\Catalog\CatalogFactory getFactory()
 */
class RawCatalogSearchResultFormatterPlugin extends AbstractElasticsearchResultFormatterPlugin
{
    /**
     * @var string
     */
    public const NAME = 'products';

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param \Elastica\ResultSet|\Generated\Shared\Transfer\SearchResultTransfer $searchResult
     * @param array $requestParameters
     *
     * @return array
     */
    protected function formatSearchResult(/*ResultSet*/ $searchResult, array $requestParameters)
    {
        if ($searchResult instanceof SearchResultTransfer) {
            return $this->formatSearchResultTransfer($searchResult);
        }

        $products = [];

        foreach ($searchResult->getResults() as $document) {
            $products[] = $document->getSource()[PageIndexMap::SEARCH_RESULT_DATA];
        }

        return $products;
    }

    /**
     * @param \Generated\Shared\Transfer\SearchResultTransfer $searchResultTransfer
     *
     * @return array
     */
    protected function formatSearchResultTransfer(SearchResultTransfer $searchResultTransfer): array
    {
        $products = [];

        foreach ($searchResultTransfer->getResults() as $item) {
            $products[] = $item[PageIndexMap::SEARCH_RESULT_DATA];
        }

        return $products;
    }
}
