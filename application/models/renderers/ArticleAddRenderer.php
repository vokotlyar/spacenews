<?php

namespace Models\Renderers;


/**
 * Класс-генератор страницы добавления новостной статьи.
 */
class ArticleAddRenderer {

    /** View-страница */
    const INCLUDE_VIEW = __DIR__ . '/../../views/add/addNewsItemView.php';
    /** Идентификатор футера */
    const FOOTER_ID = "footer-bottom-admin";

    /**
     * Сгенерировать необходимую html-страницу.
     * @return string - html-код страницы.
     */
    public function render() {
        $footerId = self::FOOTER_ID;
        ob_start();
        include self::INCLUDE_VIEW;

        return ob_get_clean();
    }

}