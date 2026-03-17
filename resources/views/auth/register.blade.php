@extends('layouts.app')
@section('title', 'Daftar - Private Chat')

@section('styles')
<style>
    .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-primary);
        position: relative;
        overflow: hidden;
    }
    .auth-container::before {
        content: '';
        position: absolute;
        width: 600px; height: 600px;
        background: radial-gradient(circle, rgba(108,92,231,0.08) 0%, transparent 70%);
        top: -200px; right: -100px;
        border-radius: 50%;
    }
    .auth-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 48px 40px;
        width: 420px;
        position: relative;
        z-index: 1;
        box-shadow: var(--shadow);
    }
    .auth-logo {
        text-align: center;
        margin-bottom: 36px;
    }
    .auth-logo .icon {
        width: 56px; height: 56px;
        background: var(--green-light);
        border: 1px solid rgba(0,210,160,0.3);
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        font-size: 24px;
    }
    .auth-logo h1 {
        font-family: 'JetBrains Mono', monospace;
        font-size: 22px;
        font-weight: 700;
    }
    .auth-logo p {
        color: var(--text-secondary);
        font-size: 14px;
        margin-top: 6px;
    }
    .form-group { margin-bottom: 20px; }
    .form-group label {
        display: block; font-size: 13px; font-weight: 600;
        color: var(--text-secondary); margin-bottom: 8px;
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .form-group input {
        width: 100%; padding: 14px 16px;
        background: var(--bg-primary); border: 1px solid var(--border);
        border-radius: var(--radius-sm); color: var(--text-primary);
        font-family: 'DM Sans', sans-serif; font-size: 15px;
        transition: border-color 0.2s;
    }
    .form-group input:focus {
        outline: none; border-color: var(--green);
        box-shadow: 0 0 0 3px var(--green-light);
    }
    .form-group input::placeholder { color: var(--text-muted); }
    .btn-primary {
        width: 100%; padding: 14px;
        background: var(--green); color: #0a0a0f;
        border: none; border-radius: var(--radius-sm);
        font-family: 'DM Sans', sans-serif; font-size: 15px;
        font-weight: 600; cursor: pointer;
        transition: background 0.2s, transform 0.1s; margin-top: 8px;
    }
    .btn-primary:hover { background: #00e6b0; }
    .btn-primary:active { transform: scale(0.98); }
    .auth-footer {
        text-align: center; margin-top: 24px;
        font-size: 14px; color: var(--text-secondary);
    }
    .auth-footer a { color: var(--green); text-decoration: none; font-weight: 600; }
    .auth-footer a:hover { text-decoration: underline; }
    .error-msg {
        background: var(--red-light); border: 1px solid rgba(255,107,107,0.3);
        color: var(--red); padding: 12px 16px;
        border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="icon">✨</div>
            <h1>Buat Akun</h1>
            <p>Bergabung ke Private Chat</p>
        </div>

        @if($errors->any())
        <div class="error-msg">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="/register">
            @csrf
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="{{ old('username') }}" placeholder="contoh: johndoe" required>
            </div>
            <div class="form-group">
                <label>Nama Tampilan</label>
                <input type="text" name="display_name" value="{{ old('display_name') }}" placeholder="Nama yang akan ditampilkan" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="password_confirmation" placeholder="Ulangi password" required>
            </div>
            <button type="submit" class="btn-primary">Daftar Sekarang</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="/">Masuk di sini</a>
        </div>
    </div>
</div>
@endsection
