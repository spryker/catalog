<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Catalog\Search;

interface SuggestMultiSearcherInterface
{
    /**
     * Executes a suggestion multi-search for multiple search strings in a single request.
     *
     * @param array<string, string> $searchStrings
     * @param array<string, mixed> $requestParameters
     *
     * @return array<string, mixed>
     */
    public function search(array $searchStrings, array $requestParameters): array;
}
