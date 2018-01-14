<?php

/**
 * Функция перехвата Warning-ов и замены их на Exception.
 * @param $errno - номер ошибки.
 * @param $errstr - строка ошибки.
 * @throws WarningException - бросается WarningException.
 */
function error_handler($errno, $errstr) {
    if($errno == E_WARNING) {
        throw new WarningException($errstr);
    }
}

/**
 * Класс WarningException
 */
class WarningException extends Exception {
    public function __toString() {
        return  "Warning: {$this->message} {$this->file} on line {$this->line}n";
    }
}