<?php

namespace Models\Renderers;


/**
 * Класс-генератор страницы авторизации.
 */
class LoginRenderer {

    /** View-страница */
    const INCLUDE_VIEW = __DIR__ . '/../../views/login/loginView.php';
    /** Идентификатор футера */
    const FOOTER_ID = "footer-bottom";


    /** @var string - сообщение выводимое пользователю */
    private $message = '';
    /** @var string - введенный пользователем логин (для запоминания поля Login, если авторизация не удалась) */
    private $login = '';
    /** @var string - ссылка, по которой шел незалогиненный пользователь (для возврата после авторизации) */
    private $referrer = '';

    /**
     * Сеттеры
     */
    public function setMessage($message) {
        $this->message = $message;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function setReferrer($referrer) {
        $this->referrer = $referrer;
    }


    /**
     * Сгенерировать необходимую html-страницу.
     * @return string - html-код страницы.
     */
    public function render() {
        $footerId = self::FOOTER_ID;
        ob_start();
        include self::INCLUDE_VIEW;

        return ob_get_clean();
    }

}