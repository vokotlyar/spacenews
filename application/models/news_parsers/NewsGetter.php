<?php
namespace Models\NewsParsers;

use Models\News\NewsItem;
use Sunra\PhpSimple\HtmlDomParser;
use Models\News\NewsSource;

/**
 * Абстрактный класс парсера новостных статей.
 */
abstract class NewsGetter {

    /** Параметрр содержащий картинку новостной статьи */
    const IMAGE_CONTAINER_PARAMETER = 'img';
    /** Начало ссылки на картинку новостной статьи */
    const IMAGE_LINK_BEGIN = "http";
    /** Параметр с источником картинки новостной статьи */
    const IMAGE_SRC_PARAMETER = 'src';
    /** Параметрр содержащий параграф текста новостной статьи */
    const PARAGRAPH_CONTAINER_PARAMETER = 'p';

    /** @var - адрес страницы с новостями */
    protected $newsUrl;
    /** @var - количество новостных статей на одной странице */
    protected $newsLinkPerPage;

    /** @var NewsSource - Новостной источник */
    protected $source;

    /**
     * NewsGetter constructor.
     * @param $name - имя новостного источника.
     * @param $url - адрес главной страницы новостного источника.
     * @param $logoPath - путь к логотипу новостного источника.
     * @param $newsUrl - адрес страницы с новостями.
     * @param $newsLinkPerPage - количество статей на странице.
     */
    public function __construct($name, $url, $logoPath, $newsUrl, $newsLinkPerPage) {
        $this->source = new NewsSource($name, $url);
        $this->source->setIconPath($logoPath);

        $this->newsUrl = $newsUrl;
        $this->newsLinkPerPage = $newsLinkPerPage;
    }


    /**
     * Получить указанное количество последних новостных статей.
     * @param int $quantity - количество.
     * @return NewsItem[] array - массив последних новостей.
     */
    public function getLastNews($quantity) {
        $news = array();
        $links = $this->getNewsLinks($quantity);

        for ($i = 0; $i < count($links); $i++) {
            $html = HtmlDomParser::file_get_html($links[$i]);
            $newsItem = $this->getOneNewsItem($html);

            $newsItem->setUrl($links[$i]);
            $news[] = $newsItem;
        }

        return $news;
    }


    /**
     * Получить одну новостную статью.
     * @param $html - код страницы со статьей.
     * @return NewsItem - новостная статья.
     */
    private function getOneNewsItem($html) {
        $title = $this->getTitle($html);
        $newsItem = new NewsItem($title, $this->source);
        $this->fillNewsItem($html, $newsItem);

        return $newsItem;
    }

    /**
     * Заполнить экземпляр новости текстом, картинкой и временем публикации.
     * @param $html - код страницы со статьей.
     * @param $newsItem - новостная статья.
     */
    protected abstract function fillNewsItem($html, NewsItem $newsItem);

    /**
     * Получить заголовок новостной статьи.
     * @param $html - код страницы.
     * @return string - заголовок.
     */
    protected abstract function getTitle($html);

    /**
     * Получить ссылки на новостные статьи с одной страницы.
     * @param $html - код страницы.
     * @return string[] array - массив текстовых ссылок.
     */
    protected abstract function getOnePageLinks($html);

    /**
     * Получить текстовое содержимое статьи.
     * @param $textBlock - часть страницы содержащая текст, картинку, дату публикации, и т.д.
     * @return string - текст статьи.
     */
    protected function getNewsItemText($textBlock) {
        $text = '';
        foreach ($textBlock->find(self::PARAGRAPH_CONTAINER_PARAMETER) as $paragraph) {
            if (strlen(trim($paragraph->text())) > 0) {
                $text .= '<p>' . trim($paragraph->text()) . '</p>';
            }
        }

        return $text;
    }

    /**
     * Получить заданное количество ссылок на новостные статьи.
     * @param $quantity int - количество.
     * @return string[] array - массив текстовых ссылок.
     */
    private function getNewsLinks($quantity) {
        $links = array();

        $page = 1;
        while (count($links) < $quantity) {
            $html = HtmlDomParser::file_get_html($this->newsUrl . $page++);
            $currentLinks = $this->getOnePageLinks($html);
            $links = array_merge($links, $currentLinks);
            if (count($currentLinks) < $this->newsLinkPerPage)
                break;
        }

        return array_slice($links, 0, $quantity);
    }

}