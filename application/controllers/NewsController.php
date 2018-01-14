<?php

namespace Controllers;

use Models\Renderers\NewsRenderer;


/**
 * Класс NewsController для отображения новостных статей - корневая страница сайта.
 */
class NewsController implements Controller {

    /** Имя параметра с номером источника */
    const SOURCE_PARAMETER = 'source';
    /** Имя параметра с номером текущей страницы */
    const CURRENT_PAGE_PARAMETER = 'page';
    /** Имя куки с количеством статей на странице */
    const NEWS_QUANTITY_COOKIE = 'qty';
    /** Имя параметра с количеством новостей на странице */
    const NEWS_PER_PAGE_PARAMETER = 'newsPerPage';
    /** Путь к куке с количеством новостей на странице */
    const NEWS_QUANTITY_COOKIE_PATH = '/';
    /** Максимально возможное время истечения куки */
    const MAX_EXPIRE_TIME = 2147483647;
    /** Значение количества статей на странице по умолчанию (при первом посещении сайта пользователем) */
    const DEFAULT_NEWS_PER_PAGE = 25;

    /** @var FrontController - экземпляр главного контроллера */
    private $frontController;

    /**
     * NewsController constructor.
     * Присвоение полю $frontController экземпляра класса-синглтона FrontController
     */
    public function __construct() {
        $this->frontController = FrontController::getInstance();
    }


    /**
     * Обработчик отображения новостных статей.
     * Определяется текущая страница, количество новостей на странице и новостной источник.
     * Далее, эти параметры передаются модели NewsRenderer(), которая формирует html-документ для вывода пользователю.
     */
    public function showAction() {
        $newsPerPage = $this->getNewsPerPage();
        $currentPage = $this->getCurrentPage();

        $renderer = new NewsRenderer($currentPage, $newsPerPage, $this->getSourceId());
        $html = $renderer->render();

        $this->frontController->setBody($html);
    }

    /**
     * Обработчик изменения количества отображаемых новостных статей на странице
     * Если приходит запрос методом POST с параметром количества статей на странице, то
     * перезаписывается соответствующая кука и происходит перенаправление на первую страницу, но
     * с запоминанием источника новостей (если он был выбран).
     */
    public function newsquantityAction() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && (int)$_POST[self::NEWS_PER_PAGE_PARAMETER]) {
            $referrer = $_SERVER['HTTP_REFERER'];
            $location = explode(self::CURRENT_PAGE_PARAMETER, $referrer)[0];
            $newsPerPage = (int)$_POST[self::NEWS_PER_PAGE_PARAMETER];

            setcookie(self::NEWS_QUANTITY_COOKIE, $newsPerPage,
                self::MAX_EXPIRE_TIME, self::NEWS_QUANTITY_COOKIE_PATH);

            header("Location: " . $location);
            exit;
        }
    }


    /**
     * Получить id источника новостей для отображения
     * @return int - id источника или 0, если источник не задан (для всех доступных)
     */
    private function getSourceId() {
        return !empty((int)$this->frontController->getParams()[self::SOURCE_PARAMETER]) ?
            ((int)$this->frontController->getParams()[self::SOURCE_PARAMETER]) : 0;
    }

    /**
     * Получить номер текущей страницы для отображения
     * @return int - номер страницы или 1, если она не задана
     */
    private function getCurrentPage() {
        return !empty($this->frontController->getParams()[self::CURRENT_PAGE_PARAMETER]) ?
            $this->frontController->getParams()[self::CURRENT_PAGE_PARAMETER] : 1;
    }

    /**
     * Получить количество новостей на странице.
     * Если присутствует необходимая кука, то берется ее значение.
     * Иначе - берется значение по умолчанию и создается соответствующая кука.
     * @return int
     */
    private function getNewsPerPage() {
        $newsPerPage = self::DEFAULT_NEWS_PER_PAGE;

        if (isset($_COOKIE[self::NEWS_QUANTITY_COOKIE])) {
            $newsPerPage = (int)$_COOKIE[self::NEWS_QUANTITY_COOKIE] > 0 ?
                (int)$_COOKIE[self::NEWS_QUANTITY_COOKIE] : $newsPerPage;
        } else {
            setcookie(self::NEWS_QUANTITY_COOKIE, $newsPerPage, self::MAX_EXPIRE_TIME);
        }

        return $newsPerPage;
    }

}