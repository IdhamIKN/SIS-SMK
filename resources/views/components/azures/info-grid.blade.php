<div class="fixed top-0 left-0 right-0 z-[1000] w-full bg-white dark:bg-slate-900 shadow-md px-4 py-3 flex items-center justify-between gap-2">

    {{-- Judul / Logo --}}
    <a href="{{ route('dashboard') }}" class="flex-1 font-semibold text-gray-800 dark:text-gray-100 no-underline truncate">
        {{ $headerTitle ?? config('app.name', 'SIS') }}
    </a>

    {{-- Tombol Back (opsional) --}}
    @if(isset($showBack) && $showBack)
        <a href="#" data-back-button class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-800 dark:text-gray-100">
            <i class="fas fa-arrow-left"></i>
        </a>
    @else
        {{-- Tombol Menu --}}
        <a href="#" data-menu="menu-main" class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-800 dark:text-gray-100">
            <i class="fas fa-bars"></i>
        </a>
    @endif

    {{-- Toggle Dark Mode --}}
    <a href="#" data-toggle-theme class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-800 dark:text-gray-100 show-on-theme-dark">
        <i class="fas fa-sun"></i>
    </a>
    <a href="#" data-toggle-theme class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-800 dark:text-gray-100 show-on-theme-light">
        <i class="fas fa-moon"></i>
    </a>

    {{-- Notifikasi (opsional) --}}
    @hasanyrole('superadmin|admin_tatib|bk')
        <a href="#" class="relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-800 dark:text-gray-100">
            <i class="fas fa-bell"></i>
            <span class="absolute top-0 right-0 w-5 h-5 bg-red-600 text-white text-xs flex items-center justify-center rounded-full font-bold">
                3
            </span>
        </a>
    @endhasanyrole

    {{-- Avatar / Profile --}}
    <a href="#" data-menu="menu-main" class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-slate-800">
        @if(auth()->user()?->avatar)
            <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="Avatar" class="rounded-full" style="width:32px;height:32px;object-fit:cover;">
        @else
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-xs text-white"></i>
            </div>
        @endif
    </a>

</div>

{{-- Offset untuk body content --}}
<div class="pt-16"></div>