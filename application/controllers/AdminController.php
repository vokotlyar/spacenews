<?php

namespace Controllers;

use Models\DB\SpaceNewsDB;
use Models\News\NewsItem;
use Models\News\NewsSource;
use Models\NewsParsers\HiNewsRu\HiNewsGetter;
use Models\NewsParsers\KosmosX\KosmosXGetter;
use Models\NewsParsers\VKosmose\VKosmoseGetter;
use Models\Renderers\ArticleEditRenderer;
use Models\Renderers\ArticleAddRenderer;
use Models\Renderers\NewsScanRenderer;
use Models\Renderers\NewsRenderer;
use Models\Renderers\LoginRenderer;
use Models\Renderers\UsersRenderer;
use Models\User\User;


/**
 * Класс AdminController для работы с административной панелью
 */
class AdminController implements Controller {

    /** Параметр отправки формы редактирования но */
    const EDIT_FORM_SUBMIT_PARAMETER = 'submit_x';
    /** Параметр количества новостных статей */
    const NEWS_QUANTITY_PARAMETER = 'newsQuantity';
    /** Начало пути для перенаправления на страницу сканирования новостей */
    const SCAN_REFERRER_PREFIX = "/admin/scan";
    /** Максимальная величина глубины сканирования */
    const MAX_SCAN_DEPTH = 200;
    /** Параметр глубины сканирования новостей */
    const SCAN_DEPTH_PARAMETER = 'depth';
    /** Параметр идентификатора новостной статьи */
    const ID_PARAMETER = 'id';
    /** Параметр даты публикации новостной статьи */
    const DATE_PARAMETER = 'date';
    /** Параметр текста заголовка новостной статьи */
    const TITLE_PARAMETER = 'title';
    /** Начало заголовка новостной статьи по умолчанию */
    const DEFAULT_TITLE_PREFIX = "News @ ";
    /** Формат отображения времени */
    const TIME_FORMAT = "Y-m-d H:i:s";
    /** Собственное имя источника новостей */
    const SELF_SOURCE_NAME = "SpaceNews";
    /** URL собственного источника новостей */
    const SELF_SOURCE_URL = "/news/show/source/1";
    /** Путь к иконке собственного источника новостей */
    const SELF_SOURCE_ICON_PATH = "/images/logo_small.jpg";
    /** Параметр временного имени загружаемого изображения */
    const IMAGE_TMP_NAME_PARAMETER = "tmp_name";
    /** Параметр имени загружаемого изображения */
    const IMAGE_NAME_PARAMETER = "name";
    /** Временная директория для загружаемых файлов */
    const TMP_IMAGE_DIR = "news_pictures/tmp/";
    /** Параметр ошибок при загрузке файла */
    const ERROR_PARAMETER = 'error';
    /** Параметр с файлом изображения для статьи */
    const IMAGE_PARAMETER = 'image';
    /** Параметр с текстом статьи */
    const ARTICLE_TEXT_PARAMETER = 'text';
    /** Имя параметра видимости статьи */
    const ARTICLE_VISIBILITY_PARAMETER = 'visibility';
    /** Значение параметра, если статья видимая */
    const VISIBLE_ARTICLE_VALUE = 'visible';
    /** Часть ссылки на id статьи */
    const REFERRER_ID_SUFFIX = "#id-";
    /** Имя параметра со ссылкой */
    const LINK_PARAMETER = 'link';
    /** Дефолтная страница возврата после удаления */
    const DEFAULT_RETURN_LOCATION = "Location: /admin/show";
    /** Параметр источника для страницы возврата */
    const LOCATION_SOURCE_PARAMETER = "/source/";
    /** Параметр номера для страницы возврата */
    const LOCATION_PAGE_PARAMETER = "/page/";
    /** Заголовок неавторизованного пользователя */
    const UNAUTHORIZED_HEADER = "HTTP/1.0 401 Unauthorized";
    /** Сообщение о неправильном логине или пароле при авторизации */
    const INCORRECT_LOGIN_OR_PASSWORD_MESSAGE = "Неправильный логин или пароль!";
    /** Страница на которую будет переход (по умолчанию) после успешной авторизации */
    const DEFAULT_LOGIN_REFERRER = '/show';
    /** Имя параметра с адресом для переадресации */
    const REFERRER_PARAMETER = 'ref';
    /** Начало адресов указывающих куда нужно перейти после успешной авторизации */
    const REFERRER_PREFIX = '/ref';
    /** Сессионная переменная отвечающая за авторизованность */
    const SESSION_ADMIN_PARAMETER = 'admin';
    /** Начало адресов на которые идет переадресация после успешной авторизации */
    const REDIRECT_PREFIX_AFTER_LOGIN = "/admin";
    /** Сообщение при отсутствии пользователя в БД */
    const ABSENT_LOGIN_EXCEPTION_MESSAGE = "Login is absent!";
    /** Сообщение при присутствии добавляемого пользователя в БД */
    const LOGIN_IN_USE_EXCEPTION_MESSAGE = "Login is already in use!";
    /** Начало пути для перенаправления на страницу управления пользователями */
    const USERS_REFERRER_PREFIX = "/admin/users/login/";
    /** Окончание пути для перенаправления на страницу управления пользователями */
    const USERS_REFERRER_SUFFIX = "/result/";
    /** Путь для перенаправления на страницу авторизации */
    const LOGIN_REFERRER = '/admin/login';
    /** Регулярное выражение для неразрешенных символов логина */
    const LOGIN_UNALLOWED_EXPRESSION = '/[^A-Za-z0-9\-]/';
    /** Значение количества статей на административной странице по умолчанию */
    const DEFAULT_NEWS_PER_PAGE = 25;
    /** Имя параметра с номером источника */
    const SOURCE_PARAMETER = 'source';
    /** Имя параметра с кодом результата */
    const RESULT_PARAMETER = 'result';
    /** Имя параметра с логином пользователя */
    const LOGIN_PARAMETER = 'login';
    /** Имя параметра с паролем пользователя */
    const PASSWORD_PARAMETER = 'password';
    /** Имя параметра с подтверждением пароля */
    const CONFIRM_PASSWORD_PARAMETER = 'confirm-password';
    /** Имя параметра с номером текущей страницы */
    const CURRENT_PAGE_PARAMETER = 'page';
    /** Имя куки с количеством статей на странице */
    const NEWS_QUANTITY_COOKIE = 'ad_qty';
    /** Максимально возможное время истечения куки */
    const MAX_EXPIRE_TIME = 2147483647;
    /** Путь к куке с количеством новостей на странице */
    const NEWS_QUANTITY_COOKIE_PATH = '/admin/';
    /** Имя параметра с количеством новостей на странице */
    const NEWS_PER_PAGE_PARAMETER = 'newsPerPage';

