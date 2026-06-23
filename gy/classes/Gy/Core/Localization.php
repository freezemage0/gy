<?php

namespace Gy\Core;


final class Localization {
    private array $textLang; // тексты определённого языка

    public function __construct($url, $fileName, $lang) {
        if (!empty($url) && !empty($fileName) && !empty($lang)) {
            //load array text language
            $this->textLang = $this->loadMessages($url . '/lang_' . $fileName . '.php', $lang);
        }
    }

    /**
     * autoLoadLang
     * авто загрузка языкового файла для файла где вызывается эта функция
     *    нужно передать в какой файле вызывается (название компонента например, шаблона)
     *
     * @param namePHPFile    - файл в котором будет вызываться данный класс // там где нужен языковой файл
     * @return
     */

    public function autoLoadLang($namePHPFile, $lang) {

    }

    /**
     *  getMessage вернуть текст для заданной переменной текущего языка
     *
     * @param string $code - передать переменную
     * @return вернёт текст или false
     */
    public function getMessage(string $code): ?string {
        return $this->textLang[$code] ?? null;
    }

    /**
     * getArrLangFromFilre загрузить массив с текстом нужного языка // load array text language
     *
     * @param string $urlFile ссылка на загружаемый файл // url load file
     * @param string $lang - нужный язык // language // rus, eng ...
     *
     * @return array массив с текстом на выбранном языке // language text array
     */
    public function loadMessages(string $urlFile, string $lang): array {
        $mess = [];

        // если есть файл с языковыми параметрами
        if (file_exists($urlFile) === true) {
            include $urlFile;
            if (!empty($mess[$lang])) {
                $mess = $mess[$lang];
            }
        }

        return $mess;
    }

}
