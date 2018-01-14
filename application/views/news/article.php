<div class="<?= $this->articleClass ?>"><?= $newsLink ?>
    <div class="thumbnail">
        <img title='<?= $article->getTitle() ?>' src='/<?= $article->getPicturePath() ?>'
             width='300' alt='<?= $article->getTitle() ?>'/>
    </div>
    <div class="post-content">
        <a target='_blank' href='<?= $article->getSource()->getSiteURL() ?>'>
            <img title='<?= $article->getSource()->getName() ?>'
                 src='<?= $article->getSource()->getIconPath() ?>' height='24'>
        </a>
        <h2  <?= $adminClass ?>><a target='<?= $target ?>' href='<?= $newsHref ?>'><?= $article->getTitle() ?></a></h2>
        <h4><?= date("Y-m-d H:i:s", $article->getPublicationTime()) ?></h4>


        <?= $firstParagraph ?>
        <input type="checkbox" id="n<?= $i ?>" class="hide"/>
        <label for="n<?= $i ?>"><span class="expand">Далее</span><span class="constrict">Свернуть</span></label>
        <div>
            <?= $restText ?>
            <h5>Источник: <a target='_blank' href='<?= $article->getSource()->getSiteURL() ?>'>
                    <?= $article->getSource()->getName() ?></a></h5>
        </div>
    </div>
