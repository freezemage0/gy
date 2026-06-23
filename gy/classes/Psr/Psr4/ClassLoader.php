<?php

namespace Psr\Psr4;

/**
 * Psr4AutoloaderClass
 *  - книверсальная реализация Psr4
 */
final class ClassLoader {
    // массив соотнощения пространств имён/префиксов и реальных путей на диске
    private array $prefixes = [];

    /**
     * register()
     *  - регистрирует авто загрузчик
     */
    public function register(bool $prepend = false): void {
        spl_autoload_register($this->loadClass(...), prepend: $prepend);
    }

    /**
     * addNamespace()
     *   -  добавляет префикс пространства имён и реальный ауть в $arPrefixes
     *
     * @param string $name - префикс пространства имён
     * @param string $directory - директория на сервере
     * @param bool $prependNamespace - флаг покажит что префикс надо добавить вначало
     */
    public function addNamespace(string $name, string $directory, bool $prependNamespace = false): void {
        $name = \trim($name, '\\') . '\\';
        $directory = \rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!isset($this->prefixes[$name])) {
            $this->prefixes[$name] = [];
        }

        if ($prependNamespace) {
            array_unshift($this->prefixes[$name], $directory);
        } else {
            $this->prefixes[$name][] = $directory;
        }
    }

    /**
     * loadClass()
     *   - загружает файл класса
     *
     * @param string $className - имя класса
     * @return string|null
     */
    public function loadClass(string $className): ?string {
        $prefixName = $className;

        while (false !== $posStr = strrpos($prefixName, '\\')) {
            $prefixName = substr($className, 0, $posStr + 1);
            $relativeClass = substr($className, $posStr + 1);
            $mappedFile = $this->loadMappedFile($prefixName, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }
            $prefixName = rtrim($prefixName, '\\');
        }

        return null;
    }

    /**
     * loadMappedFile
     *   - загружает файл в зависимости от префикса и имени класса
     *
     * @param string $prefixName - имё префикса
     * @param string $relativeClass - относительное имя класса
     * @return string|null
     */
    protected function loadMappedFile(string $prefixName, string $relativeClass): ?string {
        if (!isset($this->prefixes[$prefixName])) {
            return null;
        }

        foreach ($this->prefixes[$prefixName] as $prefixDir) {
            $urlFileClass = $prefixDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
            if ($this->requireFile($urlFileClass)) {
                return $urlFileClass;
            }
        }

        return null;
    }

    /**
     * requireFile()
     *   - загружеаем файл класса если он есть
     *
     * @param string $urlFileClass - путь к файлу класса
     * @return bool
     */
    protected function requireFile(string $urlFileClass): bool {
        $isFile = \is_file($urlFileClass);

        if ($isFile) {
            require_once $urlFileClass;
        }

        return $isFile;
    }
}
