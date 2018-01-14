<?php
namespace Models\News;

/**
 * Класс источника новостей.
 */
class NewsSource {

    /** @var int - id сайта-источника в БД */
    private $id;
    /** @var string - Название сайта-источника новостей */
    private $name;
    /** @var string - Путь к иконке сайта  */
    private $iconPath = '';
    /** @var string - Адрес главной страницы сайта новостей  */
    private $siteURL;
    /** @var int - Время-timestamp создания источника  */
    private $creationTime;
    /** @var boolean - Активен ли сайт-источник новостей
     * (возможно, сайт уже не обновляется или мы перестали с ним работать, а новости с него в БД есть)
     */
    private $isActive = true;

    /**
     * NewsSource constructor.
     * @param string $name - название источника.
     * @param string $siteURL - url источника.
     *
     * creationTime по умолчанию инициализируется текущим временем.
     */
    public function __construct($name, $siteURL) {
        $this->name = $name;
        $this->siteURL = $siteURL;
        $this->creationTime = time();
    }


    /**
     * Геттеры/Сеттеры
     */
    public function getName() {
        return $this->name;
    }

    public function getIconPath() {
        return $this->iconPath;
    }

    public function getSiteURL() {
        return $this->siteURL;
    }

    public function getCreationTime() {
        return $this->creationTime;
    }

    public function isActive() {
        return $this->isActive;
    }

    public function getId() {
        return $this->id;
    }

    public function setIconPath($iconPath) {
        $this->iconPath = $iconPath;
    }

    public function setSiteURL($siteURL) {
        $this->siteURL = $siteURL;
    }

    public function setCreationTime($creationTime) {
        $this->creationTime = $creationTime;
    }
    public function setIsActive($isActive) {
        $this->isActive = $isActive;
    }

    public function setId($id) {
        $this->id = $id;
    }

}