    /** Коды результатов добавления/удаления пользователей */
    const USER_ADDED = 1;
    const USER_EXISTS = 2;
    const INCORRECT_LOGIN_OR_PASSWORD = 3;
    const USER_ADDING_ERROR = 4;
    const USER_DELETED = 5;
    const USER_DELETE_ERROR = 6;
    const USER_NOT_FOUND = 7;

    /** Имена источников новостных статей */
    const HI_NEWS_SOURCE_NAME = "Hi-News.ru";
    const KOSMOS_X_SOURCE_NAME = "KOSMOS-X";
    const V_KOSMOSE_SOURCE_NAME = "V-Kosmose";


    /** @var FrontController - экземпляр главного контроллера */
    private $frontController;

    /**
     * AdminController constructor.
     * Присвоение полю $frontController экземпляра класса-синглтона FrontController
     */
    public function __construct() {
        $this->frontController = FrontController::getInstance();
    }


    /**
     * Обработчик отображения новостных статей.
     * Определяется текущая страница, количество новостей на странице и новостной источник.
     * Далее, эти параметры передаются модели NewsRenderer(), которая формирует html-документ для вывода пользователю.
     */
    public function showAction() {
        $newsPerPage = $this->getNewsPerPage();
        $currentPage = $this->getCurrentPage();
        $sourceId = $this->getSourceId();

        $renderer = new NewsRenderer($currentPage, $newsPerPage, $sourceId, true);
        $html = $renderer->adminViewRender();

        $this->frontController->setBody($html);
    }

