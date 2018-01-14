<div id="sidebar">
<!--    <div id="search-form">
        <h3><span>Поиск</span></h3>
        <form action="#" method="post">
            <div class="text-field">
                <input type="text" value="Search Here..."/>
            </div>
            <input type="image" src="/images/search_btn.png"/>
        </form>
    </div>-->
    <div id="sources">
        <h3><span>Выбор источника</span></h3>
        <ul>
            <?php
            foreach ($this->sources as $source) {
                echo '<li>';
                include 'newsSource.php';
                echo '</li>';
            }
            ?>
        </ul>
    </div>
<!--    <div id="subscribe-form">
        <h3><span>Подписаться на рассылку</span></h3>
        <form action="#" method="post">
            <div class="text-field">
                <input type="text" value="Enter your email"/>
            </div>
            <input type="image" src="/images/subscribe_btn.png"/>
        </form>
    </div>
    <div id="categories">
        <h3><span>Категории</span></h3>
        <ul class="left">
            <li><a href="#">Солнечная система</a></li>
            <li><a href="#">Дальний космос</a></li>
            <li><a href="#">Звезды</a></li>
            <li><a href="#">SpaceX</a></li>
            <li><a href="#">Марс</a></li>
            <li><a href="#">Луна</a></li>
            <li><a href="#">Сатурн</a></li>
            <li><a href="#">Юпитер</a></li>
            <li><a href="#">Экзопланеты</a></li>
            <li><a href="#">МКС</a></li>
            <li><a href="#">Галактики</a></li>
            <li><a href="#">Кометы</a></li>
            <li><a href="#">Туманности</a></li>
            <li><a href="#">Астероиды и метеориты</a></li>
            <li><a href="#">Черные дыры</a></li>
        </ul>
        <div class="clear"></div>
    </div>-->
</div>