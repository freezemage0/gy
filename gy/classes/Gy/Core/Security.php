<?php
declare(strict_types=1);

namespace Gy\Core;

/**
 * Security - класс с методами для обеспечения безопасности gy framework
 * class work security
 */
final class Security
{

    /**
     * filterInputData
     *  - фильтр входных данных, в присланных данных уберёт лишнее
     * 
     * @param array|string $data - потенциально с вредоносом
     * @return array|string - с большей частью вырезанным вредоносом
     */
    public static function filterInputData(array|string $data): array|string
    {
        if (\is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::filterInputData($value);
            }
        } else {
            return self::clearValue($data);
        }
        return $data;
    }


    /**
     * clearValue 
     *  - обработать одно значение, что бы лишнее не прошло
     *  (если передадут случайно массив то тоже отработает)
     * 
     * @param string $value
     * @return string
     */
    private static function clearValue($value)
    {
        return htmlspecialchars(strip_tags(stripslashes(trim($value))));
    }


}


