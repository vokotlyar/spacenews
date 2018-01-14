<?php
namespace Models\User;

/**
 * Класс пользователь.
 */
class User {

    /** @var string - Login пользователя  */
    private $login;
    /** @var string - Закодированный пароль пользователя  */
    private $hash;
    /** @var int - Время-timestamp создания пользователя  */
    private $creationTime;

    /**
     * User constructor.
     * @param string $login - логин пользователя.
     */
    public function __construct($login) {
        $this->login = $login;
        $this->creationTime = time();
    }

    /** Геттеры/сеттеры */
    public function getLogin() {
        return $this->login;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function getHash() {
        return $this->hash;
    }

    public function setHash($hash) {
        $this->hash = $hash;
    }

    public function getCreationTime() {
        return $this->creationTime;
    }

    public function setCreationTime($creationTime) {
        $this->creationTime = $creationTime;
    }


    /**
     * Установить hash из входящего пароля.
     * @param $password - пароль.
     */
    public function setHashFromPassword($password) {
        $this->hash = password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Проверить соответствие пароля хешу.
     * @param $password - пароль.
     * @return bool - корретность пароля.
     */
    public function isPassword($password) {
        return password_verify($password, $this->hash);
    }

}