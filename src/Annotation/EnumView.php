<?php
/**
 * Created by PhpStorm.
 * Date:  2022/3/17
 * Time:  10:10 PM
 */

declare(strict_types=1);

namespace Lengbin\ErrorCode\Annotation;

use Attribute;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class EnumView
{
    public const ENUM_MESSAGE = 1;
    public const ENUM_VALUE = 2;
    public const ENUM_ALL = 3;

    public int $flags;

    public function __construct(int $flags = self::ENUM_ALL)
    {
        $this->flags = $flags;
    }
}
