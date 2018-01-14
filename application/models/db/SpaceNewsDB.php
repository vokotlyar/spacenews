<?php

namespace Models\DB;

use Models\News\NewsItem;
use Models\News\NewsSource;
use Models\Images\ImageHandler;
use Models\User\User;


/**
 * Класс для работы с базой данных SpaceNews.
 */
class SpaceNewsDB {

    /** Имя БД */
    const DB_NAME = 'spacenews';
    /** Имя хоста */
    const HOST = 'localhost';
    /** Имя пользователя БД */
    const USER = 'root';
    /** Пароль пользователя */
    const PASSWORD = '';
    /** Строка для инициализации PDO-объекта */
    const DB_STRING = 'mysql:host=' . self::HOST . ';dbname=' . self::DB_NAME;

    /** Сообщение об ошибке обновления новостной статьи */
    const NEWS_ITEM_UPDATING_FAILED_MESSAGE = "News item updating failed!";
    /** Сообщение об ошибке удаления новостной статьи */
    const NEWS_ITEM_DELETE_FAILED_MESSAGE = "News item delete failed!";
    /** Сообщение об ошибке обновления картинки новостной статьи */
    const NEWS_ITEM_PICTURE_UPDATING_FAILED_MESSAGE = "News item picture updating failed!";
    /** Сообщение об ошибке обновления активности новостного источника */
    const SOURCE_ACTIVITY_UPDATING_FAILED_MESSAGE = "Source activity updating failed!";
    /** Сообщение об ошибке сохранения пользователя */
    const USER_SAVING_FAILED_MESSAGE = "User saving failed!";
    /** Сообщение о существовании логина пользователя */
    const LOGIN_IS_IN_USE_MESSAGE = "Login is already in use!";
    /** Параметр login пользователя */
    const LOGIN_PARAMETER = 'login';
    /** Параметр hash пользователя */
    const HASH_PARAMETER = 'hash';
    /** Сообщение об отсутствии пользователя с указанным логином */
    const ABSENT_LOGIN_MESSAGE = "Login is absent!";
    /** Сообщение ошибки удаления пользователя */
    const USER_DELETING_FAILED_MESSAGE = "User deleting failed!";
    /** Параметр названия источника новостей */
    const NAME_PARAMETER = 'name';
    /** Параметр url источника новостей */
    const SITE_URL_PARAMETER = 'siteURL';
    /** Параметр пути к иконке источника новостей */
    const ICON_PATH_PARAMETER = 'iconPath';
    /** Параметр времени создания источника новостей */
    const CREATION_TIME_PARAMETER = 'creationTime';
    /** Параметр активности источника новостей */
    const ACTIVE_PARAMETER = 'active';
    /** Сообщение ошибки сохранения источника новостей */
    const NEWS_SOURCE_SAVING_FAILED = "News source saving failed!";
    /** Сообщение ошибки сохранения новостной статьи */
    const NEWS_ITEM_SAVING_FAILED_MESSAGE = "News item saving failed!";
    /** Параметр идентификатора источника статей */
    const SOURCE_ID_PARAMETER = 'source_id';
    /** Параметр заголовка новостной статьи */
    const TITLE_PARAMETER = 'title';
    /** Параметр текста новостной статьи */
    const TEXT_PARAMETER = 'text';
    /** Параметр пути к картинке для новостной статьи */
    const PICTURE_PATH_PARAMETER = 'picturePath';
    /** Параметр времени публикации новостной статьи */
    const PUBLICATION_TIME_PARAMETER = 'pubTime';
    /** Параметр url новостной статьи */
    const URL_PARAMETER = 'URL';
    /** Параметр времени добавления новостной статьи */
    const ADD_TIME_PARAMETER = 'addTime';
    /** Параметр видимости новостной статьи */
    const VISIBILITY_PARAMETER = 'visible';
    /** Длина имени сохраняемой новостной картинки */
    const IMAGE_FILE_NAME_LENGTH = 8;
    /** Каталог для хранения новостных картинок */
    const IMAGES_PATH = 'news_pictures/';
    /** Каталог для хранения новостных картинок */
    const DEFAULT_IMAGE = 'default.jpg';
    /** Ширина новостных картинок в пикселях*/
    const IMAGES_WIDTH = 300;
    /** Параметр идентификатора */
    const ID_PARAMETER = 'id';

    /** @var \PDO - поле коннекта к БД. */
    protected $db;

