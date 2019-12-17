<?php

namespace App\Services;

/**
 * Class SimpleCryptService
 * @package App\Services
 */
class SimpleCryptService
{
    /**
     * @param string $data
     * @return mixed
     */
    public static function decode(string $data)
    {
        return (unserialize(base64_decode($data)));
    }

    /**
     * @param string $data
     * @return string
     */
    public static function encode($data) : string
    {
        return (base64_encode(serialize($data)));
    }
}
