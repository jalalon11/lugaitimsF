<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Position;
use Yajra\Datatables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PDO;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('purchaser.home');
    }
    public function adminprofile()
    {
        $profile = $this->get_allUserInfo(Auth::user()->id);
        return view('pages.adminprofile', compact('profile'));
    }
    public function admin_changePass(Request $request)
    {
        $request->validate([
            'username'=>'required|min:6',
            'currpwd'=>'required|min:6',
            'newpwd'=>'required|min:6',
            'conpwd'=>'required|min:6',
        ]);

        $user = $this->get_allUserInfo(Auth::user()->id);

        if(Hash::check($request->currpwd, $user[0]->password))
        {
            if($request->newpwd === $request->conpwd)
            {
                $user = User::find(Auth::user()->id);
                $user->username = strtoupper($request->username);
                $user->password = Hash::make($request->conpwd);
                $user->update();
                return back()->with('success', 'Login credentials has been successfully updated!');
            }
            else{
                return back()->with('PasswordError', 'Password Does Not Match!');
            }
        }
        else return back()->with('PasswordError', 'Current Password Does Not Exists!');
    }
    public function userprofile()
    {
        $profile = $this->get_allUserInfo(Auth::user()->id);
        return view('purchaser.userprofile', compact('profile'));
    }
    public function user_changePass(Request $request)
    {
        $request->validate([
            'username'=>'required|min:6',
            'currpwd'=>'required|min:6',
            'newpwd'=>'required|min:6',
            'conpwd'=>'required|min:6',
        ]);

        $user = $this->get_allUserInfo(Auth::user()->id);

        if(Hash::check($request->currpwd, $user[0]->password))
        {
            if($request->newpwd === $request->conpwd)
            {
                $user = User::find(Auth::user()->id);
                $user->username = strtoupper($request->username);
                $user->password = Hash::make($request->conpwd);
                $user->update();
                return back()->with('success', 'Login credentials has been successfully updated!');
            }
            else{
                return back()->with('PasswordError', 'Password Does Not Match!');
            }
        }
        else return back()->with('PasswordError', 'Current Password Does Not Exists!');
    }
    public function get_allDataInDatatables($department_id)
    {
        return DataTables::of($this->get_allUsersData($department_id))
                ->addColumn('actions', function($row){
                    $html = "<td align='center'>";
                    $html = '<button type = "button" data-id = '.$row->purchaser_id.' class = "btn  btn-secondary btn-sm edit"><i class = "fas fa-edit"></i></button>';
                    // $html .= '<button type = "button" data-id = '.$row->purchaser_id.' class = "btn btn-danger btn-sm delete"><i class = "fas fa-trash"></i></button>';
                    $html .= "</td>";
                    return $html;
                })
                ->addColumn('position', function($row){
                    return $row->position;
                })
                ->rawColumns(['actions', 'position'])
                ->make(true);
    }
    public function get_allUsers()
    {
        return User::all();
    }

    public function get_allUsersData($department_id)
    {
        $sql = DB::select('select users.*, users.id as purchaser_id, positions.*, departments.*
                        from positions, departments, users
                        where departments.id = users.department_id
                        and positions.id = users.position_id
                        and departments.id = '.$department_id.'');
        return $sql;
    }
    public function get_allUserInfo($user_id)
    {
        $sql = DB::select('select users.*, users.id as purchaser_id, positions.*, departments.*
                        from positions, departments, users
                        where departments.id = users.department_id
                        and positions.id = users.position_id
                        and users.id = '.$user_id.'');
        return $sql;
    }
    public function get_allRequisitioning()
    {
        $sql = DB::select('select users.*, users.id as purchaser_id, positions.*, departments.*
                    from positions, departments, users
                    where departments.id = users.department_id
                    and positions.id = users.position_id');
        return $sql;
    }
    public function get_allRequisitioningByJson()
    {
        return response()->json($this->get_allRequisitioning());
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug: Log the incoming request data
        Log::info('User creation request:', $request->all());

        $validatedData = [];
        $data = [];

        if ($request->purchaser_id !== null && $request->purchaser_id !== '') {
            $validatedData = [
                'fullname' => 'required|unique:users,fullname,' . $request->purchaser_id . ',id',
                'email' => 'required|email|unique:users,email,' . $request->purchaser_id . ',id',
                'position' => 'required|exists:positions,id',
                'contact_number' => 'required|min:10',
                '_departmentID' => 'required|exists:departments,id',
            ];
        } else {
            $validatedData = [
                'fullname' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'position' => 'required|exists:positions,id',
                'contact_number' => 'required|min:10',
                '_departmentID' => 'required|exists:departments,id',
            ];
        }

        $validator = Validator::make($request->all(), $validatedData);
        $status = false;
        $msg = "";

        if ($validator->fails()) {
            Log::error('User validation failed:', $validator->errors()->toArray());
            $msg = $validator->messages();
        } else {
            try {
                $position_id = (int) $request->position;
                // Determine the role based on the position
                $role = $position_id;

                if ($request->purchaser_id && $request->purchaser_id !== '') {
                    // Update existing user
                    $user = User::find($request->purchaser_id);
                    if ($user) {
                        $user->update([
                            'fullname' => strtoupper($request->fullname),
                            'position_id' => $position_id,
                            'department_id' => $request->_departmentID,
                            'role' => $role,
                            'email' => $request->email,
                            'contact_number' => $request->contact_number,
                            'username' => $request->email,
                            'password' => Hash::make('password'),
                        ]);
                    }
                } else {
                    // Create new user
                    $user = User::create([
                        'fullname' => strtoupper($request->fullname),
                        'position_id' => $position_id,
                        'department_id' => $request->_departmentID,
                        'role' => $role,
                        'email' => $request->email,
                        'contact_number' => $request->contact_number,
                        'username' => $request->email,
                        'password' => Hash::make('password'),
                    ]);
                }

                Log::info('User created successfully:', ['user_id' => $user->id]);
                $status = true;
                $msg = "User has been successfully saved!";
            } catch (\Exception $e) {
                Log::error('User creation failed:', ['error' => $e->getMessage()]);
                $msg = "Failed to create user: " . $e->getMessage();
            }
        }

        return response()->json([
            'status' => $status,
            'messages' => $msg,
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return response()->json($this->get_allUserInfo($user->id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
