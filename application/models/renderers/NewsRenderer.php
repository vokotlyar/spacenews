<?php

namespace Models\Renderers;

use Models\DB\SpaceNewsDB;

/**
 * Класс для отрисовки html-страницы с новотными статьями
 */
class NewsRenderer {

    /** View-страница новостных статей */
    const INCLUDE_VIEW = __DIR__ . '/../../views/news/newsView.php';
    /** View-страница новостных статей административной панели */
    const INCLUDE_VIEW_ADMIN = __DIR__ . '/../../views/news/newsAdminView.php';
    /** Идентификатор футера */
    const FOOTER_ID = "footer-bottom";
    /** Идентификатор футера административной панели */
    const FOOTER_ID_ADMIN = "footer-bottom-admin";
    /** Начало ссылок сайдбара */
    const SIDEBAR_LINKS_BEGIN = "/news";
    /** Начало ссылок сайдбара админ-панели */
    const ADMIN_SIDEBAR_LINKS_BEGIN = "/admin";
    /** html-класс новостной статьи */
    const ARTICLE_CLASS = "post";
    /** html-класс новостной статьи в админ-панели */
    const ADMIN_ARTICLE_CLASS = "post-admin";
    /** Обработчик изменения количества отображаемых статей на странице */
    const NEWS_QUANTITY_ACTION = "/news/newsquantity";
    /** Обработчик изменения количества отображаемых статей на странице административной панели */
    const ADMIN_NEWS_QUANTITY_ACTION = "/admin/newsquantity";
    /** Начало ссылок в блоке выбора страницы, если выбран источник новостей */
    const SOURCE_LINKS_BEGIN = "/news/show/source/";
    /** Начало ссылок в блоке выбора страницы админ-панели, если выбран источник новостей */
    const SOURCE_ADMIN_LINKS_BEGIN = "/admin/show/source/";
    /** Начало ссылок в блоке выбора страницы админ-панели */
    const ADMIN_LINKS_BEGIN = "/admin/show";
    /** Файл с html одной статьи */
    const INCLUDE_ONE_ARTICLE = __DIR__ . '/../../views/news/article.php';
    /** Файл с формой выбора видимости статьи */
    const INCLUDE_VISIBILITY_FORM = __DIR__ . '/../../views/news/selectVisibility.php';
    /** Класс неотображаемой статьи */
    const INVISIBLE_CLASS = 'class = "invisible"';
    /** Класс отображаемой статьи */
    const VISIBLE_CLASS = 'class = "visible"';
    /** Начало ссылки редактирования статьи */
    const EDIT_LINKS_BEGIN = "/admin/edit/id/";
    /** Значение атрибута target для открытия ссылки в текущем окне */
    const SELF_TARGET = "_self";
    /** Значение атрибута target для открытия ссылки в новом окне */
    const BLANK_TARGET = "_blank";
    /** Тег закрывающий абзац */
    const CLOSE_P = '</p>';
    /** Начало новостной ссылки */
    const NEWS_LINK_BEGIN = " <a name='id-";
    /** Окончание новостной ссылки */
    const NEWS_LINK_END = "'></a>";
    /** Открывающий div */
    const OPEN_DIV = "\n<div>\n";
    /** Закрывающий div */
    const CLOSE_DIV = "</div>\n";
    /** Закрывающий div с отступом */
    const CLOSE_TAB_DIV = "\n\t</div>\n";

    /** @var int - текущая страница */
    private $currentPage;
    /** @var int - статей на одной странице */
    private $newsPerPage;
    /** @var int - последняя страница */
    private $lastPage;
    /** @var - Номер первой отображаемой новостной статьи (из списка всех статей в БД) */
    private $startNewsNumber;
    /** @var int - id новостного источника или 0 для отображения новостей из всех источников */
    private $sourceId = 0;
    /** @var SpaceNewsDB - экземпляр базы данных */
    private $db;
    /** @var - блок новостных статей для отображения */
    private $newsBlock;
    /** @var - список доступных новостных источников */
    private $sources;
    /** @var  - html-класс новостной статьи (обычная или для административной панели) */
    private $articleClass;


    /**
     * NewsRenderer constructor.
     * @param $currentPage - текущая страница.
     * @param $newsPerPage - новостей на странице.
     * @param $sourceId - идентификатор источника.
     * @param bool $showInvisible - отображать ли невидимые статьи.
     */
    public function __construct($currentPage, $newsPerPage, $sourceId, $showInvisible = false) {
        $this->newsPerPage = $newsPerPage;
        $this->sourceId = $sourceId;
        $this->initDB($sourceId, $showInvisible);
        $this->setLastPage();
        $this->setValidCurrentPage($currentPage);
        $this->startNewsNumber = ($this->currentPage - 1) * $this->newsPerPage + 1;
        $this->newsBlock = $this->db->getNewsBlock($this->startNewsNumber, $this->newsPerPage);
        $this->sources = $this->db->getAllSources();
    }


