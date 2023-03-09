<?php

namespace common\components\request;

/**
 * Class Serializer
 * @package common\components\request
 */
class Serializer
{
    /**
     * Convert strings: null, false, true types to null and bool types
     *
     * @param $params
     * @returns void
     */
    public function serialize(&$params): void
    {
        foreach ($params as &$item) {
            if (is_array($item)) {
                $this->serialize($item);
            } elseif (is_string($item)) {
                if (in_array($item, ['null', 'Null', 'NULL'])) {
                    $item = null;
                } elseif (in_array($item, ['false', 'False', 'FALSE'])) {
                    $item = false;
                } elseif (in_array($item, ['true', 'True', 'TRUE'])) {
                    $item = true;
                }
            }
        }
    }
}
