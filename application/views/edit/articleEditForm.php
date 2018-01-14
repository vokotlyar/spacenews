<div id="form">
    <form enctype="multipart/form-data" action="/admin/edit/id/<?= $newsItem->getId() ?>" method="post">
        <h1>Редактирование статьи</h1>
        <a target='_blank' href='<?= $newsItem->getSource()->getSiteURL() ?>'>
            <img title='<?= $newsItem->getSource()->getName() ?>'
                 src='<?= $newsItem->getSource()->getIconPath() ?>' height='24'>
        </a>
        <h2 <?= $adminClass ?>>
            <a target='_blank' href='<?= $newsItem->getUrl() ?>'><?= $newsItem->getTitle() ?></a>
        </h2>
        <div>
            <h4>
                <?= date("Y-m-d H:i:s", $newsItem->getPublicationTime()) ?>
            </h4>
            <div class="delete">
                <a href=<?= $deleteLink ?>>
                    <input type="button" value="Удалить статью">
                </a>
            </div>
            <div class="clear"></div>
        </div>
        <div class="thumbnail">
            <img title='<?= $newsItem->getTitle() ?>' src='/<?= $newsItem->getPicturePath() ?>'
                 width='300' alt='<?= $newsItem->getTitle() ?>'/>
            <p><label for="image">Изменить изображение:</label></p>
            <input type="file" id="image" name="image" accept="image/*"/>
            <div class="clear"></div>
        </div>
        <textarea name="text" id="text" spellcheck="false"><?= $newsText ?></textarea>
        <div>
            <select name="visibility">
                <option value="visible" <?= ($newsItem->isVisible() == 1) ? ' selected="selected"' : '' ?>>Отображать
                </option>
                <option value="invisible" <?= ($newsItem->isVisible() == 0) ? ' selected="selected"' : '' ?>>Скрыть
                </option>
            </select>
            <input type="image" src="/images/save-button.png" name="submit" height="36">
            <div class="clear"></div>
        </div>
        <input type="hidden" name="link" value="<?= $linkReturn ?>"/>
    </form>
</div>