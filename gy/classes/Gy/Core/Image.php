<?php

namespace Gy\Core;

if (!defined("GY_CORE") && (GY_CORE !== true)) die( "gy: err include core" );

/* Image class work with image // wrapper class php GD
 * Image класс для работы с изображениями // обёртка класса php GD
 */
final class Image
{

    /** 
     * imageResized function compression image (jpeg)
     * imageResized - сжимает изображения (поддерживает пока jpeg)
     * @param string $origin - ссылка на изображение которое нужно сжать // url input image
     * @param string $destination - ссылка куда сохранить изображение // url save image
     * @param int $compression - сжатие (0-100) 100 - это наилучшее качество // compression (0-100) 100 max quality
     * @return bool true or false
     */
    public function resizeImage(string $origin, string $destination, int $compression): bool
    {
        $imageInfo = getimagesize($origin);

        if ($imageInfo[2] !== 2) { // jpeg ли это ? // if jpeg image
            return false;
        }

        $image = imageCreateFromJpeg($origin);// загрузить изображение сжимаемое // loading Image
        $destinationImage = imageCreateTrueColor($imageInfo[0], $imageInfo[1]); // создать изображение для сохранение с тем же разрешением // create out image

        imageCopyResampled($destinationImage, $image, 0, 0, 0, 0, $imageInfo[0], $imageInfo[1], $imageInfo[0], $imageInfo[1]);
        imageJpeg($destinationImage, $destination, $compression); // сохраняем // save out image
        imageDestroy($destinationImage); // очищаем память // clear memory

        return true;
    }

}
