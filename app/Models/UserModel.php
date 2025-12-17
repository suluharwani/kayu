<?php namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'username', 'password', 'nama_lengkap', 'role', 'last_login', 'status'];
    protected $useTimestamps = true;
    
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];
    
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['data']['password']);
        }
        return $data;
    }
    
    public function getUserByUsername($username)
    {
        return $this->where('username', $username)->first();
    }
    
    public function validateRegistration($data)
    {
        $this->validation->setRules([
            'username' => 'required|min_length[3]|max_length[20]|is_unique[users.username]',
            'password' => 'required|min_length[6]|max_length[255]',
            'pass_confirm' => 'matches[password]',
            'nama_lengkap' => 'required|max_length[100]'
        ]);
        
        return $this->validation->run($data);
    }
    
    // Method untuk mengubah status pengguna
    public function updateStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }
}