    /** @var int Id источника новостей. 0 - по умолчанию берутся все источники */
    private $sourceId = 0;
    /** @var bool Отображать ли невидимые (присутствуют в БД, но не одобрены админом: visible = false) новости */
    private $showInvisible = false;


    /**
     * SpaceNewsDB constructor.
     * Устанавливается соединение и режим ошибок Exception
     */
    public function __construct() {
        try {
            $this->db = new \PDO(self::DB_STRING, self::USER, self::PASSWORD);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * SpaceNewsDB destructor.
     * Освобождается память выделенная под переменную соединения с БД.
     */
    function __destruct() {
        $this->db = null;
    }

    /**
     * Сеттеры
     */
    public function setShowInvisible($showInvisible) {
        $this->showInvisible = (boolean)$showInvisible;
    }

    /**
     * Если $sourceId присутствует в БД, то он присваивается, иначе - 0.
     * @param $sourceId - идентификатор источника новостей.
     */
    public function setSourceId($sourceId) {
        $this->sourceId = ($this->getSourceById((int)$sourceId))->getSiteURL() ? (int)$sourceId : 0;
    }


    /**
     * Сохранить экземпляр новостной статьи NewsItem в БД.
     * Также, сохраняется главная (одна) картинка новости из сайта-источника в папку нашего сайта IMAGES_PATH.
     * @param NewsItem $newsItem - Новость
     * @throws \Exception - Бросается исключение, если возникли ошибки при сохранении.
     */
    public function saveNewsItem(NewsItem $newsItem) {
        try {
            $sql = $this->getSqlSaveNewsItem($newsItem);
            $this->db->exec($sql);
        } catch (\Exception $e) {
            throw new \Exception(self::NEWS_ITEM_SAVING_FAILED_MESSAGE);
        }
    }

    /**
     * Сохранить отсутствующие в базе новостные статьи.
     * Наличие статьи проверяется по присутствию ее url в БД.
     * @param array $newsItems - массив статей.
     */
    public function saveAbsentNews(array $newsItems) {
        foreach ($newsItems as $item) {
            if (!$this->isNewsItemUrlPresent($item)) {
                $this->saveNewsItem($item);
            }
        }
    }

    /**
     * Получить массив всех новостных статей.
     * Создается отдельный массив источников $sources, чтобы не извлекать уже присутствующий.
     * @return array - массив статей.
     */
    public function getAllNews() {
        $sql = "SELECT id, title, text, picturePath, source_id, pubTime, URL, addTime, visible FROM spacenews.news " .
            "ORDER BY pubTime DESC, addtime";
        $result = $this->db->query($sql);
        $news = $this->fetchNews($result);

        return $news;
    }

    /**
     * Получить количество всех новостных статей (с учетом sourceId и showInvisible).
     * @return int - количество статей
     */
    public function getNewsQuantity() {
        $conditionsSql = $this->getSqlConditionsPart();
        $sql = "SELECT count(*) FROM spacenews.news" . $conditionsSql;
        $result = $this->db->query($sql);

        return (int)$result->fetchColumn();
    }

    /**
     * Получить блок новостей начиная с номера $from (включительно) и с указанным количеством.
     * Нумерация новостных статей начинается с 1.
     * @param $from - Порядковый номер новости
     * @param $quantity - Необходимое количество новостей включая указанную начальную.
     * @return array - Массив новостей.
     */
    public function getNewsBlock($from, $quantity) {
        $strIds = $this->getIdsString($from, $quantity);
        $sqlGetNews = "SELECT id, title, text, picturePath, source_id, pubTime, URL, addTime, visible FROM spacenews.news " .
            "WHERE id IN ($strIds)" .
            " ORDER BY pubTime DESC, addtime DESC, id ";
        $result = $this->db->query($sqlGetNews);
        $news = $this->fetchNews($result);

        return $news;
    }

    /**
     * Получить новостную статью по id.
     * @param $id - идентификатор.
     * @return bool|NewsItem - объект новостной статьи или false.
     */
    public function getNewsItem($id) {
        $id = (int)$id;
        $sql = "SELECT id, title, text, picturePath, source_id, pubTime, URL, addTime, visible FROM spacenews.news " .
            "WHERE id = $id";
        $result = $this->db->query($sql);

        if ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $source = $this->getSourceById($row[self::SOURCE_ID_PARAMETER]);

            return $this->fetchOneNewsItem($row, $source);
        } else {
            return false;
        }
    }

    /**
     * Сохранить экземпляр класса NewsSource в соответствующую таблицу source,
     * если в ней отсутствует запись с URL новостного источника.
     * Если запись (с URL) существует в таблице БД, то другие поля не перезаписываются.
     * Возвращает ID существующей/добавленной записи.
     * @param NewsSource $newsSource - Новостной источник
     * @return int - ID записи
     * @throws \Exception - Бросается исключение, если возникли ошибки при добавлении.
     */
    public function saveNewsSource(NewsSource $newsSource) {
        try {
            $id = $this->getSourceId($newsSource->getSiteURL());

            if ($id == 0) {
                $id = (int)$this->insertNewsSource($newsSource);
            }

            return $id;

        } catch (\Exception $e) {
            throw new \Exception(self::NEWS_SOURCE_SAVING_FAILED);
        }
    }

    /**
     * Получить массив всех новостных источников.
     * @return array - источники.
     */
    public function getAllSources() {
        $sources = array();
        $sql = "SELECT id, name, iconPath, siteURL, creationTime, active FROM spacenews.source";
        $result = $this->db->query($sql);

        while ($sourceRow = $result->fetch(\PDO::FETCH_ASSOC)) {
            $source = $this->fetchOneSource($sourceRow);
            $sources[] = $source;
        }

        return $sources;
    }

    /**
     * Получить массив всех пользователей.
     * @return array - пользователи.
     */
    public function getAllUsers() {
        $users = array();
        $sql = "SELECT login, creationTime FROM spacenews.users ORDER BY creationTime DESC";
        $result = $this->db->query($sql);

        while ($userRow = $result->fetch(\PDO::FETCH_ASSOC)) {
            $users[] = $this->fetchOneUser($userRow);
        }

        return $users;
    }

    /**
     * Получить пользователя по логину.
     * @param $login - логин.
     * @return bool|User - пользователь или false.
     */
    public function getUser($login) {
        $login = $this->db->quote(strtolower($login));
        $sql = "SELECT login, hash, creationTime FROM spacenews.users WHERE login = $login";
        $result = $this->db->query($sql);

        if ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $user = $this->fetchOneUser($row);

            return $user;
        } else {
            return false;
        }
    }

