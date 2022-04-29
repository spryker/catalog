<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Catalog\Plugin\Elasticsearch\ResultFormatter;

use Elastica\ResultSet;
use Generated\Shared\Search\PageIndexMap;
use Generated\Shared\Transfer\ProductConcretePageSearchTransfer;
use Spryker\Client\Search\Plugin\Elasticsearch\ResultFormatter\AbstractElasticsearchResultFormatterPlugin;

/**
 * @method \Spryker\Client\Catalog\CatalogFactory getFactory()
 */
class ProductConcreteCatalogSearchResultFormatterPlugin extends AbstractElasticsearchResultFormatterPlugin
{
    /**
     * @var string
     */
    public const NAME = 'ProductConcreteCatalogSearchResultFormatterPlugin';

    /**
     * @var string
     */
    public const KEY_ID_PRODUCT = 'id_product';

    /**
     * @api
     *
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * @param \Elastica\ResultSet $searchResult
     * @param array<string, mixed> $requestParameters
     *
     * @return array
     */
    protected function formatSearchResult(ResultSet $searchResult, array $requestParameters): array
    {
        $productConcreteSetPageResults = [];

        foreach ($searchResult->getResults() as $document) {
            $productConcreteSetPageResults[] = $this->mapToTransfer(
                $document->getSource()[PageIndexMap::SEARCH_RESULT_DATA],
            );
        }

        return $productConcreteSetPageResults;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return \Generated\Shared\Transfer\ProductConcretePageSearchTransfer
     */
    protected function mapToTransfer(array $data): ProductConcretePageSearchTransfer
    {
        $productConcretePageSearchTransfer = new ProductConcretePageSearchTransfer();
        $productConcretePageSearchTransfer->fromArray($data, true);
        $productConcretePageSearchTransfer->setFkProduct($data[static::KEY_ID_PRODUCT]);

        return $productConcretePageSearchTransfer;
    }
}
