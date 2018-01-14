<?php
namespace Models\News;

/**
 * Класс для хранения одной новостной статьи.
 */
class NewsItem {

    /** @var int - id новостной статьи */
    private $id;
    /** @var string - Заголовок новости */
    private $title;
    /** @var string - Текст новостной статьи */
    private $text = '';
    /** @var string - Путь к картинке новости */
    private $picturePath = '';
    /** @var NewsSource - Источник новости */
    private $source;
    /** @array NewsCategory[] - Категории, к которым относится новость */
    private $category = array();
    /** @var int - Время-timestamp публикации новости в источнике (данные берутся с новостного сайта) */
    private $publicationTime;
    /** @var int - Время-timestamp добавления новости на наш сайт */
    private $addingTime;
    /** @var string - Адрес новости в первоисточнике */
    private $url;
    /** @var boolean - Отображать ли новость на нашем сайте (администратор может менять) */
    private $isVisible = false;


    /**
     * NewsItem constructor.
     * @param string $title - заголовок новости.
     * @param NewsSource $source - источник новости.
     *
     * Параметры publicationTime и addingTime по умолчанию инициализируются текущим временем,
     * а url - корневой страницей сайта источника.
     */
    public function __construct($title, NewsSource $source) {
        $this->title = $title;
        $this->source = $source;
        $this->publicationTime = time();
        $this->addingTime = time();
        $this->url = $this->source->getSiteURL();
    }

    /**
     * Геттеры/сеттеры
     */
    public function getTitle() {
        return $this->title;
    }

    public function getText() {
        return $this->text;
    }

    public function getPicturePath() {
        return $this->picturePath;
    }

    public function getSource() {
        return $this->source;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getPublicationTime() {
        return $this->publicationTime;
    }

    public function getAddingTime() {
        return $this->addingTime;
    }

    public function getUrl() {
        return $this->url;
    }

    public function isVisible() {
        return $this->isVisible;
    }

    public function setText($text) {
        $this->text = $text;
    }

    public function setPicturePath($picturePath) {
        $this->picturePath = $picturePath;
    }

    public function setPublicationTime($publicationTime) {
        $this->publicationTime = $publicationTime;
    }

    public function setAddingTime($addingTime) {
        $this->addingTime = $addingTime;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function setIsVisible($isVisible) {
        $this->isVisible = $isVisible;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }


    /**
     * Добавление одной категории новости в массив категорий
     * @param $category - Категория
     */
    public function addCategory($category) {
        $this->category[] = $category;
    }

}