    /**
     * Удалить пользователя с логином.
     * @param $login - логин.
     * @throws \Exception - бросается исключение, если пользователь отсутствует или при ошибке удаления.
     */
    public function deleteUser($login) {
        $login = $this->db->quote(strtolower($login));

        if ($this->isUserLoginPresent($login)) {
            try {
                $sql = "DELETE FROM spacenews.users WHERE login = $login";
                $this->db->exec($sql);
            } catch (\Exception $e) {
                throw new \Exception(self::USER_DELETING_FAILED_MESSAGE);
            }
        } else {
            throw new \Exception(self::ABSENT_LOGIN_MESSAGE);
        }
    }

    /**
     * Установить видимость новостной статьи.
     * @param $id - идентификатор статьи.
     * @param $isVisible - видимость.
     * @throws \Exception - бросается исключение при неудачной попытке.
     */
    public function setNewsItemVisibility($id, $isVisible) {
        try {
            $id = (int)$id;
            $isVisible = (int)$isVisible;
            $sql = "UPDATE spacenews.news SET visible = $isVisible WHERE id = $id";
            $this->db->exec($sql);
        } catch (\Exception $e) {
            throw new \Exception(self::NEWS_ITEM_UPDATING_FAILED_MESSAGE);
        }
    }

    /**
     * Обновить новостную статью.
     * @param $id - идентификатор.
     * @param $text - текст.
     * @param $isVisible - видимость.
     * @throws \Exception - бросается исключение при неудачной попытке.
     */
    public function updateNewsItem($id, $text, $isVisible) {
        try {
            $text = $this->db->quote(trim($text));
            $id = (int)$id;
            $isVisible = (int)$isVisible;
            $sql = "UPDATE spacenews.news SET visible = $isVisible, text = $text WHERE id = $id";
            $this->db->exec($sql);
        } catch (\Exception $e) {
            throw new \Exception(self::NEWS_ITEM_UPDATING_FAILED_MESSAGE);
        }
    }

    /**
     * Удалить новостную статью.
     * @param $id - идентификатор статьи.
     * @throws \Exception - бросается исключение при неудачной попытке.
     */
    public function deleteNewsItem($id) {
        try {
            $id = (int)$id;
            $sql = "DELETE FROM spacenews.news WHERE id = $id";
            $this->db->exec($sql);
        } catch (\Exception $e) {
            throw new \Exception(self::NEWS_ITEM_DELETE_FAILED_MESSAGE);
        }
    }

