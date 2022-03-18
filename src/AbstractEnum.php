<?php

namespace Lengbin\ErrorCode;

use Lengbin\ErrorCode\Annotation\EnumMessage;
use Lengbin\Helper\YiiSoft\Arrays\ArrayHelper;
use MabeEnum\Enum;
use MabeEnum\EnumSerializableTrait;
use ReflectionClass;
use ReflectionClassConstant;
use Serializable;

class AbstractEnum extends Enum implements Serializable
{
    use EnumSerializableTrait;

    /**
     * @param string $doc
     * @param array  $previous
     *
     * @return array
     */
    protected static function parse(string $doc, array $previous = [])
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

    protected static function handleMessage($constant, array $replace = [])
    {
        $message = '';
        if (version_compare(PHP_VERSION, '8.0.0', '>')) {
            $attributes = $constant->getAttributes(EnumMessage::class);
            if (!empty($attributes)) {
                $message = $attributes[0]->newInstance()->message;
            }
        }

        if (empty($message)) {
            $constantDocComment = $constant->getDocComment();
            $message = ArrayHelper::getValue(self::parse($constantDocComment), 'message', '');
        }
        return strtr($message, $replace);
    }

    /**
     * 获得
     *
     * @param array $replace
     *
     * @return string
     */
    public function getMessage(array $replace = []): string
    {
        $classname = get_called_class();
        $constant = new ReflectionClassConstant($classname, $this->getName());
        if ($constant === null) {
            return '';
        }
        return self::handleMessage($constant, $replace);
    }

    public static function getMessages(array $replace = []): array
    {
        $classname = get_called_class();
        $reflect = new ReflectionClass($classname);
        $constants = $reflect->getConstants();
        $data = [];
        foreach ($constants as $constant) {
            $data[] = self::handleMessage($constant, $replace);
        }
        return $data;
    }

    /**
     * map
     * @return array
     */
    public static function getMapJson()
    {
        $data = [];
        $values = static::getValues();
        foreach ($values as $value) {
            $data[] = [
                'value'   => $value,
                'message' => static::byValue($value)->getMessage(),
            ];
        }
        return $data;
    }

}
