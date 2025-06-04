<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSetupController extends Controller
{
    public function setupAdmin()
    {
        try {
            // Check if admin already exists
            $adminExists = User::where('role', 1)->exists();

            if ($adminExists) {
                return view('admin-setup', [
                    'status' => 'error',
                    'message' => 'Admin user already exists',
                    'user' => User::where('role', 1)->first()
                ]);
            }

            // Begin transaction
            DB::beginTransaction();

            // Create positions if they don't exist
            $this->createPositionIfNotExists('ADMIN', 1);
            $this->createPositionIfNotExists('TEACHER', 2);
            // $this->createPositionIfNotExists('PROPERTY CUSTODIAN', 3);
            // $this->createPositionIfNotExists('PRINCIPAL', 4);
            // $this->createPositionIfNotExists('STUDENT', 5);

            // Create departments if they don't exist
            $this->createDepartmentIfNotExists('SUPPLY/PROCUREMENT OFFICE', 1);
            $this->createDepartmentIfNotExists('SENIOR HIGH BUILDING', 2);
            $this->createDepartmentIfNotExists('ICT BUILDING', 3);
            $this->createDepartmentIfNotExists('HOME ECONOMICS BUILDING', 4);

            // Create admin user
            $admin = User::create([
                'role' => 1,
                'fullname' => strtoupper('VINCENT JHANREY S. JALALON'),
                'department_id' => 1,
                'position_id' => 1,
                'contact_number' => '+63988765145',
                'username' => 'admin',
                'email' => 'vincentjhanrey.jalalon@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // Default password: password
                'remember_token' => '',
            ]);

            DB::commit();

            return view('admin-setup', [
                'status' => 'success',
                'message' => 'Admin user created successfully',
                'user' => $admin
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return view('admin-setup', [
                'status' => 'error',
                'message' => 'Failed to create admin user: ' . $e->getMessage(),
                'user' => null
            ]);
        }
    }

    private function createPositionIfNotExists($position, $id)
    {
        if (!Position::where('id', $id)->exists()) {
            Position::create([
                'id' => $id,
                'position' => $position
            ]);
        }
    }

    private function createDepartmentIfNotExists($departmentName, $id)
    {
        if (!Department::where('id', $id)->exists()) {
            Department::create([
                'id' => $id,
                'department_name' => $departmentName
            ]);
        }
    }
}