    /**
     * Обновить картинку новостной статьи.
     * @param $id - идентификатор статьи.
     * @param $imagePath - путь к картинке.
     * @throws \Exception - бросается исключение при неудачной попытке.
     */
    public function updateImage($id, $imagePath) {
        $resizeImagePath = $this->getResizeImagePath($imagePath);

        try {
            $id = (int)$id;
            $resizeImagePath = $this->db->quote($resizeImagePath);
            $sql = "UPDATE spacenews.news SET picturePath = $resizeImagePath WHERE id = $id";
            $this->db->exec($sql);
        } catch (\Exception $e) {
            throw new \Exception(self::NEWS_ITEM_PICTURE_UPDATING_FAILED_MESSAGE);
        }
    }

    /**
     * Активен ли источник новостей.
     * Неактивный не сканируется на наличие новых статей.
     * @param $name - название источника.
     * @return bool - активность.
     */
    public function isSourceActive($name) {
        $name = $this->db->quote($name);
        $sql = "SELECT active FROM spacenews.source WHERE name = $name";
        $result = $this->db->query($sql);

        return $result ? true : false;
    }

    /**
     * Обновить активность источника.
     * @param $id - идентификатор источника.
     * @param $isActive - новое состояние активности.
     * @throws \Exception - бросается исключение при неудачной попытке.
     */
    public function updateSourceActivity($id, $isActive) {
        try {
            $id = (int)$id;
            $isActive = (int)$isActive;
            $sql = "UPDATE spacenews.source SET active = $isActive WHERE id = $id";
            $this->db->exec($sql);
        } catch (\Exception $e) {
            throw new \Exception(self::SOURCE_ACTIVITY_UPDATING_FAILED_MESSAGE);
        }
    }

    /**
     * Сохранить пользователя в БД.
     * @param User $user - экземпляр пользователя.
     * @throws \Exception - бросаются исключения при ошибках сохранения.
     */
    public function saveUser(User $user) {
        $login = $this->db->quote(strtolower($user->getLogin()));

        if (!($this->isUserLoginPresent($login))) {
            try {
                $hash = $this->db->quote($user->getHash());
                $time = $this->db->quote($user->getCreationTime());
                $sql = "INSERT INTO spacenews.users(login, hash, creationTime) VALUES($login, $hash, $time)";
                $this->db->exec($sql);
            } catch (\Exception $e) {
                throw new \Exception(self::USER_SAVING_FAILED_MESSAGE);
            }
        } else {
            throw new \Exception(self::LOGIN_IS_IN_USE_MESSAGE);
        }
    }

    /**
     * Проверка присутствия пользователя с логином.
     * @param $login - логин.
     * @return bool - присутствие.
     */
    private function isUserLoginPresent($login) {
        $sql = "SELECT COUNT(*) FROM spacenews.users WHERE login = $login";
        $result = $this->db->query($sql);

        return ($result->fetchColumn() > 0);
    }

    /**
     * Проверка присутствия новостной статьи с таким же url.
     * @param NewsItem $newsItem - экземпляр новостной статьи.
     * @return bool - присутствие
     */
    private function isNewsItemUrlPresent(NewsItem $newsItem) {
        $id = null;
        $url = $this->db->quote(strtolower($newsItem->getUrl()));
        $sql = "SELECT id FROM spacenews.news WHERE URL = $url";
        $result = $this->db->query($sql);
        if (is_object($result))
            $id = $result->fetch(\PDO::FETCH_ASSOC)[self::ID_PARAMETER];

        return $id !== null;
    }

    /**
     * Получить источник новостей по id.
     * @param $id - идентификатор источника.
     * @return NewsSource - источник новостей.
     */
    private function getSourceById($id) {
        $id = (int)$id;
        $sql = "SELECT name, iconPath, siteURL, creationTime, active FROM spacenews.source WHERE id = $id";
        $result = $this->db->query($sql);
        $sourceRow = $result->fetch(\PDO::FETCH_ASSOC);

        return $this->fetchOneSource($sourceRow);
    }

