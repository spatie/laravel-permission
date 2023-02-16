<?php

namespace Spatie\Permission\Tests\TestModels;

use Spatie\Permission\WildcardPermission as BaseWildcardPermission;

class WildcardPermission extends BaseWildcardPermission
{
    /** @var string */
    public const WILDCARD_TOKEN = '@';

    /** @var string */
    public const PART_DELIMITER = ':';

    /** @var string */
    public const SUBPART_DELIMITER = ';';
}
