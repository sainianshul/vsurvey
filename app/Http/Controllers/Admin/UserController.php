<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Users\UserDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(UserDataTable $dataTable)
    {
        return $dataTable->render('admin.users.index');
    }

    public function data(UserDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        
        $data['role'] = User::ROLE_USER;
        $data['created_by'] = auth()->id() ?? User::CREATED_BY_ADMIN;
        
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Optional: If no password is provided, generate a random one
            $data['password'] = Hash::make(Str::random(16));
        }

        User::create($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        abort_unless($user->isUser(), 404);
        
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        abort_unless($user->isUser(), 404);

        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        abort_unless($user->isUser(), 404);

        $user->update($request->validated());

        return redirect()->back()
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        abort_unless($user->isUser(), 404);

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.'
        ]);
    }

    public function updateStatus(Request $request, User $user)
    {
        abort_unless($user->isUser(), 404);

        $request->validate([
            'status' => 'required|integer|in:' . implode(',', array_keys(User::getStatusList())),
        ]);

        $user->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }

    public function blocked(\App\DataTables\Users\BlockedUserDataTable $dataTable)
    {
        return $dataTable->render('admin.users.blocked');
    }

    public function blockedData(\App\DataTables\Users\BlockedUserDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function unblock(User $user)
    {
        abort_unless($user->isUser(), 404);

        $user->update([
            'status' => User::STATUS_ACTIVE,
            'blocked_reason' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User unblocked successfully.'
        ]);
    }

    public function deleted(\App\DataTables\Users\DeletedUserDataTable $dataTable)
    {
        return $dataTable->render('admin.users.deleted');
    }

    public function deletedData(\App\DataTables\Users\DeletedUserDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        abort_unless($user->isUser(), 404);

        $user->restore();

        return response()->json([
            'success' => true,
            'message' => 'User restored successfully.'
        ]);
    }

    public function revokeToken(Request $request, User $user)
    {
        $request->validate([
            'token_id' => 'required|integer',
        ]);

        $token = $user->tokens()->where('id', $request->token_id)->first();

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Token not found.'], 404);
        }

        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device session revoked successfully.'
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->input('q');
        
        $query = clone (new User())->users();
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->limit(50)->get();
        
        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id' => $user->id,
                'text' => ($user->name ?? 'Unknown') . ' (' . ($user->phone ?? 'N/A') . ')'
            ];
        }
        
        return response()->json([
            'results' => $results
        ]);
    }
}
