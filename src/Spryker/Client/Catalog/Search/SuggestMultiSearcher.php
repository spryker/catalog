<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Catalog\Search;

use Spryker\Client\Catalog\PluginResolver\QueryExpanderPluginResolverInterface;
use Spryker\Client\Catalog\PluginResolver\ResultFormatterPluginResolverInterface;
use Spryker\Client\Search\Dependency\Plugin\SearchStringSetterInterface;
use Spryker\Client\Search\SearchClientInterface;
use Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface;

readonly class SuggestMultiSearcher implements SuggestMultiSearcherInterface
{
    /**
     * @param array<string, array<\Spryker\Client\SearchExtension\Dependency\Plugin\QueryExpanderPluginInterface>> $suggestionQueryExpanderPluginVariants
     * @param array<\Spryker\Client\SearchExtension\Dependency\Plugin\QueryExpanderPluginInterface> $suggestionQueryExpanderPlugins
     * @param array<string, array<\Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface>> $suggestionResultFormatterPluginVariants
     * @param array<\Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface> $suggestionResultFormatterPlugins
     */
    public function __construct(
        protected SearchClientInterface $searchClient,
        protected QueryInterface $suggestSearchQueryPlugin,
        protected QueryExpanderPluginResolverInterface $queryExpanderPluginResolver,
        protected ResultFormatterPluginResolverInterface $resultFormatterPluginResolver,
        protected array $suggestionQueryExpanderPluginVariants,
        protected array $suggestionQueryExpanderPlugins,
        protected array $suggestionResultFormatterPluginVariants,
        protected array $suggestionResultFormatterPlugins,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @param array<string, string> $searchStrings
     * @param array<string, mixed> $requestParameters
     *
     * @return array<string, mixed>
     */
    public function search(array $searchStrings, array $requestParameters): array
    {
        $searchQueries = [];
        $resultFormattersPerQuery = [];

        foreach ($searchStrings as $key => $searchString) {
            $searchQuery = clone $this->suggestSearchQueryPlugin;

            if ($searchQuery instanceof SearchStringSetterInterface) {
                $searchQuery->setSearchString($searchString);
            }

            $searchQuery = $this->searchClient->expandQuery(
                $searchQuery,
                $this->resolveQueryExpanderPlugins($searchQuery),
                $requestParameters,
            );

            $searchQueries[$key] = $searchQuery;
            $resultFormattersPerQuery[$key] = $this->resolveResultFormatters($searchQuery);
        }

        return $this->searchClient->multiSearch($searchQueries, $resultFormattersPerQuery, $requestParameters);
    }

    /**
     * @return array<\Spryker\Client\SearchExtension\Dependency\Plugin\QueryExpanderPluginInterface>
     */
    protected function resolveQueryExpanderPlugins(QueryInterface $searchQuery): array
    {
        return $this->queryExpanderPluginResolver->resolve(
            $searchQuery,
            $this->suggestionQueryExpanderPluginVariants,
            $this->suggestionQueryExpanderPlugins,
        );
    }

    /**
     * @return array<\Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface>
     */
    protected function resolveResultFormatters(QueryInterface $searchQuery): array
    {
        return $this->resultFormatterPluginResolver->resolve(
            $searchQuery,
            $this->suggestionResultFormatterPluginVariants,
            $this->suggestionResultFormatterPlugins,
        );
    }
}