    /**
     * Добавить экземпляр класса NewsSource в соответствующую таблицу source (без проверки наличия). Возвращает id записи.
     * @param NewsSource $newsSource - Новостной источник.
     * @return int - id добавленной записи.
     */
    private function insertNewsSource(NewsSource $newsSource) {
        $url = $this->db->quote(strtolower($newsSource->getSiteURL()));
        $isActive = (int)$newsSource->isActive();
        $creationTime = (int)$newsSource->getCreationTime();
        $sql = "INSERT INTO spacenews.source(name, iconPath, siteURL, creationTime, active)
                VALUES({$this->db->quote($newsSource->getName())}, 
                {$this->db->quote($newsSource->getIconPath())},
                $url,
                $creationTime,
                $isActive)";
        $this->db->exec($sql);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Получить id источника по URL
     * @param $url - Адрес для поиска
     * @return int - Найденный id или 0 в случае отсутствия
     */
    private function getSourceId($url) {
        $id = 0;
        $url = $this->db->quote(strtolower($url));
        $sql = "SELECT id FROM spacenews.source WHERE siteURL = $url";
        $result = $this->db->query($sql);
        if (is_object($result))
            $id = $result->fetch(\PDO::FETCH_ASSOC)[self::ID_PARAMETER];

        return $id;
    }

    /**
     * Установить правильную (из нашего сайта) ссылку на изображение новости.
     * Начальное изображение из статьи приводится к нужному размеру и сохраняется в папку изображений нашего сайта.
     * В случае ошибки при обработке картинки, устанавливается DEFAULT_IMAGE.
     * @param NewsItem $newsItem - Новостная статья
     */
    private function setPicture(NewsItem $newsItem) {
        try {
            $imageHandler = new ImageHandler($newsItem->getPicturePath(), self::IMAGES_PATH,
                self::IMAGE_FILE_NAME_LENGTH);
            $newsItem->setPicturePath($imageHandler->saveResizedImage(self::IMAGES_WIDTH));
        } catch (\Exception $e) {
            $newsItem->setPicturePath(self::IMAGES_PATH . self::DEFAULT_IMAGE);
        }
    }

    /**
     * Получить часть SQL-запроса с условиями в зависимости от sourceId и showInvisible
     * @return string
     */
    private function getSqlConditionsPart() {
        $conditionsQty = $this->getConditionsQuantity();

        $conditionsSql = "";
        if ($conditionsQty > 0) {
            $conditionsSql = " WHERE ";
            $conditionsSql .= $this->getConditionsExpression($conditionsQty);
        }

        return $conditionsSql;
    }

    /**
     * Сформировать sql-запрос для сохранения новостной статьи.
     * @param NewsItem $newsItem - экземпляр новостной статьи.
     * @return string - запрос.
     */
    private function getSqlSaveNewsItem(NewsItem $newsItem) {
        $sourceId = $this->saveNewsSource($newsItem->getSource());
        $url = strtolower($newsItem->getUrl());
        $isVisible = (int)$newsItem->isVisible();
        $this->setPicture($newsItem);
        $pubTime = (int)$newsItem->getPublicationTime();
        $addTime = (int)$newsItem->getAddingTime();

        $sql = "INSERT INTO spacenews.news(title, text, picturePath, source_id, pubTime, URL, addTime, visible)
                VALUES({$this->db->quote($newsItem->getTitle())}, 
                {$this->db->quote($newsItem->getText())},
                {$this->db->quote($newsItem->getPicturePath())},
                $sourceId,
                $pubTime,
                {$this->db->quote($url)},
                $addTime,
                $isVisible)";

        return $sql;
    }

    /**
     * Получить массив новостных статей из результата sql-запроса
     * @param $result - рузультат запроса.
     * @return array - массив статей.
     */
    private function fetchNews( $result) {
        $news = array();
        $sources = array();
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            if (!array_key_exists($row[self::SOURCE_ID_PARAMETER], $sources)) {
                $sources[$row[self::SOURCE_ID_PARAMETER]] = $this->getSourceById($row[self::SOURCE_ID_PARAMETER]);
            }
            $source = $sources[$row[self::SOURCE_ID_PARAMETER]];

            $news[] = $this->fetchOneNewsItem($row, $source);
        }

        return $news;
    }

    /**
     * Получить одну новостную статью.
     * @param $row - одна строка из результата запроса.
     * @param $source - источник новостней.
     * @return NewsItem - статья.
     */
    private function fetchOneNewsItem($row, $source) {
        $newsItem = new NewsItem($row[self::TITLE_PARAMETER], $source);
        $newsItem->setId($row[self::ID_PARAMETER]);
        $newsItem->setText($row[self::TEXT_PARAMETER]);
        $newsItem->setPicturePath($row[self::PICTURE_PATH_PARAMETER]);
        $newsItem->setPublicationTime($row[self::PUBLICATION_TIME_PARAMETER]);
        $newsItem->setUrl($row[self::URL_PARAMETER]);
        $newsItem->setAddingTime($row[self::ADD_TIME_PARAMETER]);
        $newsItem->setIsVisible($row[self::VISIBILITY_PARAMETER]);

        return $newsItem;
    }

