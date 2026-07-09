<?php

namespace Gy\Core;


final class Localization {
    private ?array $dictionary = null; // тексты определённого языка

    public function __construct(
        private readonly string $url,
        private readonly string $fileName,
        private readonly string $lang
    ) {
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
     * Получает локализованный текст на основе константного обозначения.
     */
    public function getMessage(string $code): ?string {
        if ($this->dictionary === null) {
            $this->dictionary = $this->loadMessages("{$this->url}/lang_{$this->fileName}.php", $this->lang);
        }

        return $this->dictionary[$code] ?? null;
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
        if (!\is_file($urlFile)) {
            return $mess;
        }

        include $urlFile;
        if (!empty($mess[$lang])) {
            $mess = $mess[$lang];
        }

        return $mess;
    }

}
