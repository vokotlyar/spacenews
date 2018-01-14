<?php

namespace Models\Renderers;

/**
 * Класс для генерации панели перехода по страницам новостей сайта.
 */
class PagingRenderer {

    /** Предыдущая страница со ссылкой на ее номер */
    const INCLUDE_PREVIOUS_PAGE_LINK = __DIR__ . '/../../views/news/pagination/prevPageLink.php';
    /** Предыдущая страница со ссылкой на корень сайта */
    const INCLUDE_PREVIOUS_ROOT_LINK = __DIR__ . '/../../views/news/pagination/prevRootLink.php';
    /** Следующая страница со ссылкой на ее номер */
    const INCLUDE_NEXT_PAGE_LINK = __DIR__ . '/../../views/news/pagination/nextPageLink.php';
    /** Текущая страница без ссылки */
    const INCLUDE_CURRENT_PAGE = __DIR__ . '/../../views/news/pagination/currentPage.php';
    /** Страница со ссылкой на ее номер */
    const INCLUDE_PAGE_LINK = __DIR__ . '/../../views/news/pagination/pageLink.php';
    /** Корневая страница со ссылкой */
    const INCLUDE_ROOT_PAGE_LINK = __DIR__ . '/../../views/news/pagination/rootPageLink.php';
    /** Разрыв '***' */
    const INCLUDE_GAP = __DIR__ . '/../../views/news/pagination/gapPage.php';

    /** @var array - массив страниц для отображения */
    private $pages = array();
    /** @var int - текущая страница */
    private $currentPage;
    /** @var int - всего страниц */
    private $lastPage;
    /** @var string - префикс ссылок на страницы (могут быть разными в зависимости от выбранного источника новостей) */
    private $linksPrefix;

    /**
     * PagingRenderer constructor.
     * @param $currentPage - текущая страница.
     * @param $lastPage - последняя страница.
     * @param $linksPrefix - префикс ссылок на страницы перехода.
     */
    public function __construct($currentPage, $lastPage, $linksPrefix) {
        $this->lastPage = $lastPage;
        $this->linksPrefix = $linksPrefix;
        $this->currentPage = ($currentPage < 1) ? 1 : $currentPage;
        $this->currentPage = ($this->currentPage > $lastPage) ? $lastPage : $this->currentPage;

        $this->fillPages();
    }

    /**
     * Геттер
     */
    public function getPages() {
        return $this->pages;
    }


    /**
     * Сгенерировать html-код панели перехода по страницам новостей.
     * @return string - html-код панели.
     */
    public function render() {
        ob_start();

        $this->addPreviousLink();
        foreach ($this->pages as $page) {
            $this->addPageLink($page);
        }
        $this->addNextLink();

        return ob_get_clean();
    }


    /**
     * Заполнить массив страниц для отображения.
     * Отображаются: первая, последняя, текущая и по две страницы в обе стороны от текущей.
     * 0 - для промежутков (если они есть) между (текущей +/- 2) и первой/последней.
     *
     * Примеры:
     * Текущая : массив
     * 1 : 1-2-3-0-N
     * 2 : 1-2-3-4-0-N
     * 3 : 1-2-3-4-5-0-N
     * 4 : 1-2-3-4-5-6-0-N
     * 5 : 1-0-3-4-5-6-7-0-N
     * 6 : 1-0-4-5-6-7-8-0-N
     *
     */
    private function fillPages() {
        $this->pages[] = 1;

        $minusTwoPages = $this->getMinusTwoPage();
        $plusTwoPages = $this->getPlusTwoPage();

        for ($i = $minusTwoPages; $i <= $plusTwoPages; $i++) {
            $this->pages[] = $i;
        }

        $this->addLastPage($plusTwoPages);
    }

    /**
     * Получить номер страницы перед предыдущей от текущей (с учетом невыхода за рамки).
     * Строка $this->pages[] = 0; - это добавление разрыва в страницах 1 ... 4 5 6
     * @return int - номер страницы.
     */
    private function getMinusTwoPage() {
        $minusTwoPages = 2;
        if ($this->currentPage > 4) {
            $minusTwoPages = $this->currentPage - 2;
            $this->pages[] = 0;
        }

        return $minusTwoPages;
    }

    /**
     * Получить номер страницы после следующей от текущей (с учетом выхода за рамки).
     * @return int - номер страницы.
     */
    private function getPlusTwoPage() {
        $plusTwoPages = $this->lastPage;
        if ($this->currentPage < $this->lastPage - 2) {
            $plusTwoPages = $this->currentPage + 2;
        }
        return $plusTwoPages;
    }

    /**
     * Добавление номера последней страницы (с учетом разрыва и допустимых рамок).
     * @param $plusTwoPages - номер страницы после следующей от текущей.
     */
    private function addLastPage($plusTwoPages) {
        if ($plusTwoPages < $this->lastPage - 1)
            $this->pages[] = 0;

        if ($plusTwoPages < $this->lastPage)
            $this->pages[] = $this->lastPage;
    }

    /**
     * Добавление ссылки перехода на предыдущую страницу "<<"
     */
    private function addPreviousLink() {
        if ($this->currentPage > 1) {
            $page = $this->currentPage - 1;
            if ($page != 1) {
                include self::INCLUDE_PREVIOUS_PAGE_LINK;
            } else {
                include self::INCLUDE_PREVIOUS_ROOT_LINK;
            }
        }
    }

    /**
     * Добавление ссылки перехода на следующую страницу ">>"
     */
    private function addNextLink() {
        if ($this->currentPage < $this->lastPage) {
            $page = $this->currentPage + 1;
            include self::INCLUDE_NEXT_PAGE_LINK;
        }
    }

    /**
     * Добавление ссылки перехода на страницу с номером.
     * @param $page - номер страницы для перехода.
     */
    private function addPageLink($page) {
        if ($page == $this->currentPage) {
            include self::INCLUDE_CURRENT_PAGE;
        } elseif ($page > 1) {
            include self::INCLUDE_PAGE_LINK;
        } elseif ($page == 1) {
            include self::INCLUDE_ROOT_PAGE_LINK;
        } elseif ($page == 0) {
            include self::INCLUDE_GAP;
        }
    }

}