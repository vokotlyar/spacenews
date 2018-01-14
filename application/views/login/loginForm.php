<div id="login-form">
    <form action="/admin/login<?= $this->referrer ?>" method="post">
        <h1>Авторизация</h1>
        <p>
            <label for="login">Логин:</label>
            <input type="text" maxlength="20" size="25" name="login" id="login" value="<?= $this->login ?>">
        </p>
        <p>
            <label for="password">Пароль:</label>
            <input type="password" size="25" name="password" id="password">
        </p>
        <p>
<!--            <label><input type="checkbox" name="remember" value="read"><span> Запомнить меня</span></label>-->
            <input type="submit" name="submit" style="height:35px" value="Войти">
        </p>

        <?= $this->message ?>

        <div class="clear"></div>
    </form>
</div>