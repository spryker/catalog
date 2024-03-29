<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Catalog\PluginResolver;

use Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface;

interface QueryPluginResolverInterface
{
    /**
     * @param array<\Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface> $queryPluginVariants
     * @param \Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface $defaultQueryPlugin
     *
     * @return \Spryker\Client\SearchExtension\Dependency\Plugin\QueryInterface
     */
    public function resolve(array $queryPluginVariants, QueryInterface $defaultQueryPlugin): QueryInterface;
}
