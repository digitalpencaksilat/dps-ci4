<?php

namespace App\Controllers\Development;

use App\Controllers\BaseController;

class AdminUtilityController extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $admins = $db->table('admin')->get()->getResult();

        return view('development/admin_utility', [
            'title' => 'Admin Utility',
            'admins' => $admins,
        ]);
    }

    public function updatePassword()
    {
        $idAdmin = $this->request->getPost('id_admin');
        $newPassword = $this->request->getPost('new_password');

        if (empty($idAdmin) || empty($newPassword)) {
            session()->setFlashdata('error', 'All fields are required.');
            return redirect()->to(base_url('development/admin-utility'));
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $db = db_connect();
        $db->table('admin')->where('id_admin', $idAdmin)->update(['password' => $hashedPassword]);

        if ($db->affectedRows() > 0) {
            session()->setFlashdata('success', 'Password updated successfully using BCrypt.');
        } else {
            session()->setFlashdata('error', 'No changes made or admin not found.');
        }

        return redirect()->to(base_url('development/admin-utility'));
    }
}
