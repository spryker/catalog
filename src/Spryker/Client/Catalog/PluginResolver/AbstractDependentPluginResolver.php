<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Catalog\PluginResolver;

use Spryker\Client\CatalogExtension\Dependency\Plugin\SearchTypeIdentifierInterface;

abstract class AbstractDependentPluginResolver
{
    /**
     * @param object $mainPlugin
     * @param array<string, array<object>> $dependentPlugins
     *
     * @return array<object>
     */
    public function resolveByType(object $mainPlugin, array $dependentPlugins): array
    {
        if ($mainPlugin instanceof SearchTypeIdentifierInterface && !empty($dependentPlugins[$mainPlugin->getSearchType()])) {
            return $dependentPlugins[$mainPlugin->getSearchType()];
        }

        return [];
    }
}
