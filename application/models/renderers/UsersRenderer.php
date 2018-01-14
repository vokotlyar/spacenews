<?php

namespace Models\Renderers;

use Models\DB\SpaceNewsDB;


/**
 * Класс-генератор страницы управления пользователями.
 */
class UsersRenderer {

    /** View-страница */
    const INCLUDE_VIEW = __DIR__ . '/../../views/users/usersView.php';
    /** Идентификатор футера */
    const FOOTER_ID = "footer-bottom-admin";
    /** Закрывающий тег таблицы */
    const CLOSE_TABLE_TAG = "\n        </table>\n";
    /** Файл с одной строкой таблицы пользователей */
    const INCLUDE_TABLE_ROW = __DIR__ . '/../../views/users/rowTable.php';
    /** Файл с шапкой таблицы пользователей */
    const INCLUDE_TABLE_HEADER = __DIR__ . '/../../views/users/topTable.php';
    /** Сообщение о неправильном логине или пароле */
    const WRONG_LOGIN_OR_PASSWORD = "<p class='message-error'>Неправильный логин или пароль!</p>";
    /** Сообщение об успешном добавлении пользователя */
    const USER_ADDED = "<p class='message-ok'>Добавлен пользователь: ";
    /** Закрывающий тег параграфа */
    const CLOSE_P = " .</p>";
    /** Сообщение о существующем пользователе (начало) */
    const USER_EXISTS_BEGIN = "<p class='message-error'>Пользователь ";
    /** Сообщение о существующем пользователе (окончание) */
    const USER_EXISTS_END = " уже существует!</p>";
    /** Сообщение об ошибке добавления пользователя (начало) */
    const ERROR_ADDING_USER_BEGIN = "<p class='message-error'>Ошибка добавления пользователя ";
    /** Сообщение об ошибке удаления пользователя (окончание) */
    const ERROR_ADDING_USER_END = " в базу данных!</p>";
    /** Сообщение об успешном удалении пользователя (начало) */
    const USER_DELETED_BEGIN = "<p class='message-ok'>Пользователь ";
    /** Сообщение об успешном удалении пользователя (окончание) */
    const USER_DELETED_END = " удален.</p>";
    /** Сообщение об ошибке удаления пользователя (начало) */
    const ERROR_DELETING_USER_BEGIN = "<p class='message-error'>Ошибка удаления пользователя  ";
    /** Сообщение об ошибке удаления пользователя (окончание) */
    const ERROR_DELETING_USER_END = " из базы данных.</p>";
    /** Сообщение о ненайденном пользователе (начало) */
    const USER_NOT_FOUND_BEGIN = "<p class='message-error'>Пользователь с логином ";
    /** Сообщение о ненайденном пользователе (окончание) */
    const USER_NOT_FOUND_END = " не найден!</p>";

    /** @var string - введенный пользователем логин */
    private $login;
    /** @var  - код результата авторизации */
    private $resultCode;
    /** @var SpaceNewsDB - экземпляр базы данных */
    private $db;
    /** @var string - сообщение при добавлении пользователя */
    private $messageAdd = '';
    /** @var string - сообщение при удалении пользователя */
    private $messageDelete = '';


    /**
     * UsersRenderer constructor.
     */
    public function __construct() {
        $this->db = new SpaceNewsDB();
    }


    /**
     * Сеттеры
     */
    public function setLogin($login) {
        $this->login = $login;
    }

    public function setResultCode($resultCode) {
        $this->resultCode = $resultCode;
    }


    /**
     * Сгенерировать необходимую html-страницу.
     * @return string - html-код страницы.
     */
    public function render() {
        $footerId = self::FOOTER_ID;
        $this->setMessages();
        ob_start();
        include self::INCLUDE_VIEW;

        return ob_get_clean();
    }


    /**
     * Установить результирующие сообщения после добавления/удаления пользователей.
     */
    private function setMessages() {
        $user = "<span> $this->login </span>";

        if ($this->resultCode) {
            switch ($this->resultCode) {
                case 1:
                    $this->messageAdd = self::USER_ADDED . "{$user}" . self::CLOSE_P;
                    break;
                case 2:
                    $this->messageAdd = self::USER_EXISTS_BEGIN . "{$user}" . self::USER_EXISTS_END;
                    break;
                case 3:
                    $this->messageAdd = self::WRONG_LOGIN_OR_PASSWORD;
                    break;
                case 4:
                    $this->messageAdd = self::ERROR_ADDING_USER_BEGIN . "{$user}" . self::ERROR_ADDING_USER_END;
                    break;
                case 5:
                    $this->messageDelete = self::USER_DELETED_BEGIN . "{$user}" . self::USER_DELETED_END;
                    break;
                case 6:
                    $this->messageDelete = self::ERROR_DELETING_USER_BEGIN . "{$user}" . self::ERROR_DELETING_USER_END;
                    break;
                case 7:
                    $this->messageDelete = self::USER_NOT_FOUND_BEGIN . "{$user}" . self::USER_NOT_FOUND_END;
                    break;
            }
        }
    }

    /**
     * Генерация таблицы
     */
    private function renderTable() {
        $users = $this->db->getAllUsers();

        if (count($users) > 0) {
            include self::INCLUDE_TABLE_HEADER;

            $this->renderOneRow($users);

            echo self::CLOSE_TABLE_TAG;
        }
    }

    /**
     * Генерация одной строки таблицы.
     * @param $users - массив пользователей.
     */
    private function renderOneRow($users) {
        foreach ($users as $user) {
            $login = $user->getLogin();
            $time = $user->getCreationTime();
            include self::INCLUDE_TABLE_ROW;
        }
    }

}