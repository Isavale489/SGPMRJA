<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }

    public function getUsers()
    {
        $users = User::all();
        return DataTables::of($users)->make(true);
    }

    private function handleFileUpload($file, $oldPath, $directory)
    {
        // Delete old file if exists
        if ($oldPath && file_exists(public_path($oldPath))) {
            unlink(public_path($oldPath));
        }

        // Upload new file
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($directory), $filename);
        return $directory . '/' . $filename;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:user',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role' => 'required|in:Administrador,Supervisor',
            'estado' => 'nullable|boolean',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->estado = $request->estado;

        // Manejar la subida del avatar
        if ($request->hasFile('avatar')) {
            $user->avatar = $this->handleFileUpload(
                $request->file('avatar'),
                null,
                'avatars'
            );
        }



        $user->save();

        return response()->json(['success' => 'User created successfully.']);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:user,email,' . $id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role' => 'required|in:Administrador,Supervisor',
            'estado' => 'nullable|boolean',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->estado = $request->estado;

        // Manejar la subida del avatar
        if ($request->hasFile('avatar')) {
            $user->avatar = $this->handleFileUpload(
                $request->file('avatar'),
                $user->avatar,
                'avatars'
            );
        }



        $user->save();

        return response()->json(['success' => 'User updated successfully.']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->avatar && file_exists(public_path($user->avatar))) {
            unlink(public_path($user->avatar));
        }
        $user->delete();
        return response()->json(['success' => 'User deleted successfully.']);
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar ? asset($user->avatar) : null,

            'created_at' => $user->created_at->format('d/m/Y H:i:s'),
            'updated_at' => $user->updated_at->format('d/m/Y H:i:s')
        ]);
    }

    public function reportePdf()
    {
        $users = User::all();
        $pdf = \PDF::loadView('admin.users.reporte_pdf', compact('users'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('usuarios_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }
}

