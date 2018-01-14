<?php

namespace Models\Renderers;

use PHPUnit\Framework\TestCase;


/**
 * Тестирование класса PagingRenderer
 */
class PagingRendererTest extends TestCase {

    /**
     * Тест правильности заполнения массива с номерами страниц в панели перехода по страницам новостей.
     *
     * Должны отображаться: первая, последняя, текущая и по две страницы в обе стороны от текущей.
     * 0 - для промежутков (если они есть) между (текущей +/- 2) и первой/последней.
     *
     * Примеры:
     * Текущая : массив
     * 1 : 1-2-3-0-N
     * 2 : 1-2-3-4-0-N
     * 3 : 1-2-3-4-5-0-N
     * 4 : 1-2-3-4-5-6-0-N
     * 5 : 1-0-3-4-5-6-7-0-N
     * 6 : 1-0-4-5-6-7-8-0-N
     *
     * @param $currentPage
     * @param $lastPage
     * @param $linksPrefix
     * @param array $result
     *
     * @dataProvider dataProvider
     */
    public function testFillPages($currentPage, $lastPage, $linksPrefix, $result) {
        $renderer = new PagingRenderer($currentPage, $lastPage, $linksPrefix);

        $this->assertEquals($result, $renderer->getPages());
    }

    /**
     * Набор данных для тестирования метода testFillPages($currentPage, $lastPage, $linksPrefix, $result).
     * @return array - данные для тестирования.
     */
    public function dataProvider() {
        return array(
            array(1, 1, '', array(1)),
            array(10, 1, '', array(1)),
            array(-10, 1, '', array(1)),

            array(1, 2, '', array(1, 2)),
            array(2, 2, '', array(1, 2)),

            array(1, 3, '', array(1, 2, 3)),
            array(2, 3, '', array(1, 2, 3)),
            array(3, 3, '', array(1, 2, 3)),

            array(2, 4, '', array(1, 2, 3, 4)),

            array(1, 5, '', array(1, 2, 3, 0, 5)),
            array(2, 5, '', array(1, 2, 3, 4, 5)),
            array(5, 5, '', array(1, 0, 3, 4, 5)),

            array(1, 6, '', array(1, 2, 3, 0, 6)),
            array(4, 6, '', array(1, 2, 3, 4, 5, 6)),
            array(6, 6, '', array(1, 0, 4, 5, 6)),

            array(5, 9, '', array(1, 0, 3, 4, 5, 6, 7, 0, 9)),

            array(1, 100, '', array(1, 2, 3, 0, 100)),
            array(0, 100, '', array(1, 2, 3, 0, 100)),
            array(-1000, 100, '', array(1, 2, 3, 0, 100)),
            array(2, 100, '', array(1, 2, 3, 4, 0, 100)),
            array(3, 100, '', array(1, 2, 3, 4, 5, 0, 100)),
            array(4, 100, '', array(1, 2, 3, 4, 5, 6, 0, 100)),
            array(5, 100, '', array(1, 0, 3, 4, 5, 6, 7, 0, 100)),
            array(6, 100, '', array(1, 0, 4, 5, 6, 7, 8, 0, 100)),
            array(95, 100, '', array(1, 0, 93, 94, 95, 96, 97, 0, 100)),
            array(96, 100, '', array(1, 0, 94, 95, 96, 97, 98, 0, 100)),
            array(97, 100, '', array(1, 0, 95, 96, 97, 98, 99, 100)),
            array(98, 100, '', array(1, 0, 96, 97, 98, 99, 100)),
            array(99, 100, '', array(1, 0, 97, 98, 99, 100)),
            array(100, 100, '', array(1, 0, 98, 99, 100)),
            array(150, 100, '', array(1, 0, 98, 99, 100)),
        );
    }

}
