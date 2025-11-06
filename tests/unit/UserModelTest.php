<?php

namespace Tests\Unit;

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class UserModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = 'App';

    protected UserModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new UserModel();
    }

    public function testCreateUser()
    {
        $data = [
            'email'    => 'test@example.com',
            'password' => 'TestPass123',
            'role'     => 'user',
        ];

        $id = $this->model->insert($data);
        $this->assertIsNumeric($id);

        $user = $this->model->find($id);
        $this->assertEquals('test@example.com', $user['email']);
        $this->assertEquals('user', $user['role']);
        $this->assertNotEquals('TestPass123', $user['password']); // Should be hashed
    }

    public function testPasswordIsHashed()
    {
        $data = [
            'email'    => 'hash@test.com',
            'password' => 'MyPassword123',
            'role'     => 'user',
        ];

        $id = $this->model->insert($data);
        $user = $this->model->find($id);

        $this->assertTrue(password_verify('MyPassword123', $user['password']));
    }

    public function testVerifyPassword()
    {
        $data = [
            'email'    => 'verify@test.com',
            'password' => 'VerifyPass123',
            'role'     => 'user',
        ];

        $id = $this->model->insert($data);
        $user = $this->model->find($id);

        $this->assertTrue($this->model->verifyPassword('VerifyPass123', $user['password']));
        $this->assertFalse($this->model->verifyPassword('WrongPassword', $user['password']));
    }

    public function testEmailValidation()
    {
        $data = [
            'email'    => 'invalid-email',
            'password' => 'TestPass123',
            'role'     => 'user',
        ];

        $result = $this->model->insert($data);
        $this->assertFalse($result);
    }

    public function testUniqueEmail()
    {
        $data = [
            'email'    => 'unique@test.com',
            'password' => 'TestPass123',
            'role'     => 'user',
        ];

        $this->model->insert($data);
        
        // Try to insert duplicate email
        $result = $this->model->insert($data);
        $this->assertFalse($result);
    }

    public function testRoleValidation()
    {
        $validRoles = ['admin', 'user'];
        
        foreach ($validRoles as $role) {
            $data = [
                'email'    => "test_{$role}@example.com",
                'password' => 'TestPass123',
                'role'     => $role,
            ];

            $id = $this->model->insert($data);
            $this->assertIsNumeric($id);
            
            $user = $this->model->find($id);
            $this->assertEquals($role, $user['role']);
        }
    }

    public function testFindByEmail()
    {
        $data = [
            'email'    => 'findme@test.com',
            'password' => 'TestPass123',
            'role'     => 'user',
        ];

        $this->model->insert($data);
        $user = $this->model->where('email', 'findme@test.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('findme@test.com', $user['email']);
    }

    public function testUpdateUser()
    {
        $data = [
            'email'    => 'update@test.com',
            'password' => 'TestPass123',
            'role'     => 'user',
        ];

        $id = $this->model->insert($data);
        
        $updateData = [
            'role' => 'admin',
        ];

        $this->model->update($id, $updateData);
        $user = $this->model->find($id);

        $this->assertEquals('admin', $user['role']);
    }

    public function testDeleteUser()
    {
        $data = [
            'email'    => 'delete@test.com',
            'password' => 'TestPass123',
            'role'     => 'user',
        ];

        $id = $this->model->insert($data);
        $this->model->delete($id);
        
        $user = $this->model->find($id);
        $this->assertNull($user);
    }
}