    /**
     * Обработчик редактирования новостной статьи.
     */
    public function editAction() {
        $id = (int)$this->frontController->getParams()[self::ID_PARAMETER];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->editPostHandler($id);
        }

        $renderer = new ArticleEditRenderer($id);
        $html = $renderer->render();

        $this->frontController->setBody($html);
    }

    /**
     * Обработчик добавления новостной статьи.
     */
    public function addAction() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->addPostHandler();
        }

        $renderer = new ArticleAddRenderer();
        $html = $renderer->render();

        $this->frontController->setBody($html);
    }

    /**
     * Обработчик сканирования (парсинг интернет-сайтов) новых статей.
     */
    public function scanAction() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->scanPostHandler();
        }

        $renderer = new NewsScanRenderer();
        $this->setScanRendererFields($renderer);
        $html = $renderer->render();

        $this->frontController->setBody($html);
    }

    /**
     * Обработчик удаления новостной статьи.
     * После удаления происходит возврат на страницу показа всех новостей
     * с которой переходили на редактирование статьи (учитываются source и page).
     */
    public function deleteAction() {
        (new SpaceNewsDB())->deleteNewsItem((int)$this->frontController->getParams()[self::ID_PARAMETER]);

        header($this->getReturnLocation());
        exit;
    }

    /**
     * Обработчик изменения количества отображаемых новостных статей на странице.
     * Если приходит запрос методом POST с параметром количества статей на странице, то
     * перезаписывается соответствующая кука и происходит перенаправление на первую страницу, но
     * с запоминанием источника новостей (если он был выбран).
     */
    public function newsquantityAction() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && (int)$_POST[self::NEWS_PER_PAGE_PARAMETER]) {
            $referrer = $_SERVER['HTTP_REFERER'];
            $location = explode(self::CURRENT_PAGE_PARAMETER, $referrer)[0];
            $newsPerPage = (int)$_POST[self::NEWS_PER_PAGE_PARAMETER];

            setcookie(self::NEWS_QUANTITY_COOKIE, $newsPerPage,
                self::MAX_EXPIRE_TIME, self::NEWS_QUANTITY_COOKIE_PATH);

            header("Location: " . $location);
        } else {
            header(self::DEFAULT_RETURN_LOCATION);
        }

        exit;
    }

    /**
     * Обработчик страницы авторизации
     */
    public function loginAction() {
        session_start();
        header(self::UNAUTHORIZED_HEADER);

        $renderer = new LoginRenderer();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->loginPostHandler($renderer);
        }
        $renderer->setReferrer($this->getReferrerFromParams());
        $html = $renderer->render();

        $this->frontController->setBody($html);
    }

    /**
     * Обработчик выхода залогиненного пользователя.
     * Производится logout с перенаправлением на страницу авторизации.
     */
    public function logoutAction() {
        session_destroy();
        header('Location: ' . self::LOGIN_REFERRER);
        exit;
    }

    /**
     * Обработчик страницы управления пользователями
     */
    public function usersAction() {
        $renderer = new UsersRenderer();
        $this->setResultParams($renderer);
        $html = $renderer->render();

        $this->frontController->setBody($html);
    }

    /**
     * Обработчик добавления пользователя.
     * Производится попытка добавления указанного в запросе пользователя.
     * Код результата передается в параметрах на страницу управления пользователями.
     */
    public function adduserAction() {
        $login = $this->clearLogin($_POST[self::LOGIN_PARAMETER]);
        $result = $this->addLogin($login);

        header("Location: " . self::USERS_REFERRER_PREFIX . $login . self::USERS_REFERRER_SUFFIX . $result);
        exit;
    }

    /**
     * Обработчик удаления пользователя.
     * Производится попытка удаления указанного в запросе пользователя.
     * Код результата передается в параметрах на страницу управления пользователями.
     */
    public function deleteuserAction() {
        $login = $this->clearLogin($this->frontController->getParams()[self::LOGIN_PARAMETER]);
        $result = $this->deleteLogin($login);

        header("Location: " . self::USERS_REFERRER_PREFIX . $login . self::USERS_REFERRER_SUFFIX . $result);
        exit;
    }



    /**
     * Получить id источника новостей для отображения
     * @return int - id источника или 0, если источник не задан (для всех доступных)
     */
    private function getSourceId() {
        return !empty((int)$this->frontController->getParams()[self::SOURCE_PARAMETER]) ?
            ((int)$this->frontController->getParams()[self::SOURCE_PARAMETER]) : 0;
    }

    /**
     * Получить номер текущей страницы для отображения
     * @return int - номер страницы или 1, если она не задана
     */
    private function getCurrentPage() {
        return !empty($this->frontController->getParams()[self::CURRENT_PAGE_PARAMETER]) ?
            $this->frontController->getParams()[self::CURRENT_PAGE_PARAMETER] : 1;
    }

    /**
     * Получить количество новостей на странице.
     * Если присутствует необходимая кука, то берется ее значение.
     * Иначе - берется значение по умолчанию и создается соответствующая кука.
     * @return int
     */
    private function getNewsPerPage() {
        $newsPerPage = self::DEFAULT_NEWS_PER_PAGE;

        if (isset($_COOKIE[self::NEWS_QUANTITY_COOKIE])) {
            $newsPerPage = (int)$_COOKIE[self::NEWS_QUANTITY_COOKIE] > 0 ?
                (int)$_COOKIE[self::NEWS_QUANTITY_COOKIE] : $newsPerPage;
        } else {
            setcookie(self::NEWS_QUANTITY_COOKIE, $newsPerPage,
                self::MAX_EXPIRE_TIME, self::NEWS_QUANTITY_COOKIE_PATH);
        }

        return $newsPerPage;
    }

    /**
     * Метод очистки введенного пользователем логина от недопустимых символов.
     * @param $login - полученный логин
     * @return string - очищенный логин
     */
    private function clearLogin($login) {
        $login = str_replace(' ', '', $login);
        $login = preg_replace(self::LOGIN_UNALLOWED_EXPRESSION, '', $login);
        $login = strtolower($login);

        return $login;
    }

    /**
     * Попытка удалить указанный логин из БД с возвращением кода результата.
     * @param $login - логин для удаления
     * @return int - код результата удаления
     */
    private function deleteLogin($login) {
        $result = self::USER_DELETE_ERROR;

        if ('' != $login) {
            try {
                (new SpaceNewsDB())->deleteUser($login);
                $result = self::USER_DELETED;
            } catch (\Exception $e) {
                if (self::ABSENT_LOGIN_EXCEPTION_MESSAGE === $e->getMessage()) {
                    $result = self::USER_NOT_FOUND;
                }
            }
        }

        return $result;
    }

    /**
     * Попытка добавить указанный логин в БД с возвращением кода результата.
     * @param $login - логин для добавления
     * @return int - код результата добавления
     */
    private function addLogin($login) {
        if ($this->isLoginAndPasswords($login)) {
            $result = $this->addCheckedUser($login);
        } else {
            $result = self::INCORRECT_LOGIN_OR_PASSWORD;
        }

        return $result;
    }

    /**
     * Проверка корректности логина и пароля, и совпадения паролей.
     * @param $login - логин
     * @return bool - корректны ли логин и пароли
     */
    private function isLoginAndPasswords($login) {
        return '' != $_POST[self::PASSWORD_PARAMETER] && '' != $login &&
            $_POST[self::PASSWORD_PARAMETER] === $_POST[self::CONFIRM_PASSWORD_PARAMETER];
    }

    /**
     * Добавление пользователя с проверенным логином и паролем.
     * @param $login - логин пользователя
     * @return int - код результата добавления
     */
    private function addCheckedUser($login) {
        $result = self::USER_ADDING_ERROR;

        try {
            $user = new User($login);
            $user->setHashFromPassword($_POST[self::PASSWORD_PARAMETER]);
            (new SpaceNewsDB())->saveUser($user);
            $result = self::USER_ADDED;
        } catch (\Exception $e) {
            if (self::LOGIN_IN_USE_EXCEPTION_MESSAGE === $e->getMessage()) {
                $result = self::USER_EXISTS;
            }
        }

        return $result;
    }

    /**
     * Установка результирующих параметров (если они есть) для отображения на странице
     * @param $renderer - генаратор hmtl-страницы
     */
    private function setResultParams(UsersRenderer $renderer) {
        if ($this->frontController->getParams()[self::RESULT_PARAMETER]) {
            $login = $this->clearLogin($this->frontController->getParams()[self::LOGIN_PARAMETER]);
            $result = (int)$this->frontController->getParams()[self::RESULT_PARAMETER];

            $renderer->setLogin($login);
            $renderer->setResultCode($result);
        }
    }

    /**
     * Формирование адреса страницы, по которому шел неавторизованный пользователь.
     * @return string - адрес
     */
    private function getReferrerFromParams() {
        $referrer = '';
        foreach ($this->frontController->getParams() as $paramName => $paramValue) {
            $referrer .= "/" . trim(strip_tags($paramName)) . "/" . trim(strip_tags($paramValue));
        }

        return $referrer;
    }

    /**
     * Обработчик данных формы авторизации пришедших методом POST
     * @param $renderer - генаратор hmtl-страницы
     */
    private function loginPostHandler(LoginRenderer $renderer) {
        $login = $this->clearLogin($_POST[self::LOGIN_PARAMETER]);
        $renderer->setLogin($login);
        $user = (new SpaceNewsDB())->getUser($login);

        if ($user && $user->isPassword($_POST[self::PASSWORD_PARAMETER])) {
            $this->correctAuthenticationHandler();
        } else {
            $renderer->setMessage(self::INCORRECT_LOGIN_OR_PASSWORD_MESSAGE);
        }
    }

    /**
     * Обработчик после корректной авторизации.
     * Устанавливается сессионная переменная дающая доступ к админ-страницам сайта.
     * Пользователь перенаправляется на запрашиваемую им (до авторизации) или дефолтную страницу.
     */
    private function correctAuthenticationHandler() {
        $_SESSION[self::SESSION_ADMIN_PARAMETER] = true;

        $referrer = self::DEFAULT_LOGIN_REFERRER;
        if (trim(strip_tags($this->frontController->getParams()[self::REFERRER_PARAMETER]))) {
            $referrer = $this->getReferrerFromParams();
        }

        header("Location: " . self::REDIRECT_PREFIX_AFTER_LOGIN .
            str_replace(self::REFERRER_PREFIX, '', $referrer));

        exit;
    }

    /**
     * Определить страницу возврата после удаления статьи.
     * @return string - страница для возврата
     */
    private function getReturnLocation() {
        $returnLocation = self::DEFAULT_RETURN_LOCATION;

        if ($this->frontController->getParams()[self::SOURCE_PARAMETER]) {
            $returnLocation .= self::LOCATION_SOURCE_PARAMETER .
                (int)$this->frontController->getParams()[self::SOURCE_PARAMETER];
        }

        if ($this->frontController->getParams()[self::CURRENT_PAGE_PARAMETER]) {
            $returnLocation .= self::LOCATION_PAGE_PARAMETER .
                (int)$this->frontController->getParams()[self::CURRENT_PAGE_PARAMETER];
        }

        return $returnLocation;
    }

    /**
     * Обработчик данных (пришедших методом POST) формы редактирования новостной статьи.
     * После редактирования страница спозиционируется на заголовок статьи.
     * @param $id - идентификатор новостной статьи.
     */
    private function editPostHandler($id) {
        $text = $_POST[self::ARTICLE_TEXT_PARAMETER];

        if (!$_POST[self::EDIT_FORM_SUBMIT_PARAMETER]) {
            $this->changeArticleVisibility($id);
            header("Location: " . $_SERVER['HTTP_REFERER'] . self::REFERRER_ID_SUFFIX . $id);
        } else {
            $this->updateArticle($id, $text);
            header("Location: " . $_POST[self::LINK_PARAMETER]);
        }

        exit;
    }

    /**
     * Изменить видимость новостной статьи.
     * @param $id - идентификатор статьи.
     */
    private function changeArticleVisibility($id) {
        $visibility = (self::VISIBLE_ARTICLE_VALUE === $_POST[self::ARTICLE_VISIBILITY_PARAMETER]) ? 1 : 0;
        $db = new SpaceNewsDB();
        $db->setNewsItemVisibility($id, $visibility);
    }

    /**
     * Обновить новостную статью.
     * @param $id - идентификатор.
     * @param $text - новый текст.
     */
    private function updateArticle($id, $text) {
        $visibility = (self::VISIBLE_ARTICLE_VALUE === $_POST[self::ARTICLE_VISIBILITY_PARAMETER]) ? 1 : 0;
        $db = new SpaceNewsDB();
        $db->updateNewsItem($id, $text, $visibility);

        if ($_FILES[self::IMAGE_PARAMETER] && $_FILES[self::IMAGE_PARAMETER][self::ERROR_PARAMETER] === 0) {
            $this->updateArticleImage($id, $db);
        }
    }

    /**
     * Обновить изображение новостной статьи.
     * Закаченное на сервер изображение перемещается в нужную папку и изменяется соответствующий путь в БД.
     * @param $id - идентификатор статьи.
     * @param $db - экземпляр базы данных.
     */
    private function updateArticleImage($id, SpaceNewsDB $db) {
        $imagePath = self::TMP_IMAGE_DIR . $_FILES[self::IMAGE_PARAMETER][self::IMAGE_NAME_PARAMETER];
        move_uploaded_file($_FILES[self::IMAGE_PARAMETER][self::IMAGE_TMP_NAME_PARAMETER], $imagePath);
        $db->updateImage($id, $imagePath);
        unlink($imagePath);
    }

    /**
     * Обработчик данных (пришедших методом POST) формы добавления новостной статьи.
     */
    private function addPostHandler() {
        $title = $this->getAddingNewsTitle();
        $source = $this->getSelfSource();
        $imagePath = $this->getPicturePath();

        $newsItem = new NewsItem($title, $source);
        $this->setNewsItemFields($newsItem, $imagePath);

        (new SpaceNewsDB())->saveNewsItem($newsItem);

        $this->removeTmpPictureFile($imagePath);

        header(self::DEFAULT_RETURN_LOCATION);
        exit;
    }

    /**
     * Получить заголовок добавляемой статьи.
     * @return string - заголовок.
     */
    private function getAddingNewsTitle() {
        $title = trim(strip_tags($_POST[self::TITLE_PARAMETER]));
        if (!$title) {
            $date = date(self::TIME_FORMAT, time());
            $title = self::DEFAULT_TITLE_PREFIX . $date;
        }

        return $title;
    }

    /**
     * Получить источник добавляемой через форму на сайте (не сканирование) статьи.
     * @return NewsSource - источник новости.
     */
    private function getSelfSource() {
        $source = new NewsSource(self::SELF_SOURCE_NAME, self::SELF_SOURCE_URL);
        $source->setIconPath(self::SELF_SOURCE_ICON_PATH);

        return $source;
    }

    /**
     * Получить путь к картинке новостной статьи.
     * @return string - путь к картинке.
     */
    private function getPicturePath() {
        $imagePath = '';
        if ($_FILES[self::IMAGE_PARAMETER] && $_FILES[self::IMAGE_PARAMETER][self::ERROR_PARAMETER] === 0) {
            $imagePath = self::TMP_IMAGE_DIR . $_FILES[self::IMAGE_PARAMETER][self::IMAGE_NAME_PARAMETER];
            move_uploaded_file($_FILES[self::IMAGE_PARAMETER][self::IMAGE_TMP_NAME_PARAMETER], $imagePath);
        }

        return $imagePath;
    }

    /**
     * Удалить временный файл с картинкой для статьи.
     * @param $imagePath - путь к картинке.
     */
    private function removeTmpPictureFile($imagePath) {
        if ($_FILES[self::IMAGE_PARAMETER] && $_FILES[self::IMAGE_PARAMETER][self::ERROR_PARAMETER] === 0) {
            unlink($imagePath);
        }
    }

    /**
     * Получить время публикации добавляемой статьи.
     * @return int - Время публикации в int-формате.
     */
    private function getPublicationTime() {
        $pubTime = time();
        if (strtotime(trim(strip_tags($_POST[self::DATE_PARAMETER])))) {
            $pubTime = strtotime(trim(strip_tags($_POST[self::DATE_PARAMETER])));
        }

        return $pubTime;
    }

    /**
     * Установка полей новостной статьи.
     * @param $newsItem - экземпляр статьи.
     * @param $imagePath - путь к картинке статьи.
     */
    private function setNewsItemFields(NewsItem $newsItem, $imagePath) {
        $newsItem->setText($_POST[self::ARTICLE_TEXT_PARAMETER]);
        $newsItem->setAddingTime(time());
        $newsItem->setPublicationTime($this->getPublicationTime());
        $newsItem->setPicturePath($imagePath);
    }

    /**
     * Установка полей сканнера новостных статей.
     * @param $renderer - экземпляр NewsScanRenderer.
     */
    private function setScanRendererFields(NewsScanRenderer $renderer) {
        if ($this->frontController->getParams()) {
            $renderer->setPrevNewsQuantities($this->frontController->getParams());
        }
    }

    /**
     * Обработчик данных (пришедших методом POST) формы сканирования новостных статей.
     */
    private function scanPostHandler() {
        $this->addAbsentNews();
        $qtyString = $this->getQuantityNewsString();

        header("Location: " . self::SCAN_REFERRER_PREFIX . $qtyString);
        exit;
    }

    /**
     * Получить глубину сканирования (максимальное количество статей на выходе).
     * @return int - глубина сканирования.
     */
    private function getScanDepth() {
        $newsScanDepth = (int)$_POST[self::SCAN_DEPTH_PARAMETER] >= 1 ? (int)$_POST[self::SCAN_DEPTH_PARAMETER] : 1;
        if ($newsScanDepth > self::MAX_SCAN_DEPTH)
            $newsScanDepth = self::MAX_SCAN_DEPTH;

        return $newsScanDepth;
    }

    /**
     * Получить массив отмеченных для сканирования парсеров.
     * @param $db - экземпляр базы данных.
     * @return array - массив парсеров.
     */
    private function getSelectedParsers(SpaceNewsDB $db) {
        $parsers = array();

        if ($_POST[self::SOURCE_PARAMETER]) {
            if (in_array(self::HI_NEWS_SOURCE_NAME, $_POST[self::SOURCE_PARAMETER]) &&
                $db->isSourceActive(self::HI_NEWS_SOURCE_NAME)) {

                $parsers[] = new HiNewsGetter();
            }

            if (in_array(self::KOSMOS_X_SOURCE_NAME, $_POST[self::SOURCE_PARAMETER]) &&
                $db->isSourceActive(self::KOSMOS_X_SOURCE_NAME)) {

                $parsers[] = new KosmosXGetter();
            }

            if (in_array(self::V_KOSMOSE_SOURCE_NAME, $_POST[self::SOURCE_PARAMETER]) &&
                $db->isSourceActive(self::V_KOSMOSE_SOURCE_NAME)) {

                $parsers[] = new VKosmoseGetter();
            }
        }

        return $parsers;
    }

    /**
     * Добавить отсутствующие новостные статьи в БД.
     * Производится сканирование последних N (количество задается на форме) статей
     * для отмеченных на форме источников.
     * Затем, отсутствующие статьи сохраняются.
     */
    private function addAbsentNews() {
        $newsScanDepth = $this->getScanDepth();
        $db = new SpaceNewsDB();

        $parsers = $this->getSelectedParsers($db);

        if (!empty($parsers)) {
            foreach ($parsers as $parser) {
                $lastNews = $parser->getLastNews($newsScanDepth);
                $db->saveAbsentNews($lastNews);
            }
        }
    }

    /**
     * Получить строку с количеством новостных статей для каждого источника до сканирования.
     * @return string - строка с количествами вида /id/кол-во/id/кол-во... ("/1/6/2/165/3/156/4/324")
     */
    private function getQuantityNewsString() {
        $qtyString = '';
        foreach ($_POST[self::NEWS_QUANTITY_PARAMETER] as $id => $quantity) {
            $qtyString .= '/' . $id . '/' . $quantity;
        }

        return $qtyString;
    }

}