<tr>
    <td class="login"><?= $login ?></td>
    <td><?= date("Y-m-d H:i:s", $time) ?></td>
    <td class="delete-user">
        <a href="/admin/deleteuser/login/<?= $login ?>">
            <img src="/images/delete-button.png" height="30" alt="Удалить">
        </a>
    </td>
</tr>