<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Catalog\Plugin\Elasticsearch\Query;

use Elastica\Query;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchAll;
use Elastica\Query\MatchQuery;
use Elastica\Query\MultiMatch;
use Generated\Shared\Search\PageIndexMap;
use Generated\Shared\Transfer\SearchContextTransfer;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\Search\Dependency\Plugin\QueryInterface;
use Spryker\Client\Search\Dependency\Plugin\SearchStringSetterInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\SearchContextAwareQueryInterface;

/**
 * @method \Spryker\Client\Catalog\CatalogFactory getFactory()
 * @method \Spryker\Client\Catalog\CatalogConfig getConfig()
 */
class ProductConcreteCatalogSearchQueryPlugin extends AbstractPlugin implements QueryInterface, SearchContextAwareQueryInterface, SearchStringSetterInterface
{
    /**
     * @var string
     */
    protected const SOURCE_IDENTIFIER = 'page';

    /**
     * @uses \Spryker\Shared\ProductPageSearch\ProductPageSearchConstants::PRODUCT_CONCRETE_RESOURCE_NAME
     *
     * @var string
     */
    protected const PRODUCT_CONCRETE_RESOURCE_NAME = 'product_concrete';

    /**
     * @var \Elastica\Query
     */
    protected $query;

    /**
     * @var string
     */
    protected $searchString = '';

    /**
     * @var \Generated\Shared\Transfer\SearchContextTransfer
     */
    protected $searchContextTransfer;

    /**
     * Specification:
     * - Builds score based on multimatch cross fileds query type.
     */
    public function __construct()
    {
        $this->createQuery();
    }

    /**
     * {@inheritDoc}
     * - Returns query object for concrete products catalog search.
     *
     * @api
     *
     * @return \Elastica\Query
     */
    public function getSearchQuery(): Query
    {
        return $this->query;
    }

    /**
     * {@inheritDoc}
     * - Builds score based on multimatch cross fileds query type.
     *
     * @api
     *
     * @param string $searchString
     *
     * @return void
     */
    public function setSearchString($searchString)
    {
        $this->searchString = $searchString;
        $this->createQuery();
    }

    /**
     * {@inheritDoc}
     * - Defines context for concrete products catalog search.
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\SearchContextTransfer
     */
    public function getSearchContext(): SearchContextTransfer
    {
        if (!$this->hasSearchContext()) {
            $this->setupDefaultSearchContext();
        }

        return $this->searchContextTransfer;
    }

    /**
     * {@inheritDoc}
     * - Sets context for concrete products catalog search.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SearchContextTransfer $searchContextTransfer
     *
     * @return void
     */
    public function setSearchContext(SearchContextTransfer $searchContextTransfer): void
    {
        $this->searchContextTransfer = $searchContextTransfer;
    }

    /**
     * @return void
     */
    protected function setupDefaultSearchContext(): void
    {
        $searchContextTransfer = new SearchContextTransfer();
        $searchContextTransfer->setSourceIdentifier(static::SOURCE_IDENTIFIER);

        $this->searchContextTransfer = $searchContextTransfer;
    }

    /**
     * @return \Elastica\Query
     */
    protected function createQuery(): Query
    {
        $this->query = new Query();
        $this->addFulltextSearchToQuery();
        $this->setQuerySource();

        return $this->query;
    }

    /**
     * @return void
     */
    protected function addFulltextSearchToQuery(): void
    {
        $matchQuery = $this->createFulltextSearchQuery();
        $boolQuery = $this->createBoolQuery($matchQuery);
        $this->query->setQuery($boolQuery);
    }

    /**
     * @return \Elastica\Query\AbstractQuery
     */
    protected function createFulltextSearchQuery(): AbstractQuery
    {
        if ($this->searchString === '') {
            return new MatchAll();
        }

        $fields = [
            PageIndexMap::FULL_TEXT_BOOSTED . '^' . $this->getFullTextBoostedBoostingValue(),
        ];

        $matchQuery = (new MultiMatch())
            ->setFields($fields)
            ->setQuery($this->searchString)
            ->setType(MultiMatch::TYPE_CROSS_FIELDS);

        return $matchQuery;
    }

    /**
     * @param \Elastica\Query\AbstractQuery $matchQuery
     *
     * @return \Elastica\Query\BoolQuery
     */
    protected function createBoolQuery(AbstractQuery $matchQuery): AbstractQuery
    {
        $boolQuery = new BoolQuery();
        $boolQuery->addMust($matchQuery);
        $boolQuery = $this->setTypeFilter($boolQuery);

        return $boolQuery;
    }

    /**
     * @param \Elastica\Query\BoolQuery $boolQuery
     *
     * @return \Elastica\Query\BoolQuery
     */
    protected function setTypeFilter(BoolQuery $boolQuery): BoolQuery
    {
        $typeFilter = $this->getMatchQuery();
        $typeFilter->setField(PageIndexMap::TYPE, static::PRODUCT_CONCRETE_RESOURCE_NAME);
        $boolQuery->addMust($typeFilter);

        return $boolQuery;
    }

    /**
     * @return void
     */
    protected function setQuerySource(): void
    {
        $this->query->setSource([PageIndexMap::SEARCH_RESULT_DATA]);
    }

    /**
     * @return int
     */
    protected function getFullTextBoostedBoostingValue(): int
    {
        return $this->getFactory()
            ->getCatalogConfig()
            ->getElasticsearchFullTextBoostedBoostingValue();
    }

    /**
     * @return bool
     */
    protected function hasSearchContext(): bool
    {
        return (bool)$this->searchContextTransfer;
    }

    /**
     * For compatibility with PHP 8.
     *
     * @return \Elastica\Query\MatchQuery|\Elastica\Query\Match
     */
    protected function getMatchQuery()
    {
        $matchQueryClassName = class_exists(MatchQuery::class)
            ? MatchQuery::class
            : '\Elastica\Query\Match';

        return new $matchQueryClassName();
    }
}
