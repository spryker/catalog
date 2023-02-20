<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Catalog\PluginResolver;

use Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface;

class ResultFormatterPluginResolver extends AbstractDependentPluginResolver implements ResultFormatterPluginResolverInterface
{
    /**
     * @param \Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface $query
     * @param array<string, array<\Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface>> $resultFormatterPluginVariants
     * @param array<\Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface> $defaultResultFormatterPlugins
     *
     * @return array<\Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface>
     */
    public function resolve(
        QueryInterface $query,
        array $resultFormatterPluginVariants,
        array $defaultResultFormatterPlugins
    ): array {
        /** @var array<\Spryker\Client\SearchExtension\Dependency\Plugin\ResultFormatterPluginInterface> $applicableResultFormatterPlugins */
        $applicableResultFormatterPlugins = $this->resolveByType($query, $resultFormatterPluginVariants);

        if ($applicableResultFormatterPlugins) {
            return $applicableResultFormatterPlugins;
        }

        return $defaultResultFormatterPlugins;
    }
}