    /**
     * Сгенерировать html-страницу с новостными статьями.
     * @return string - html-код страницы.
     */
    public function render() {
        $linksBegin = ($this->sourceId != 0) ? self::SOURCE_LINKS_BEGIN . $this->sourceId : "";
        $sidebarLinksBegin = self::SIDEBAR_LINKS_BEGIN;
        $paginationHtmlPart = (new PagingRenderer($this->currentPage, $this->lastPage, $linksBegin))->render();
        $footerId = self::FOOTER_ID;
        $this->articleClass = self::ARTICLE_CLASS;
        $newsQuantityAction = self::NEWS_QUANTITY_ACTION;

        ob_start();
        include self::INCLUDE_VIEW;

        return ob_get_clean();
    }

    /**
     * Сгенерировать html-страницу административной панели с новостными статьями.
     * @return string - html-код страницы.
     */
    public function adminViewRender() {
        $linksBegin = ($this->sourceId != 0) ? self::SOURCE_ADMIN_LINKS_BEGIN . $this->sourceId : self::ADMIN_LINKS_BEGIN;
        $sidebarLinksBegin = self::ADMIN_SIDEBAR_LINKS_BEGIN;
        $paginationHtmlPart = (new PagingRenderer($this->currentPage, $this->lastPage, $linksBegin))->render();
        $footerId = self::FOOTER_ID_ADMIN;
        $newsQuantityAction = self::ADMIN_NEWS_QUANTITY_ACTION;
        $this->articleClass = self::ADMIN_ARTICLE_CLASS;

        ob_start();
        include self::INCLUDE_VIEW_ADMIN;

        return ob_get_clean();
    }


    /**
     * Установить номер последней страницы с новостными статьями.
     * Номер зависит от общего количества статей и количества статей на странице.
     */
    private function setLastPage() {
        $totalNews = $this->db->getNewsQuantity();
        $lastPage = (int)($totalNews / $this->newsPerPage);
        if ($totalNews % $this->newsPerPage != 0)
            $lastPage += 1;

        $this->lastPage = $lastPage;
    }

    /**
     * Установить корректную текущую страницу (чтобы не выходила за рамки [1, lastPage]).
     * @param $currentPage - страница.
     */
    private function setValidCurrentPage($currentPage) {
        $page = ($currentPage < 1) ? 1 : $currentPage;
        $this->currentPage = ($page > $this->lastPage) ? $this->lastPage : $page;
    }

    /**
     * Создание соединения к БД и инициализация полей.
     * @param $sourceId - идентификатор источника (0 - все источники).
     * @param $showInvisible - отображать ли невидимые статьи.
     */
    private function initDB($sourceId, $showInvisible) {
        $this->db = new SpaceNewsDB();
        $this->db->setSourceId($sourceId);
        if ($showInvisible)
            $this->db->setShowInvisible(true);
    }

    /**
     * Генерация новостных статей.
     */
    private function renderArticles() {
        $i = 0;
        foreach ($this->newsBlock as $article) {
            $i++;
            $splitText = explode(self::CLOSE_P, $article->getText(), 2);
            $firstParagraph = $splitText[0] . self::CLOSE_P;
            $restText = $splitText[1];
            $target = self::BLANK_TARGET;
            $newsHref = $article->getUrl();

            include self::INCLUDE_ONE_ARTICLE;

            echo self::CLOSE_DIV;
        }
    }

    /**
     * Генерация статей административной панели.
     */
    private function renderAdminArticles() {
        $i = 0;
        foreach ($this->newsBlock as $article) {
            $this->renderOneAdminArticle($article, ++$i);
        }
    }

    /**
     * Генерация одной статьи административной панели.
     * @param $article - объект новостной статьи.
     * @param $i - порядковый номер статьи на странице.
     */
    private function renderOneAdminArticle($article, $i) {
        $this->renderAdminArticleContent($article, $i);
        $this->renderAdminArticleVisibility($article);

        echo self::CLOSE_DIV;
    }

    /**
     * Генерация содержимого (заголовок, картинка, текст, ...) статьи административной панели.
     * @param $article - объект статьи.
     * @param $i - порядковый номер статьи на странице.
     */
    private function renderAdminArticleContent($article, $i) {
        $splitText = explode(self::CLOSE_P, $article->getText(), 2);
        $firstParagraph = $splitText[0] . self::CLOSE_P;
        $restText = $splitText[1];
        $adminClass = ($article->isVisible() == 0) ? self::INVISIBLE_CLASS : self::VISIBLE_CLASS;
        $newsLink = self::NEWS_LINK_BEGIN . $article->getId() . self::NEWS_LINK_END;
        $target = self::SELF_TARGET;
        $newsHref = self::EDIT_LINKS_BEGIN . $article->getId();

        include self::INCLUDE_ONE_ARTICLE;
    }

    /**
     * Генерация формы выбора видимости статьи административной панели.
     * @param $article - объект статьи.
     */
    private function renderAdminArticleVisibility($article) {
        echo self::OPEN_DIV;

        include self::INCLUDE_VISIBILITY_FORM;

        echo self::CLOSE_TAB_DIV;
    }

}