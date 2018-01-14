<?php

namespace Models\Renderers;

use Models\DB\SpaceNewsDB;


/**
 * Класс-генератор страницы сканирования источников новостей.
 */
class NewsScanRenderer {

    /** View-страница */
    const INCLUDE_VIEW = __DIR__ . '/../../views/scan/scanNewsView.php';
    /** Идентификатор футера */
    const FOOTER_ID = "footer-bottom-admin";
    /** Начало http-ссылок */
    const HTTP_PREFIX = 'http://';
    /** Начало https-ссылок */
    const HTTPS_PREFIX = 'https://';
    /** Файл с одной строкой таблицы сканирования новостей */
    const INCLUDE_ONE_ROW_FORM = __DIR__ . '/../../views/scan/rowForm.php';
    /** Значение отмеченного чекбокса */
    const CHECKED_VALUE = 'checked';
    /** Значение неактивного чекбокса */
    const DISABLE_VALUE = 'disabled';
    /** Класс ячейки с количеством добавленных статей */
    const ADDED_NEWS_CELL_CLASS = "<td class='new-column'>";
    /** Закрывающий ячейку тег */
    const CLOSE_CELL_TAG = "</td>";
    /** Собственное имя источника данного сайта */
    const SELF_SOURCE_NAME = 'SpaceNews';
    /** Заголовок в шапке таблицы показывающий количество добавленных статей */
    const NEW_ARTICLES_TABLE_HEADER = '<th>Новых статей</th>';

    /** @var SpaceNewsDB - экземпляр базы данных */
    private $db;
    /** @var  - массив с предыдущими количествами новостных статей */
    private $prevNewsQuantities;
    /** @var  - массив новостных источников */
    private $sources;
    /** @var  - массив с количеством новостных статей для каждого источника */
    private $newsQuantities;
    /** @var  - массив с количеством добавленных статей */
    private $addedNewsQuantities;


    /**
     * NewsScanRenderer constructor.
     * Создается экземпляр БД и устанавливается показ скрытых (для обычных пользователей) статей.
     */
    public function __construct() {
        $this->db = new SpaceNewsDB();
        $this->db->setShowInvisible(true);
    }

    /**
     * Геттер/сеттер.
     */
    public function getPrevNewsQuantities() {
        return $this->prevNewsQuantities;
    }

    public function setPrevNewsQuantities($prevNewsQuantities) {
        $this->prevNewsQuantities = $prevNewsQuantities;
    }


    /**
     * Сгенерировать необходимую html-страницу.
     * @return string - html-код страницы.
     */
    public function render() {
        $footerId = self::FOOTER_ID;
        $headerNew = $this->getNewArticlesTableHeader();
        $this->setFields();

        ob_start();
        include self::INCLUDE_VIEW;

        return ob_get_clean();
    }


    /**
     * Получить заголовок поля таблицы отвечающий за количество найденных статей.
     * @return string - '' (если новые статьи не найдены) или текст заголовка.
     */
    private function getNewArticlesTableHeader() {
        $headerNew = '';
        if ($this->prevNewsQuantities) {
            $headerNew = self::NEW_ARTICLES_TABLE_HEADER;
        }

        return $headerNew;
    }

    /**
     * Установить значения полям класса:
     * - извлекаются из базы источники новостей.
     * - заполняется массив с количеством новостных статей для каждого источника.
     * - если пришли предыдущие количества статей, то заполняется массив добавленных.
     */
    private function setFields() {
        $this->sources = $this->db->getAllSources();
        $this->newsQuantities = $this->getNewsQuantities();
        if ($this->prevNewsQuantities) {
            $this->addedNewsQuantities = $this->getAddedNewsQuantities();
        }
    }

    /**
     * Получить текущее количество новостных статей для каждого из источников.
     * @return array - массив, где ключ - id источника, а значение - количество статей.
     */
    private function getNewsQuantities() {
        $newsQuantity = array();
        foreach ($this->sources as $source) {
            $id = $source->getId();
            $this->db->setSourceId($id);
            $newsQuantity[$id] = $this->db->getNewsQuantity();
        }

        return $newsQuantity;
    }

    /**
     * Получить количество добавленных (после сканирования) новостных статей для каждого из источников.
     * @return array - массив, где ключ - id источника, а значение - количество новых статей.
     */
    private function getAddedNewsQuantities() {
        $addNewsQuantity = array();
        foreach ($this->newsQuantities as $id => $qty) {
            $addNewsQuantity[$id] = $this->newsQuantities[$id] - $this->prevNewsQuantities[$id];
        }

        return $addNewsQuantity;
    }

    /**
     * Генерация строк таблицы.
     */
    private function renderTableRows() {
        foreach ($this->sources as $source) {
            if (self::SELF_SOURCE_NAME !== $source->getName()) {
                $this->renderOneRow($source);
            }
        }
    }

    /**
     * Генерация одной строки таблицы.
     * @param $source - новостной источник.
     */
    private function renderOneRow($source) {
        $name = $source->getName();
        $iconPath = $source->getIconPath();
        $url = $source->getSiteURL();
        $newsQty = $this->newsQuantities[$source->getId()];
        $checkboxStatus = $this->getCheckboxStatus($source);
        $addedNewsRowValue = $this->getAddedNewsRowValue($source);
        $showUrl = str_replace(self::HTTP_PREFIX, '',
            str_replace(self::HTTPS_PREFIX, '', $url));

        include self::INCLUDE_ONE_ROW_FORM;
    }

    /**
     * Получить состояние (отмечен, неотмечен, неактивен) чекбокса для выбора сканирования источника.
     * @param $source - источник новостей.
     * @return string - состояние.
     */
    private function getCheckboxStatus($source) {
        $checkboxStatus = self::CHECKED_VALUE;
        if (!$source->isActive()) {
            $checkboxStatus = self::DISABLE_VALUE;
        }

        return $checkboxStatus;
    }

    /**
     * Получить значение ячейки с количеством добавленных новостных статей.
     * @param $source - источник новостей.
     * @return string - значение ячейки.
     */
    private function getAddedNewsRowValue($source) {
        $addedNewsRowValue = '';
        if ($this->addedNewsQuantities) {
            $qty = $this->addedNewsQuantities[$source->getId()];
            $addedNewsRowValue = self::ADDED_NEWS_CELL_CLASS . $qty . self::CLOSE_CELL_TAG;
        }

        return $addedNewsRowValue;
    }

}