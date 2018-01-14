<?php

namespace Models\NewsParsers\VKosmose;

use Models\News\NewsItem;
use Models\NewsParsers\NewsGetter;

/**
 * Парсер новостей сайта v-kosmose.com
 */
class VKosmoseGetter extends NewsGetter {

    /** Имя новостного источника */
    const NAME = 'V-Kosmose';
    /** Адрес главной страницы новостного источника */
    const URL = 'http://v-kosmose.com';
    /** Адрес страницы с новостями */
    const NEWS_URL = "http://v-kosmose.com/novosti-kosmosa/page/";
    /** Количество новостных статей на одной странице */
    const NEWS_LINKS_PER_PAGE = 25;
    /** Путь к логотипу новостного источника */
    const LOGO_PATH = "/application/models/news_parsers/v-kosmose_com/logo.png";

    /** Параметрр содержащий заголовок новостной статьи */
    const TITLE_CONTAINER_PARAMETER = '.entry-title';
    /** Параметрр содержащий текстовый блок новостной статьи */
    const TEXT_BLOCK_CONTAINER_PARAMETER = '.entry-content';
    /** Параметрр содержащий дату публикации новостной статьи */
    const PUBLISHED_CONTAINER_PARAMETER = '.entry-date';
    /** Параметрр содержащий ссылку на новостную статью */
    const LINKS_CONTAINER_PARAMETER = 'article';
    /** Параметрр со значением ссылки на новостную статью */
    const LINK_PARAMETER = 'a';


    /**
     * VKosmoseGetter constructor.
     */
    public function __construct() {
        parent::__construct(self::NAME, self::URL, self::LOGO_PATH,
            self::NEWS_URL, self::NEWS_LINKS_PER_PAGE);
    }


    /**
     * Получить ссылки на новостные статьи с одной страницы.
     * @param $html - код страницы.
     * @return array - массив текстовых ссылок.
     */
    protected function getOnePageLinks($html) {
        $links = array();
        $newsItems = $html->find(self::LINKS_CONTAINER_PARAMETER);
        for ($i = 0; $i < count($newsItems); $i++) {
            $links[] = $newsItems[$i]->find(self::LINK_PARAMETER)[0]->href;
        }

        return $links;
    }

    /**
     * Получить заголовок новостной статьи.
     * @param $html - код страницы.
     * @return string - заголовок.
     */
    protected function getTitle($html) {
        return $html->find(self::TITLE_CONTAINER_PARAMETER)[0]->text();
    }

    /**
     * Заполнить экземпляр новости текстом, картинкой и временем публикации.
     * @param $html - код страницы со статьей.
     * @param $newsItem - новостная статья.
     */
    protected function fillNewsItem($html, NewsItem $newsItem) {
        $textBlock = $html->find(self::TEXT_BLOCK_CONTAINER_PARAMETER)[0];
        $newsItem->setText($this->getNewsItemText($textBlock));

        $published = $html->find(self::PUBLISHED_CONTAINER_PARAMETER)[0]->text();
        $newsItem->setPublicationTime(strtotime($published));

        $imageBlock = $textBlock->find(parent::IMAGE_CONTAINER_PARAMETER)[0];
        $picturePath = $this->getPicturePath($imageBlock);
        $newsItem->setPicturePath($picturePath);
    }


    /**
     * Извлечь путь к картинке из статьи.
     * @param $imageBlock - блок (часть сайта) с картинкой.
     * @return string - путь.
     */
    private function getPicturePath($imageBlock) {
        $picturePath = '';
        if ($imageBlock) {
            $imageUrl = $imageBlock->getAttribute(parent::IMAGE_SRC_PARAMETER);
            $picturePath = parent::IMAGE_LINK_BEGIN . array_pop(explode(parent::IMAGE_LINK_BEGIN, $imageUrl));
        }

        return $picturePath;
    }

}