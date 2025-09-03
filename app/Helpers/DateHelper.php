<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Chuyển đổi từ định dạng dd/mm/yyyy sang yyyy-mm-dd
     * 
     * @param string $dateString
     * @return string|null
     */
    public static function convertToDatabaseFormat($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        // Nếu đã là định dạng yyyy-mm-dd thì trả về luôn
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return $dateString;
        }

        // Chuyển từ dd/mm/yyyy sang yyyy-mm-dd
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateString, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];
            
            // Validate date
            if (checkdate($month, $day, $year)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        return null;
    }

    /**
     * Chuyển đổi từ định dạng yyyy-mm-dd sang dd/mm/yyyy
     * 
     * @param string $dateString
     * @return string|null
     */
    public static function convertToDisplayFormat($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        // Nếu đã là định dạng dd/mm/yyyy thì trả về luôn
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateString)) {
            return $dateString;
        }

        // Chuyển từ yyyy-mm-dd sang dd/mm/yyyy
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dateString, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
            
            return "{$day}/{$month}/{$year}";
        }

        return $dateString;
    }

    /**
     * Validate định dạng dd/mm/yyyy
     * 
     * @param string $dateString
     * @return bool
     */
    public static function isValidDateFormat($dateString)
    {
        if (empty($dateString)) {
            return false;
        }

        if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateString, $matches)) {
            return false;
        }

        $day = (int) $matches[1];
        $month = (int) $matches[2];
        $year = (int) $matches[3];

        return checkdate($month, $day, $year);
    }

    /**
     * Chuyển đổi request data từ dd/mm/yyyy sang yyyy-mm-dd
     * 
     * @param array $data
     * @param array $dateFields
     * @return array
     */
    public static function convertRequestDates($data, $dateFields = [])
    {
        foreach ($dateFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $converted = self::convertToDatabaseFormat($data[$field]);
                if ($converted) {
                    $data[$field] = $converted;
                }
            }
        }

        return $data;
    }
}
