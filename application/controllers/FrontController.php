<?php

namespace Controllers;


/**
 * Класс-синглтон FrontController. Анализирует запрос и вызывает необходимые обработчики.
 */
class FrontController {

    /** Путь к интерфейсу-маркеру, который должны реализовывать контроллеры */
    const CONTROLLERS_INTERFACE = 'Controllers\Controller';
    /** Путь к классам-контроллерам */
    const CONTROLLERS_PATH = 'Controllers\\';
    /** Окончание названий классов-контроллеров */
    const CONTROLLERS_SUFFIX = 'Controller';
    /** Окончание названий методов-действий */
    const ACTION_METHODS_SUFFIX = 'Action';
    /** Название параметра с номером страницы */
    const PAGE_PARAMETER = 'page';
    /** Путь к контроллеру админ-панели */
    const ADMIN_CONTROLLER_PATH = "Controllers\AdminController";
    /** Имя метода авторизации */
    const LOGIN_ACTION = "loginAction";
    /** Начало пути для перенаправления неавторизованных пользователей */
    const LOGIN_REFERRER_PREFIX = '/admin/login/ref';
    /** Имя сессионной переменной отвечающей за авторизованность */
    const SESSION_LOGIN_VAR = 'admin';
    /** Ненужная часть запроса при формировании пути перенаправления неавторизованных пользователей */
    const REQUEST_PART_FOR_REPLACE = '/admin';


    /** Контроллер-обработчик */
    protected $controller;
    /** Действие */
    protected $action;
    /** Параметры запроса */
    protected $params = array();
    /** Тело html-документа для вывода пользователю */
    protected $body;

    /** Экземпляр класса FrontController */
    private static $instance;


    /**
     * FrontController constructor.
     * Устанавливает значения полей controller и action указанные в запросе.
     */
    private function __construct() {
        $this->setFieldsFromRequest();
    }

    /**
     * Получить экземпляр класса
     * @return FrontController
     */
    public static function getInstance() {
        if (!(self::$instance instanceof self))
            self::$instance = new self();

        return self::$instance;
    }


    /** Геттеры/сеттеры */
    public function getParams() {
        return $this->params;
    }

    public function getController() {
        return $this->controller;
    }

    public function getAction() {
        return $this->action;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
    }


    /**
     * Метод маршрутизации.
     * Осуществляется парсинг запроса с проверками существования (через рефлексию) указанных контроллера и метода.
     * Далее вызывается запрашиваемый или метод по умолчанию (если запрос некорректный).
     */
    public function route() {
        if (class_exists($this->getController())) {
            $class = new \ReflectionClass($this->getController());
            $this->invokeAction($class);
        } else {
            (new NewsController())->showAction();
        }
    }


    /**
     * Установка параметров запроса
     * @param $splits - Массив параметров запроса
     */
    private function setParams($splits) {
        $keys = array();
        $values = array();
        $qty = count($splits);
        for ($i = 2; $i < $qty; $i++) {
            if ($i % 2 == 0) {
                $keys[] = $splits[$i];
            } else {
                $values[] = $splits[$i];
            }
        }

        $this->params = array_combine($keys, $values);
    }

    /**
     * Парсинг строки запроса и установка полей controller, action и params
     * Без указания контроллера и действия может быть корневая страница - отображение новостных статей и
     * корневая страница с указанием номера необходимой страницы: http://.../page/3
     */
    private function setFieldsFromRequest() {
        $request = $_SERVER['REQUEST_URI'];
        $splits = explode('/', trim($request, '/'));

        if (self::PAGE_PARAMETER == $splits[0] && !empty($splits[1])) {
            $this->params[self::PAGE_PARAMETER] = (int)$splits[1];
        } elseif (!empty($splits[0]) && !empty($splits[1]) && count($splits) % 2 == 0) {
            $this->controller = self::CONTROLLERS_PATH . ucfirst($splits[0]) . self::CONTROLLERS_SUFFIX;
            $this->action = $splits[1] . self::ACTION_METHODS_SUFFIX;
            if (!empty($splits[2])) {
                $this->setParams($splits);
            }
        }
    }

    /**
     * Метод вызова указанного действия.
     * @param $class - Класс, который реализует действие.
     */
    private function invokeAction(\ReflectionClass $class) {

        if ($class->implementsInterface(self::CONTROLLERS_INTERFACE)
            && $class->hasMethod($this->getAction())
        ) {

            $controller = $class->newInstance();
            $this->handleAdminRequest();
            $method = $class->getMethod($this->getAction());
            $method->invoke($controller);

        } else {
            (new NewsController())->showAction();
        }
    }

    /**
     * Обработка запроса к администраторской панели сайта.
     * Если идет запрос к админке, то проверяется авторизованность с перенаправлением
     * на страницу входа (в случае необходимости).
     */
    private function handleAdminRequest() {
        if (self::ADMIN_CONTROLLER_PATH == $this->controller && self::LOGIN_ACTION != $this->action) {
            $this->checkAdminRights();
        }
    }

    /**
     * Проверка администраторских прав.
     * Если сессионная переменная $_SESSION['admin'] не истинна (администратор не залогинен),
     * то происходит перенаправление на форму авторизации.
     * Также, в строке запроса запоминается путь, по которому шел пользователь,
     * чтобы открыть нужную страницу после успешной авторизации.
     */
    private function checkAdminRights() {
        session_start();
        if (!isset($_SESSION[self::SESSION_LOGIN_VAR])) {

            header('Location: ' . self::LOGIN_REFERRER_PREFIX .
                str_replace(self::REQUEST_PART_FOR_REPLACE, '', $_SERVER['REQUEST_URI']));

            exit;
        }
    }

}	