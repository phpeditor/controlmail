<?php
include_once 'config.php';
session_start();
$mysqli = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
if ($mysqli->connect_errno) {
    printf("Error connection database: %s\n", $mysqli->connect_error);
    exit();
}
if(isset($_SESSION['controlmail_user']) && $_SESSION['controlmail_user'])
    $controlmail_user = $_SESSION['controlmail_user'];
else
    $controlmail_user = NULL;
if(isset($_SESSION['controlmail_password']) && $_SESSION['controlmail_password'])
    $controlmail_password = $_SESSION['controlmail_password'];
else
    $controlmail_password = NULL;
$auth_query = mysqli_query($mysqli, "SELECT * FROM `users` WHERE `email` = '".mysqli_escape_string($mysqli, $controlmail_user)."' AND `password` = '".mysqli_escape_string($mysqli, $controlmail_password)."'");
if(mysqli_num_rows($auth_query))
{
    $user = mysqli_fetch_assoc($auth_query);
    if(isset($_GET['logout']))
    {
        unset($_SESSION['controlmail_user']);
        unset($_SESSION['controlmail_password']);
        header('Location: /controlmail/');
    }
    elseif(isset($_GET['delete']) && $_GET['delete'] && $_GET['delete']!='support')
    {
        mysqli_query($mysqli, "DELETE FROM `users` WHERE `user` = '".mysqli_escape_string($mysqli, $_GET['delete'])."'");
        $_SESSION['controlmail_message'] = 'E-mail успешно удален!';
        header('Location: /controlmail/');
    }
    elseif(isset($_GET['edit']) && $_GET['edit'])
    {
        $mail_user = $_GET['edit'];
        $mail_data = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT * FROM `users` WHERE `user` = '".mysqli_escape_string($mysqli, $mail_user)."'"));
        $error = false;
        if(isset($_POST['mail_password']) && isset($_POST['mail_repassword']))
        {
            $mail_password = $_POST['mail_password'];
            $mail_repassword = $_POST['mail_repassword'];
            if(strlen($mail_password)<6)
            {
                $error[] = 'Пароль не может быть меньше 6 символов!';
            }
            if($mail_password!=$mail_repassword)
            {
                $error[] = 'Пароли не совпадают!';
            }
            if(!$error)
            {
                mysqli_query($mysqli, "UPDATE `users` SET `password` = ENCRYPT('".mysqli_escape_string($mysqli, $mail_password)."', 'key') WHERE `user` = '".mysqli_escape_string($mysqli, $mail_user)."'");
                $_SESSION['controlmail_message'] = 'E-mail успешно изменен!';
                if($mail_user=='support')
                {
                    $_SESSION['controlmail_user'] = $mail_data['email'];
                    $_SESSION['controlmail_password'] = $mail_data['password'];
                }
                header('Location: /controlmail/');
            }
        }
        include_once 'header.php';
        echo '<div class="section">';
        echo '<div class="container">';
        echo '<div class="top">';
        echo '<div class="top-left">';
        echo '<div class="top-left-title">'.htmlspecialchars($controlmail_user).'</div>';
        echo '<div class="top-left-logout"><a href="/controlmail/?logout"><img src="/controlmail/image/logout.png" alt="Выйти" title="Выйти"></a></div>';
        echo '</div>';
        echo '<div class="top-right">';
        echo '<a class="button-back" href="/controlmail/"><img src="/controlmail/image/back.png" alt="Назад" title="Назад"></a>';
        echo '</div>';
        echo '</div>';
        if($error)
        {
            foreach($error as $err)
                echo '<div class="error">'.htmlspecialchars($err).'</div>';
        }
        echo '<form action="/controlmail/?edit='.htmlspecialchars($mail_user).'" method="POST" enctype="multipart/form-data">';
        echo '<div class="form-title">Редактирровать '.htmlspecialchars($mail_data['email']).'</div>';
        echo '<div class="form-input"><input name="mail_password" type="password" placeholder="Пароль"></div>';
        echo '<div class="form-input"><input name="mail_repassword" type="password" placeholder="Повтор пароля"></div>';
        echo '<div class="form-submit"><input type="submit" value="Сохранить"></div>';
        echo '</form>';
        echo '<div class="copyright">Controlmail</div>';
        echo '</div>';
        echo '</div>';
        include_once 'footer.php';
    }
    elseif(isset($_GET['add']))
    {
        $error = false;
        if(isset($_POST['mail_user']) && isset($_POST['mail_password']) && isset($_POST['mail_repassword']))
        {
            $mail_user = $_POST['mail_user'];
            $mail_password = $_POST['mail_password'];
            $mail_repassword = $_POST['mail_repassword'];
            if(!$mail_user)
            {
                $error[] = 'Имя пользователя не может быть пустым!';
            }
            if(preg_match('|@|isU',$mail_user))
            {
                $error[] = 'Неправильный формат имени пользователя!';
            }
            if(mysqli_num_rows(mysqli_query($mysqli, "SELECT * FROM `users` WHERE `user` = '".mysqli_escape_string($mysqli, $mail_user)."'")))
            {
                $error[] = 'Это имя пользователя уже занято!';
            }
            if(strlen($mail_password)<6)
            {
                $error[] = 'Пароль не может быть меньше 6 символов!';
            }
            if($mail_password!=$mail_repassword)
            {
                $error[] = 'Пароли не совпадают!';
            }
            if(!$error)
            {
                mysqli_query($mysqli, "INSERT INTO `users` (`user`, `email`, `password`) VALUES ('".mysqli_escape_string($mysqli, $mail_user)."', '".mysqli_escape_string($mysqli, $mail_user.'@'.DOMAIN)."', ENCRYPT('".mysqli_escape_string($mysqli, $mail_password)."', 'key'))");
                $_SESSION['controlmail_message'] = 'E-mail успешно создан!';
                header('Location: /controlmail/');
            }
        }
        include_once 'header.php';
            echo '<div class="section">';
                echo '<div class="container">';
                    echo '<div class="top">';
                        echo '<div class="top-left">';
                            echo '<div class="top-left-title">'.htmlspecialchars($controlmail_user).'</div>';
                            echo '<div class="top-left-logout"><a href="/controlmail/?logout"><img src="/controlmail/image/logout.png" alt="Выйти" title="Выйти"></a></div>';
                        echo '</div>';
                        echo '<div class="top-right">';
                            echo '<a class="button-back" href="/controlmail/"><img src="/controlmail/image/back.png" alt="Назад" title="Назад"></a>';
                        echo '</div>';
                    echo '</div>';
                    if($error)
                    {
                        foreach($error as $err)
                            echo '<div class="error">'.htmlspecialchars($err).'</div>';
                    }
                    echo '<form action="/controlmail/?add" method="POST" enctype="multipart/form-data">';
                        echo '<div class="form-title">Добавить E-mail</div>';
                        echo '<div class="form-user"><input name="mail_user" type="text" placeholder="Имя пользователя"><div class="form-user-text">@'.DOMAIN.'</div></div>';
                        echo '<div class="form-input"><input name="mail_password" type="password" placeholder="Пароль"></div>';
                        echo '<div class="form-input"><input name="mail_repassword" type="password" placeholder="Повтор пароля"></div>';
                        echo '<div class="form-submit"><input type="submit" value="Создать"></div>';
                    echo '</form>';
                    echo '<div class="copyright">Controlmail</div>';
                echo '</div>';
            echo '</div>';
        include_once 'footer.php';
    }
    else
    {
        include_once 'header.php';
        echo '<div class="section">';
            echo '<div class="container">';
                echo '<div class="top">';
                    echo '<div class="top-left">';
                        echo '<div class="top-left-title">'.htmlspecialchars($controlmail_user).'</div>';
                        echo '<div class="top-left-logout"><a href="/controlmail/?logout"><img src="/controlmail/image/logout.png" alt="Выйти" title="Выйти"></a></div>';
                    echo '</div>';
                    echo '<div class="top-right">';
                        echo '<a class="button-add" href="/controlmail/?add"><img src="/controlmail/image/add.png" alt="Создать" title="Создать"></a>';
                    echo '</div>';
                echo '</div>';
                if(isset($_SESSION['controlmail_message']))
                {
                    echo '<div class="success">'.htmlspecialchars($_SESSION['controlmail_message']).'</div>';
                    unset($_SESSION['controlmail_message']);
                }
                echo '<div class="emails">';
                $email_query = mysqli_query($mysqli, "SELECT * FROM `users` ORDER BY `email` ASC");
                $i = 0;
                while($email = mysqli_fetch_assoc($email_query))
                {
                    $i++;
                    echo '<div class="emails-inner">';
                        echo '<div class="emails-inner-title">'.$i.'. '.htmlspecialchars($email['email']).'</div>';
                        echo '<div class="emails-inner-action">';
                            echo '<a class="emails-inner-action-edit" href="/controlmail/?edit='.htmlspecialchars($email['user']).'"><img src="/controlmail/image/edit.png" alt="Редактировать" title="Редактировать"></a>';
                            if($email['user']!='support')
                            {
                                echo '<a class="emails-inner-action-delete" onclick="return confirm(\'Вы уверены что хотите удалить этот E-mail '.htmlspecialchars($email['email']).'?\')" href="/controlmail/?delete='.htmlspecialchars($email['user']).'"><img src="/controlmail/image/delete.png" alt="Удалить" title="Удалить"></a>';
                            }
                        echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
                echo '<div class="copyright">Controlmail</div>';
            echo '</div>';
        echo '</div>';
        include_once 'footer.php';
    }
}
else
{
    $errors = false;
    if(isset($_POST['controlmail_user']) && isset($_POST['controlmail_password']))
    {
        $email = $_POST['controlmail_user'];
        $user_data = explode('@', $email);
        $user = reset($user_data);
        $password = $_POST['controlmail_password'];
        $email_query = mysqli_query($mysqli, "SELECT * FROM `users` WHERE `email` = '".mysqli_escape_string($mysqli, $email)."' AND `password` = ENCRYPT('".mysqli_escape_string($mysqli, $password)."', 'key')");
        if($user == 'support' && mysqli_num_rows($email_query))
        {
            $email = mysqli_fetch_assoc($email_query);
            $_SESSION['controlmail_user'] = $email['email'];
            $_SESSION['controlmail_password'] = $email['password'];
            $_SESSION['controlmail_message'] = 'Вы успешно авторизовались!';
            header('Location: /controlmail/');
        }
        else
        {
            $errors = 'Неправильный пароль!';
        }
    }
    include_once 'header.php';
    echo '<div class="auth-container">';
        echo '<div class="auth-logo"><img src="/controlmail/image/logo.png"></div>';
        echo '<form action="/controlmail/" method="POST" enctype="multipart/form-data">';
            if($errors)
                echo '<div class="auth-error">'.$errors.'</div>';
            echo '<div class="auth-input"><input name="controlmail_user" type="text" placeholder="Имя пользователя"></div>';
            echo '<div class="auth-input"><input name="controlmail_password" type="password" placeholder="Пароль"></div>';
            echo '<div class="auth-submit"><input type="submit" value="ВОЙТИ"></div>';
            echo '<div class="auth-text">Controlmail</div>';
        echo '</form>';
    echo '</div>';
    include_once 'footer.php';
}