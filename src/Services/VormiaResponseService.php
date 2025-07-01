<?php

namespace VormiaQueryPhp\Services;

class VormiaResponseService
{
    /**
     * Format data to the standard VormiaQuery response structure.
     *
     * @param mixed $data
     * @param string $message
     * @param array|null $meta
     * @return array
     */
    public static function format($data, $message = 'Success', $meta = null)
    {
        $meta = $meta ?? [
            'total' => is_array($data) ? count($data) : 1,
            'page' => 1,
            'perPage' => is_array($data) ? count($data) : 1,
        ];
        return [
            'response' => $data,
            'message' => $message,
            'meta' => $meta,
        ];
    }
}
