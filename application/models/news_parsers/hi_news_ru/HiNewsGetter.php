<?php

namespace Models\NewsParsers\HiNewsRu;

use Models\News\NewsItem;
use Models\NewsParsers\NewsGetter;

/**
 * Парсер новостей сайта Hi-News.ru
 */
class HiNewsGetter extends NewsGetter {

    /** Имя новостного источника */
    const NAME = 'Hi-News.ru';
    /** Адрес главной страницы новостного источника */
    const URL = 'https://hi-news.ru';
    /** Адрес страницы с новостями */
    const NEWS_URL = "https://hi-news.ru/space/page/";
    /** Количество новостных статей на одной странице */
    const NEWS_LINKS_PER_PAGE = 30;
    /** Путь к логотипу новостного источника */
    const LOGO_PATH = "/application/models/news_parsers/hi_news_ru/logo.png";

    /** Параметрр содержащий дату публикации новостной статьи */
    const PUBLISHED_CONTAINER_PARAMETER = "meta[itemprop=datePublished]";
    /** Параметр с содержимым даты публикации новостной статьи */
    const CONTENT_PARAMETER = 'content';
    /** Параметрр содержащий текстовый блок новостной статьи */
    const TEXT_BLOCK_CONTAINER_PARAMETER = '.text';
    /** Параметрр содержащий ссылку на новостную статью */
    const LINKS_CONTAINER_PARAMETER = '.items-wrap';
    /** Параметрр содержащий заголовок новостной статьи */
    const TITLE_CONTAINER_PARAMETER = '.single-title';


    /**
     * HiNewsGetter constructor.
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
        $newsItems = $html->find(self::LINKS_CONTAINER_PARAMETER)[0];
        for ($i = 0; $i < count($newsItems->childNodes()); $i++) {
            $links[] = $newsItems->childNodes($i)->childNodes(0)->childNodes(0)->href;
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

        $published = $textBlock->find(self::PUBLISHED_CONTAINER_PARAMETER)[0]
            ->getAttribute(self::CONTENT_PARAMETER);
        $newsItem->setPublicationTime(strtotime($published));

        $imageBlock = $textBlock->getElementByTagName(parent::IMAGE_CONTAINER_PARAMETER);
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