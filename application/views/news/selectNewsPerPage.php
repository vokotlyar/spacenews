<form class="news-per-page" action="<?= $newsQuantityAction ?>" method="post">
    <select name="newsPerPage" onchange="this.form.submit();">
        <option value="5" <?= $this->newsPerPage == 5 ? ' selected="selected"' : '' ?>>5</option>
        <option value="10" <?= $this->newsPerPage == 10 ? ' selected="selected"' : '' ?>>10</option>
        <option value="25" <?= $this->newsPerPage == 25 ? ' selected="selected"' : '' ?>>25</option>
        <option value="50" <?= $this->newsPerPage == 50 ? ' selected="selected"' : '' ?>>50</option>
        <option value="100" <?= $this->newsPerPage == 100 ? ' selected="selected"' : '' ?>>100</option>
    </select>
</form>
