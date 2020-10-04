<?php

namespace Lengbin\ErrorCode;

use Lengbin\Helper\YiiSoft\Arrays\ArrayHelper;
use MabeEnum\Enum;
use MabeEnum\EnumSerializableTrait;
use Roave\BetterReflection\BetterReflection;
use Serializable;

class BaseEnum extends Enum implements Serializable
{
    use EnumSerializableTrait;

    /**
     * @param string $doc
     * @param array  $previous
     *
     * @return array
     */
    protected function parse(string $doc, array $previous = [])
    {
        $pattern = '/\\@(\\w+)\\(\\"(.+)\\"\\)/U';
        if (preg_match_all($pattern, $doc, $result)) {
            if (isset($result[1], $result[2])) {
                $keys = $result[1];
                $values = $result[2];

                foreach ($keys as $i => $key) {
                    if (isset($values[$i])) {
                        $previous[strtolower($key)] = $values[$i];
                    }
                }
            }
        }
        return $previous;
    }

    /**
     * 获得
     * @return string
     */
    public function getMessage(): string
    {
        $className = get_called_class();
        $classInfo = (new BetterReflection())->classReflector()->reflect($className);
        $constantDocComment = $classInfo->getReflectionConstant($this->getName())->getDocComment();
        return ArrayHelper::getValue($this->parse($constantDocComment), 'message', '');
    }

}
