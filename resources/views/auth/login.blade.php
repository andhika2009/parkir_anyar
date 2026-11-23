<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Parkir</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
     <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-[#0a0a0a] overflow-hidden relative font-['Plus_Jakarta_Sans',sans-serif] px-4">

    <!-- Ambient Orbs (Senada Emas / Amber Dashboard) -->
    <div class="absolute w-[350px] h-[350px] bg-amber-400 rounded-full blur-[130px] opacity-20 pointer-events-none top-[15%] left-[20%] animate-pulse"></div>
    <div class="absolute w-[300px] h-[300px] bg-yellow-600 rounded-full blur-[130px] opacity-20 pointer-events-none bottom-[15%] right-[20%] animate-pulse"></div>

    <!-- Container Form -->
    <div class="relative z-10 w-full max-w-[400px] p-10 bg-[#141414]/90 backdrop-blur-xl border border-amber-400/25 rounded-[20px] shadow-[0_25px_50px_-12px_rgba(0,0,0,0.85)]">
        <div class="text-center mb-8">
            <h2 class="text-amber-300 text-[1.6rem] font-bold tracking-tight mb-1.5">Sistem Parkir</h2>
            <p class="text-neutral-400 text-sm">Masuk ke akun Anda untuk mengakses sistem</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/login" method="POST">
            @csrf

            <div class="mb-5">
                <label for="username" class="block text-neutral-300 text-[0.825rem] font-semibold mb-2">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="{{ old('username') }}"
                    placeholder="Masukkan username" 
                    required 
                    autofocus
                    class="w-full px-4 py-3 bg-[#1c1c1c] border border-amber-400/20 rounded-[10px] text-white text-sm placeholder-neutral-500 outline-none transition-all duration-200 focus:bg-[#222222] focus:border-amber-400 focus:ring-4 focus:ring-amber-400/15"
                >
            </div>

            <div class="mb-6">
                <label for="password" class="block text-neutral-300 text-[0.825rem] font-semibold mb-2">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••" 
                    required
                    class="w-full px-4 py-3 bg-[#1c1c1c] border border-amber-400/20 rounded-[10px] text-white text-sm placeholder-neutral-500 outline-none transition-all duration-200 focus:bg-[#222222] focus:border-amber-400 focus:ring-4 focus:ring-amber-400/15"
                >
            </div>

            <button 
                type="submit" 
                class="w-full py-3.5 bg-gradient-to-r from-amber-400 to-yellow-500 hover:from-amber-300 hover:to-yellow-400 active:translate-y-0 hover:-translate-y-0.5 text-neutral-950 rounded-[10px] text-sm font-bold cursor-pointer transition-all duration-200 shadow-[0_4px_14px_rgba(251,191,36,0.25)] hover:shadow-[0_6px_20px_rgba(251,191,36,0.4)]"
            >
                Login
            </button>
        </form>
    </div>

</body>
</html>