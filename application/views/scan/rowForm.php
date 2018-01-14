<tr>
    <td class="first-column">
        <label>
            <input name="source[]" value="<?= $name ?>" type="checkbox" <?= $checkboxStatus ?>>
            <img title='<?= $name ?>' src='<?= $iconPath ?>' height='24'>
        </label>
    </td>
    <td class="link-column"><a target='_blank' href='<?= $url ?>'><?= $showUrl ?></a></td>
    <td class="qty-column"><?= $newsQty ?></td>
    <?= $addedNewsRowValue ?>
</tr>