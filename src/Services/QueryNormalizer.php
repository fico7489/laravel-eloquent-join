<?php

namespace Fico7489\Laravel\EloquentJoin\Services;

class QueryNormalizer
{
    public static function normalize($parameters)
    {
        $firstParam = array_values($parameters)[0];

        if (is_array($firstParam)) {
            $parametersNew = [];
            foreach ($firstParam as $k => $v) {
                $parametersNew = [$k, '=', $v];
            }
        } elseif (count($parameters) == 2) {
            $secondParam = array_values($parameters)[1];
            $parametersNew = [$firstParam, '=', $secondParam];
        } else {
            $parametersNew = $parameters;
        }

        return $parametersNew;
    }

    public static function normalizeScope($parameters)
    {
        unset($parameters[0]);
        $parameters = array_values($parameters);

        return self::normalize($parameters);
    }
}
