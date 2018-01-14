<div id="adduser-form">
    <form action="/admin/adduser" method="post">
        <h2>Регистрация</h2>
        <p>
            <label for="login">Логин:</label>
            <input type="text" maxlength="20" size="25" name="login" id="login">
        </p>
        <p>
            <label for="password">Пароль:</label>
            <input type="password" size="25" name="password" id="password">
        </p>
        <p>
            <label for="confirm-password">Подтвердить пароль:</label>
            <input type="password" size="25" name="confirm-password" id="confirm-password">
        </p>
        <p>
            <input type="submit" name="submit" style="height:35px" value="Зарегистрировать">
        </p>
        <?= $this->messageAdd ?>
        <div class="clear"></div>
    </form>
</div>