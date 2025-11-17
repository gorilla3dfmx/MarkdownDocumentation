<?php
/**
 * Authentication Controller
 */
class AuthController {

    public function showLogin($params = []) {
        if (Auth::isAuthenticated()) {
            Url::redirect('/');
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        return View::render('login', [
            'title' => 'Login - ' . SITE_TITLE,
            'error' => $error
        ]);
    }

    public function login($params = []) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (Auth::login($username, $password)) {
            $redirect = $_POST['redirect'] ?? '/';
            Url::redirect($redirect);
        } else {
            $_SESSION['login_error'] = 'Invalid username or password';
            Url::redirect('/login');
        }
    }

    public function logout($params = []) {
        Auth::logout();
        Url::redirect('/');
    }
}
