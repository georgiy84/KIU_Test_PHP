<?php
namespace App\Helpers;

class JsonHelper
{
    public static function findKeyRecursively(array $array, string $key)
    {
        foreach ($array as $k => $value) {
            if ($k === $key) {
                return $value;
            }
            if (is_array($value)) {
                $result = self::findKeyRecursively($value, $key);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return null;
    }
}
