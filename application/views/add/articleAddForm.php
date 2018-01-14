<div id="add-form">
    <form enctype="multipart/form-data" action="/admin/add" method="post">
        <h1>Добавление статьи</h1>
        <a href='/'>
            <img title='Space News'
                 src='/images/logo_small.jpg' height='24'>
        </a>
        <p><label>Заголовок статьи: <input id="title" name="title" type="text"></label></p>
        <p><label>Дата публикации: <input id="date" name="date" type="date" value="<?= date('Y-m-d') ?>"></label></p>
        <p><label>Задать изображение: <input type="file" name="image" accept="image/*"/></label></p>
        <textarea name="text" id="text" spellcheck="false"><p></p></textarea>
        <input type="image" src="/images/save-button.png" name="submit" height="36">
        <div class="clear"></div>
    </form>
</div>