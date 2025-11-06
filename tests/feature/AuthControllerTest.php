<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

class AuthControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = 'App';
    protected $seed        = 'Tests\Support\Database\Seeds\TestSeeder';

    public function testLoginPageDisplays()
    {
        $result = $this->get('/auth/login');
        
        $result->assertStatus(200);
        $result->assertSee('Masuk');
        $result->assertSee('Email');
        $result->assertSee('Password');
    }

    public function testRegisterPageDisplays()
    {
        $result = $this->get('/auth/register');
        
        $result->assertStatus(200);
        $result->assertSee('Daftar');
        $result->assertSee('Email');
        $result->assertSee('Password');
    }

    public function testSuccessfulLogin()
    {
        $result = $this->post('/auth/login', [
            'email'    => 'admin@test.com',
            'password' => 'Admin123',
        ]);

        $result->assertRedirectTo('/dashboard');
        $this->assertTrue(session()->has('user_id'));
    }

    public function testLoginWithInvalidCredentials()
    {
        $result = $this->post('/auth/login', [
            'email'    => 'admin@test.com',
            'password' => 'WrongPassword',
        ]);

        $result->assertRedirectTo('/auth/login');
        $this->assertFalse(session()->has('user_id'));
    }

    public function testLoginWithNonExistentEmail()
    {
        $result = $this->post('/auth/login', [
            'email'    => 'notexist@test.com',
            'password' => 'SomePassword123',
        ]);

        $result->assertRedirectTo('/auth/login');
        $this->assertFalse(session()->has('user_id'));
    }

    public function testSuccessfulRegistration()
    {
        $result = $this->post('/auth/register', [
            'email'    => 'newuser@test.com',
            'password' => 'NewPass123',
        ]);

        $result->assertRedirectTo('/auth/login');
        
        // Verify user was created
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', 'newuser@test.com')->first();
        $this->assertNotNull($user);
    }

    public function testRegistrationWithWeakPassword()
    {
        $result = $this->post('/auth/register', [
            'email'    => 'weak@test.com',
            'password' => 'weak',
        ]);

        $result->assertRedirectTo('/auth/register');
    }

    public function testRegistrationWithInvalidEmail()
    {
        $result = $this->post('/auth/register', [
            'email'    => 'invalid-email',
            'password' => 'ValidPass123',
        ]);

        $result->assertRedirectTo('/auth/register');
    }

    public function testRegistrationWithDuplicateEmail()
    {
        $result = $this->post('/auth/register', [
            'email'    => 'admin@test.com', // Already exists in seed
            'password' => 'ValidPass123',
        ]);

        $result->assertRedirectTo('/auth/register');
    }

    public function testLogout()
    {
        // Login first
        $_SESSION['user_id'] = 1;
        $_SESSION['email'] = 'admin@test.com';
        $_SESSION['role'] = 'admin';

        $result = $this->get('/auth/logout');
        
        $result->assertRedirectTo('/auth/login');
        $this->assertFalse(session()->has('user_id'));
    }

    public function testGuestCannotAccessDashboard()
    {
        $result = $this->get('/dashboard');
        
        $result->assertRedirectTo('/auth/login');
    }

    public function testAuthenticatedUserCanAccessDashboard()
    {
        // Set session as logged in user
        $_SESSION['user_id'] = 1;
        $_SESSION['email'] = 'admin@test.com';
        $_SESSION['role'] = 'admin';

        $result = $this->get('/dashboard');
        
        $result->assertStatus(200);
    }
}
