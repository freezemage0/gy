<?php

declare(strict_types=1);

namespace Gy\Core;

/**
 * class Captcha - для работы с капчей
 */
final class Captcha
{

    // символы которые будут в капче
    //private static $letters = 'abcdefghijklmnopqrstuvwxyzABCDRFGHIJKLMNOPQRSTUVWXYZ0123456789';
    //  убрал ноль и буквы о, что бы не было путаниц
    private const string CHARACTERS = 'aAbBcCdDeEfFgG1hHiI2jJkK3lLm4MnN5p6PqQr7RsSt8TuUv9VwWxXyYzZ';

    private $count = 5; // количество символов
    private $code = 5; // код капчи
    // путь до шрифта (шрифт нужен что бы поворачивать буквы)
    public static string $defaultUrlFonts = "/fonts/18018.otf"; //

    public function __construct(
        private readonly ?string $fontPath = null,
    ) {
        $oldCodeCaptcha = self::getCaptchaValue();

        if (!empty($oldCodeCaptcha)) {
            $this->code = $oldCodeCaptcha;
        } else {
            $this->generateCaptchaCode();
        }
    }

    /**
     * createNewCapchaData
     *  - сгенерирует новые символы и сохранит
     *
     */
    public function generateCaptchaCode(): void
    {
        $this->setCapchaValue(self::getRandLetters($this->count));
    }

    /**
     * clearCapcha - очистить текущий код капчи
     */
    public static function clear(): void
    {
        unset($_SESSION['captcha']);
    }

    /**
     * chackCapcha - проверить код с установленным кодом в капче
     * @param string $captcha
     * @return boolean
     */
    public function validate(string $captcha): bool
    {
        $result = false;
        // проверит код с капчи
        // всё приводится к верхнему регистру что бы пользователю проще 
        //     было угадать капчу

        $captcha = $_SESSION['captcha'];

        if ($_SESSION['captcha'] == strtoupper($captcha)) {
            $result = true;
        }
        self::clear();

        if ($result === false) {
            // если проверка капчи не прошла то сгенерить новый код капчи
            $this->generateCaptchaCode();
        }

        return $result;
    }

    /**
     * setCapchaValue - установить код капчи
     * @param string $value
     */
    private function setCapchaValue(string $value): void
    {
        // задать код в классе
        $this->code = $value;

        // записать в сессию значение
        $_SESSION['captcha'] = \strtoupper($this->code);
    }

    /**
     * getCapchaValue
     *  - получить код капчи из сессии
     *
     * @return string
     */
    private function getCaptchaValue(): ?string
    {
        return $_SESSION['captcha'] ?? null;
    }

    /**
     * getImageCapcha - вызовет createImageCapcha с нужным кодом
     * это всё чтобы нарисовать картинку капчи
     */
    public function getImageCapcha()
    {
        // задаст стандартные настройки и вызовет createImageCapcha для определённого кода
        $this->createImageCapcha($this->code);
    }

    /**
     * createImageCapcha - нарисовать картинку капчи по заданному коду
     * @param string $code
     */
    private function createImageCapcha($code)
    {
        // постоянные ширина и высота
        $gX = 100;
        $gY = 50;

        ob_clean(); // очистить вывод до этого момента
        header("Content-type: image/png");
        $img = imagecreatetruecolor($gX, $gY);

        // определяем белый цвет
        $white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);

        // делаем фон белым
        imagefill($img, 1, 1, $white);

        // нарисовать шум (рендомной длинны в рендомные стороны)
        $j = rand(5, 10);
        for ($i = 0; $i < $j; $i++) {
            // произвольно задать цвет
            $r = rand(50, 230);
            $g = rand(50, 230);
            $b = rand(50, 230);
            $textColor = imagecolorallocate($img, $r, $g, $b);

            // Рисуем линию
            $x1 = rand(0, $gX);
            $x2 = rand(0, $gX);
            $y1 = rand(0, $gY);
            $y2 = rand(0, $gY);

            imageline($img, $x1, $y1, $x2, $y2, $textColor);
        }

        // рисуется код капчи
        for ($i = 0; $i < strlen($code); $i++) {
            // произвольно задать цвет
            $r = rand(50, 230);
            $g = rand(50, 230);
            $b = rand(50, 230);
            $textColor = imagecolorallocate($img, $r, $g, $b);

            $font = rand(5, 7); // размер шрифта

            $j = rand(0, 1);
            if ($j == 0) {
                $y = sin($i) * 10;
            } else {
                $y = cos($i) * 10;
            }

            $x = rand(3, 10);

            if ($this->fontPath === null) {
                // если не задан шрифт то будет штатным рисоваться но без поворота букв
                imagestring($img, $font, $x + ($i * 20), 10 + $y, $code[$i], $textColor);
                imagestring($img, $font, $x + 1 + ($i * 20), 11 + $y, $code[$i], $textColor);
            } else {
                // иначе заданным шрифтом рисует с поворотом букв
                $a = 30 - rand(0, 60); // угол от -30 до 30
                imagettftext($img, $font * 3, $a, $x + ($i * 20), 30 + $y, $textColor, $this->fontPath, $code[$i]);
                imagettftext($img, $font * 3, $a, $x + 1 + ($i * 20), 31 + $y, $textColor, $this->fontPath, $code[$i]);
            }
        }

        imagepng($img);
        imagedestroy($img);
        //die(); // что бы не было вывода после
    }

    /**
     * getRandLetters - получить рендомный набор символов, указанной длинны
     * @param int $count
     * @return string
     */
    public function getRandLetters($count)
    {
        $randLetters = '';
        for ($i = 0; $i < $count; $i++) {
            $randLetters .= self::getRandLetter();
        }
        return $randLetters;
    }

    /**
     * getRandLetter - получить произвольный символ из заданного набора символов self::$arrayLetters
     * @return string
     */
    private function getRandLetter()
    {
        $randLetter = \rand(0, \strlen(self::CHARACTERS) - 1);

        return \substr(self::CHARACTERS, $randLetter, 1);
    }
}
