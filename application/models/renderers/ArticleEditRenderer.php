<?php

namespace Models\Renderers;


use Models\DB\SpaceNewsDB;

/**
 * Класс-генератор страницы редактирования новостной статьи.
 */
class ArticleEditRenderer {

    /** View-страница */
    const INCLUDE_VIEW = __DIR__ . '/../../views/edit/editNewsItemView.php';
    /** Часть для замены при формировании ссылки на удаление статьи */
    const DELETE_LINK_TO_REPLACE = 'show';
    /** Текст замены при формировании ссылки на удаление статьи */
    const DELETE_LINK_REPLACE_WITH = 'delete';
    /** Текст для поиска в ссылке */
    const SEARCH_IN_REFERRER = '/show';
    /** Параметр идентификатора в ссылке */
    const ID_LINK_PARAMETER = "/id/";
    /** Часть ссылки позиционирующая на id */
    const ID_LINK_PART = "#id-";
    /** Значение класса для невидимой статьи */
    const CLASS_INVISIBLE = 'class = "invisible"';
    /** Значение класса для видимой статьи */
    const CLASS_VISIBLE = 'class = "visible"';
    /** начало ссылки для удаления статьи */
    const DELETE_LINK_PREFIX = "/admin/delete/id/";
    /** Корневая страница админ-панели */
    const ROOT_ADMIN_PAGE = '/admin/show';
    /** Идентификатор футера */
    const FOOTER_ID = "footer-bottom-admin";


    /** @var SpaceNewsDB - экземпляр базы данных */
    private $db;
    /** @var  - идентификатор статьи */
    private $newsId;

    /**
     * ArticleEditRenderer constructor.
     * @param $newsId - идентификатор статьи
     */
    public function __construct($newsId) {
        $this->newsId = $newsId;
        $this->db = new SpaceNewsDB();
    }


    /**
     * Сгенерировать необходимую html-страницу.
     * @return string - html-код страницы.
     */
    public function render() {
        $newsItem = $this->db->getNewsItem($this->newsId);

        if (!$newsItem) {
            $this->toRootPage();
        }

        return $this->getHtml($newsItem);
    }


    /**
     * Перенаправление на корневую страницу админ-панели.
     */
    private function toRootPage() {
        header("Location: " . self::ROOT_ADMIN_PAGE);
        exit;
    }

    /**
     * Получить html-код страницы редактирования.
     * @param $newsItem - экземпляр статьи.
     * @return string - html-код.
     */
    private function getHtml($newsItem) {
        $adminClass = ($newsItem->isVisible() == 0) ? self::CLASS_INVISIBLE : self::CLASS_VISIBLE;
        $newsText = $this->getNewsText($newsItem);
        $deleteLink = $this->getDeleteLink();
        $footerId = self::FOOTER_ID;
        $linkReturn = $_SERVER['HTTP_REFERER'] . self::ID_LINK_PART . $this->newsId;

        ob_start();
        include self::INCLUDE_VIEW;

        return ob_get_clean();
    }

    /**
     * Получить текст новостной статьи.
     * @param $newsItem - экземпляр статьи.
     * @return string - текст статьи.
     */
    private function getNewsText($newsItem) {
        $splitText = explode('</p>', $newsItem->getText());
        $newsText = '';
        foreach ($splitText as $paragraph) {
            if (trim($paragraph))
                $newsText .= trim($paragraph) . "</p>\n\n";
        }

        return $newsText;
    }

    /**
     * Получить ссылку для удаления статьи.
     * @return string - ссылка.
     * Пример по умолчанию: /admin/delete/id/555
     * Пример с учетом HTTP_REFERER: /admin/delete/source/4/page/3/id/1414 - после удаления
     * будет возврат на /source/4/page/3
     */
    private function getDeleteLink() {
        $deleteLink = self::DELETE_LINK_PREFIX . $this->newsId;

        if (strpos($_SERVER['HTTP_REFERER'], self::SEARCH_IN_REFERRER) !== false) {
            $deleteLink = str_replace(self::DELETE_LINK_TO_REPLACE, self::DELETE_LINK_REPLACE_WITH,
                    $_SERVER['HTTP_REFERER']) . self::ID_LINK_PARAMETER . $this->newsId;
        }

        return $deleteLink;
    }

}