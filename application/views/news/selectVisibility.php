<form class="visibility" action="/admin/edit/id/<?= $article->getId() ?>" method="post">
    <select name="visibility" onchange="this.form.submit();">
        <option value="visible" <?= ($article->isVisible() == 1) ? ' selected="selected"' : '' ?>>Отображать</option>
        <option value="invisible" <?= ($article->isVisible() == 0) ? ' selected="selected"' : '' ?>>Скрыть</option>
    </select>
</form>
