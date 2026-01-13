<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        // Determine if login is email or NIK
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nis';
        
        if ($loginType === 'nis') {
            // Find student by NIS
            $student = Student::where('nis', $request->login)->first();
            
            if ($student) {
                // Attempt login with user email
                $credentials = [
                    'email' => $student->user->email,
                    'password' => $request->password
                ];
                
                if (Auth::attempt($credentials)) {
                    $request->session()->regenerate();
                    return $this->redirectBasedOnRole(Auth::user());
                }
            }
        } else {
            // Login with email
            $credentials = [
                'email' => $request->login,
                'password' => $request->password
            ];
            
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                return $this->redirectBasedOnRole(Auth::user());
            }
        }

        return back()->withErrors([
            'login' => 'NIS/Email atau password salah.',
        ])->onlyInput('login');
    }

    /**
     * Redirect based on user role
     */
    private function redirectBasedOnRole($user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('dashboard.admin');
        } elseif ($user->role === 'guru') {
            return redirect()->route('dashboard.guru');
        } else {
            return redirect()->route('dashboard.siswa');
        }
    }

    /**
     * Show registration form for admin/guru
     */
    public function showRegisterAdminGuruForm()
    {
        return view('auth.register-admin-guru');
    }

    /**
     * Handle registration request for admin/guru
     */
    public function registerAdminGuru(Request $request)
    {
        // If user is already logged in, redirect to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard.' . Auth::user()->role);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,guru',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Auto login after registration
        Auth::login($user);

        $roleName = $user->role === 'admin' ? 'Admin' : 'Guru';
        
        return redirect()->route('dashboard.' . $user->role)
            ->with('success', "Registrasi berhasil! Akun $roleName '$request->name' telah dibuat.")
            ->with('new_user', true);
    }

    /**
     * Show registration form for siswa
     */
    public function showRegisterSiswaForm()
    {
        return view('auth.register-siswa');
    }

    /**
     * Handle registration request for siswa
     */
    public function registerSiswa(Request $request)
    {
        // If user is already logged in, redirect to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard.' . Auth::user()->role);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'nis' => 'required|string|unique:students,nis',
            'kelas' => 'required|string',
            'nama_ortu' => 'required|string',
            'kontak_ortu' => 'required|string',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah terdaftar.',
            'kelas.required' => 'Kelas wajib diisi.',
            'nama_ortu.required' => 'Nama orang tua wajib diisi.',
            'kontak_ortu.required' => 'Kontak orang tua wajib diisi.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user with siswa role
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
        ]);

        // Create student record
        Student::create([
            'user_id' => $user->id,
            'nis' => $request->nis,
            'kelas' => $request->kelas,
            'nama_ortu' => $request->nama_ortu,
            'kontak_ortu' => $request->kontak_ortu,
        ]);

        // Auto login after registration
        Auth::login($user);
        
        return redirect()->route('dashboard.siswa')
            ->with('success', "Registrasi berhasil! Akun Siswa '$request->name' telah dibuat.")
            ->with('new_user', true);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
