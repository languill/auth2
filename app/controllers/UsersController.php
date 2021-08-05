<?php
namespace App\controllers;

use App\QueryBuilder;
use App\Helpers;
use League\Plates\Engine;
use PDO;
use Delight\Auth\Auth;
use \Tamtamchik\SimpleFlash\Flash;

class UsersController {
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

    public function index() 
    {
        if($this->auth->isLoggedIn()) { 
            $admin = Helpers::canAddUser($this->auth, \Delight\Auth\Role::ADMIN);
            $currentUser = $this->auth->getUserId();            
            $users = $this->qb->getAll('users'); 
            echo $this->templates->render('users', ['flash' => $this->flash->display(), 'users' => $users, 'admin' => $admin, 'currentUser' => $currentUser]);  
        } else {
            Helpers::redirect_to('/login');  
        }
         
    }

    public function addUser()
    {
        $admin = Helpers::canAddUser($this->auth, \Delight\Auth\Role::ADMIN);
        if($this->auth->isLoggedIn() && $admin) {            
            echo $this->templates->render('addUser', ['flash' => $this->flash->display(), 'admin' => $admin]); 
        } else {
            Helpers::redirect_to('/login');  
        }  
    }

    public function addUserForm() 
    {   
        try {           
            
            $userId = $this->auth->admin()->createUser($_POST['email'], $_POST['password'], $_POST['username']);

            $avatar = Helpers::upload_file($_FILES['avatar']['name'], $_FILES['avatar']['tmp_name']);		
		
            if(empty($_FILES['avatar']['tmp_name'])) {
                $avatar = NULL;
            }

            $this->qb->update([
                'job_title' => Helpers::text_validate($_POST['job_title']),
                'phone' => Helpers::text_validate($_POST['phone']),
                'address' => Helpers::text_validate($_POST['address']), 
                'online_status' => Helpers::text_validate($_POST['online_status']),
                'avatar' => $avatar,	
                'vk' => Helpers::text_validate($_POST['vk']),
                'telegram' => Helpers::text_validate($_POST['telegram']),
                'instagram' =>Helpers::text_validate($_POST['instagram']),
            ], $userId, 'users');

            $this->flash->success('Мы зарегистрировали нового пользователя с ID  ' . $userId);
            Helpers::redirect_to('/users');  
            
        }
        catch (\Delight\Auth\InvalidEmailException $e) {
            $this->flash->error('Неверный адрес электронной почты');
            Helpers::redirect_to('/addUser');
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            $this->flash->error('Неправильный пароль');
            Helpers::redirect_to('/addUser');
        }
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            $this->flash->error('Пользователь уже существует');
            Helpers::redirect_to('/addUser');
        }

        echo $this->templates->render('addUser', ['flash' => $this->flash->display()]);
        
    }

    

    public function edit($id)
    {
        if($this->auth->isLoggedIn()) {   
            $user = $this->qb->getOne('users', $id); 
            echo $this->templates->render('edit', ['flash' => $this->flash->display(), 'user' => $user]); 
        } else {
            Helpers::redirect_to('/login');  
        } 
    }

    public function editForm()
    {        
        $this->qb->update([
            'username' => Helpers::text_validate($_POST['username']),
            'job_title' => Helpers::text_validate($_POST['job_title']),
            'phone' => Helpers::text_validate($_POST['phone']),
            'address' => Helpers::text_validate($_POST['address']), 
                
        ], $_POST['id'], 'users');

        $this->flash->success('Данные о пользователе обновлены');
        Helpers::redirect_to('/users'); 
    }

    public function security($id) 
    {
        if($this->auth->isLoggedIn()) {   
            $user = $this->qb->getOne('users', $id); 
            echo $this->templates->render('security', ['flash' => $this->flash->display(), 'user' => $user]); 
        } else {
            Helpers::redirect_to('/login');  
        } 
    }

    public function securityForm()
    {
        if($_POST['password'] != $_POST['retype_password']) {
            $this->flash->error('Пароли не совпадают');
            Helpers::redirect_to("/security/{$_POST['id']}");  
        }

        try {
            $this->auth->admin()->changePasswordForUserById($_POST['id'], $_POST['password']);
        }
        catch (\Delight\Auth\UnknownIdException $e) {            
            $this->flash->error('Неизвестный ID');
            Helpers::redirect_to("/security/{$_POST['id']}");
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            $this->flash->error('Неправильный пароль ');
            Helpers::redirect_to("/security/{$_POST['id']}");
        }

        $this->qb->update([
            'email' => Helpers::text_validate($_POST['email']),               
        ], $_POST['id'], 'users');

        $this->flash->success('Данные о пользователе обновлены');
        Helpers::redirect_to('/users'); 
    }

    public function status($id)
    {
        if($this->auth->isLoggedIn()) {   
            $user = $this->qb->getOne('users', $id); 
            $statuses = [
                'online' => 'Онлайн',
                'away' => 'Отошел',
                'dontbother' => 'Не беспокоить',
            ];
            echo $this->templates->render('status', ['flash' => $this->flash->display(), 'user' => $user, 'statuses' => $statuses]); 
        } else {
            Helpers::redirect_to('/login');  
        } 
    }

    public function statusForm()
    {        
        $this->qb->update([
            'online_status' => Helpers::text_validate($_POST['online_status']),
        ], $_POST['id'], 'users');

        $this->flash->success('Данные о пользователе обновлены');
        Helpers::redirect_to('/users'); 
    }

    public function media($id)
    {
        if($this->auth->isLoggedIn()) {   
            $user = $this->qb->getOne('users', $id); 
            echo $this->templates->render('media', ['flash' => $this->flash->display(), 'user' => $user]); 
        } else {
            Helpers::redirect_to('/login');  
        } 
    }

    public function mediaForm()
    {
        if(empty($_FILES['avatar']['tmp_name'])) {
            $this->flash->error('Загрузите аватар');
            Helpers::redirect_to("/media/{$_POST['id']}");  
        } else {
            $avatar = Helpers::upload_file($_FILES['avatar']['name'], $_FILES['avatar']['tmp_name']);

            $this->qb->update([                
                'avatar' => $avatar,
            ], $_POST['id'], 'users');

            $this->flash->success('Данные о пользователе обновлены');
            Helpers::redirect_to('/users');
        }         
    }

    public function delete($id)
    {
        $user = $this->qb->getOne('users', $id); 
        $image = $user['avatar'];
	    $path = "images/" . $image;
        Helpers::delete_image($image, $path);

        $admin = Helpers::canAddUser($this->auth, \Delight\Auth\Role::ADMIN);
        if($admin) {
            try {
                $this->auth->admin()->deleteUserById($id);
                $this->flash->success('Пользователь удален');
                Helpers::redirect_to('/users'); 
            }
            catch (\Delight\Auth\UnknownIdException $e) {            
                $this->flash->error('Неизвестный ID');
                Helpers::redirect_to('/users'); 
            }     
        } else {
            try {
                $this->auth->admin()->deleteUserById($id);
                $this->auth->logOut(); 
                Helpers::redirect_to('/login');
            }
            catch (\Delight\Auth\UnknownIdException $e) {            
                $this->flash->error('Неизвестный ID');
                Helpers::redirect_to('/users'); 
            }
        }
        
    }
    
}

