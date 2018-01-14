<?php

namespace Models\News;

/**
 * Класс новостная категория
 */
class NewsCategory {

    /** @var string - Название категории */
    private $name;

    /**
     * NewsCategory constructor.
     * @param string $name
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * Геттер
     */
    public function getName() {
        return $this->name;
    }

}