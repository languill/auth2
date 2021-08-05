<?php
namespace App\controllers;

use App\QueryBuilder;
use App\Helpers;
use League\Plates\Engine;
use PDO;
use Delight\Auth\Auth;
use \Tamtamchik\SimpleFlash\Flash;


class HomeController {

    private $templates;    
    private $qb;
    private $auth;
    private $flash;

    public function __construct(QueryBuilder $qb, Engine $engine, Auth $auth, Flash $flash) {
        $this->qb = $qb;
        $this->templates = $engine; 
        $this->auth = $auth;
        $this->flash = $flash;
    }

    public function index() {  
        echo $this->templates->render('homepage', ['flash' => $this->flash->display()]);    
    }

    public function registration() {
        echo $this->templates->render('registration', ['flash' => $this->flash->display()]); 
    }

    public function registrationForm() {
        try {
            $userId = $this->auth->register(Helpers::text_validate($_POST['email']), Helpers::text_validate($_POST['password']));

            $this->flash->success('Мы зарегистрировали нового пользователя с ID  ' . $userId);
            Helpers::redirect_to('/login');            
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            $this->flash->error('Неверный адрес электронной почты');
            Helpers::redirect_to('/registration');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            $this->flash->error('Неправильный пароль');
            Helpers::redirect_to('/registration');
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {            
            $this->flash->error('Пользователь уже существует');
            Helpers::redirect_to('/registration');
            
        }
        catch (\Delight\Auth\TooManyRequestsException $e) {
            $this->flash->error('Слишком много запросов');
            Helpers::redirect_to('/registration');
        }

        echo $this->templates->render('registration', ['flash' => $this->flash->display()]);
    }

    public function login() {
        echo $this->templates->render('login', ['flash' => $this->flash->display()]); 
    }

    public function loginForm() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {	
            try {
                $this->auth->login(Helpers::text_validate($_POST['email']), Helpers::text_validate($_POST['password']));                
                $this->flash->success('Пользователь авторизован ');
                Helpers::redirect_to('/users');
            }
            catch (\Delight\Auth\InvalidEmailException $e) {
                $this->flash->error('Неправильный адрес электронной почты');
                Helpers::redirect_to('/login');                
            }
            catch (\Delight\Auth\InvalidPasswordException $e) {
                $this->flash->error('Неправильный пароль');
                Helpers::redirect_to('/login'); 
            }
            catch (\Delight\Auth\EmailNotVerifiedException $e) {
                $this->flash->error('Электронная почта не подтверждена');
                Helpers::redirect_to('/login');                 
            }
            catch (\Delight\Auth\TooManyRequestsException $e) {
                $this->flash->error('Слишком много запросов');
                Helpers::redirect_to('/login');                 
            }
        }
        echo $this->templates->render('login', ['flash' => $this->flash->display()]); 
    }

    public function logout() {
        $this->auth->logOut(); 
        Helpers::redirect_to('/login');  
    }
}