    /**
     * Получить количество учитываемых условий.
     * @return int - количество условий.
     */
    private function getConditionsQuantity() {
        $conditionsQty = 0;
        if ($this->sourceId != 0)
            $conditionsQty++;
        if (!$this->showInvisible)
            $conditionsQty++;

        return $conditionsQty;
    }

    /**
     * Получить sql-выражение от количества условий.
     * @param $conditionsQty - количество условий.
     * @return string - sql-выражение.
     */
    private function getConditionsExpression($conditionsQty) {
        if ($conditionsQty == 2)
            $conditionsExpression = "source_id = $this->sourceId AND visible = TRUE ";
        elseif ($this->sourceId != 0)
            $conditionsExpression = "source_id = $this->sourceId ";
        else
            $conditionsExpression = "visible = TRUE ";

        return $conditionsExpression;
    }

    /**
     * Получить строку со списком необходимых id новостных статей.
     * Строка будет использована для дальнейшей выборки.
     * @param $from - порядковый номер первой статьи.
     * @param $quantity - количество необходимых статей.
     *
     * @return string - результирующая строка идентификаторов.
     * Пример: "1418, 1420, 1421, 1422, 1423, 1425, 1377, 1489, 1347, 1348"
     */
    private function getIdsString($from, $quantity) {
        $sqlSetCounter = "SET @row_number = 0";
        $this->db->exec($sqlSetCounter);

        $from = (int)$from;
        $to = $from + (int)$quantity - 1;
        $conditionsSql = $this->getSqlConditionsPart();

        $sqlGetIds = "SELECT t.id FROM (SELECT (@row_number:=@row_number + 1) AS num, id, pubtime, addtime " .
            "FROM spacenews.news " .
            $conditionsSql .
            "ORDER BY pubtime DESC, addtime) t " .
            "WHERE t.num >= $from AND t.num <= $to";

        $resultIds = $this->db->query($sqlGetIds);
        $ids = array_column($resultIds->fetchAll(\PDO::FETCH_ASSOC), self::ID_PARAMETER);
        $strIds = implode(", ", $ids);

        return $strIds;
    }

    /**
     * Извлечь один новостной источник из результата sql-запроса.
     * @param $sourceRow - запись результата запроса.
     * @return NewsSource - новостной источник.
     */
    private function fetchOneSource($sourceRow) {
        $source = new NewsSource($sourceRow[self::NAME_PARAMETER], $sourceRow[self::SITE_URL_PARAMETER]);
        $source->setIconPath($sourceRow[self::ICON_PATH_PARAMETER]);
        $source->setId($sourceRow[self::ID_PARAMETER]);
        $source->setCreationTime($sourceRow[self::CREATION_TIME_PARAMETER]);
        $source->setIsActive($sourceRow[self::ACTIVE_PARAMETER]);

        return $source;
    }

    /**
     * Извлечь одного пользователя из результата sql-запроса.
     * @param $row - запись результата запроса.
     * @return User - пользователь.
     */
    private function fetchOneUser($row) {
        $user = new User($row[self::LOGIN_PARAMETER]);
        $user->setHash($row[self::HASH_PARAMETER]);
        $user->setCreationTime($row[self::CREATION_TIME_PARAMETER]);

        return $user;
    }

    /**
     * Меняет размеры картинки на нужные (IMAGES_WIDTH) и возвращает новое полное имя.
     * Если возникла ошибка, то возвращается картинка по умолчанию (DEFAULT_IMAGE).
     * @param $imagePath - картинка для изменения размеров.
     * @return string - полный путь.
     */
    private function getResizeImagePath($imagePath) {
        try {
            $imageHandler = new ImageHandler($imagePath, self::IMAGES_PATH,
                self::IMAGE_FILE_NAME_LENGTH);
            $resizeImagePath = $imageHandler->saveResizedImage(self::IMAGES_WIDTH);
        } catch (\Exception $e) {
            $resizeImagePath = self::IMAGES_PATH . self::DEFAULT_IMAGE;
        }

        return $resizeImagePath;
    }

}