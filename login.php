<?php
// Include the new session manager
include_once 'api/config/session.php';

// "No Loophole" Smart Bouncer:
if (isset($_SESSION['user_id'])) {
    
    // Check their role
    if ($_SESSION['role'] === 'admin') {
        header('Location: dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'resident') {
        header('Location: my_dashboard.php');
        exit;
    }
    
    // Fallback for any other role (like 'volunteer' or 'empty')
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Log In - Disaster Assistance & Evacuation Tracking System</title>
    <script src="https://cdn.tailwindcss.com?plugins=container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;700;900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0d7ff2",
                        "background-light": "#f5f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Public Sans", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }
    </style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
    <div class="relative flex h-auto min-h-screen w-full flex-col items-center">
        <header class="flex h-20 w-full items-center justify-center border-b border-white/10 px-4 sm:px-8">
            <div class="flex w-full max-w-6xl items-center justify-between">
                <div class="flex items-center gap-4 text-white">
                    <div class="h-8 w-8 text-primary">
                        <svg fill="none" viewbox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                            <path d="M39.5563 34.1455V13.8546C39.5563 15.708 36.8773 17.3437 32.7927 18.3189C30.2914 18.916 27.263 19.2655 24 19.2655C20.737 19.2655 17.7086 18.916 15.2073 18.3189C11.1227 17.3437 8.44365 15.708 8.44365 13.8546V34.1455C8.44365 35.9988 11.1227 37.6346 15.2073 38.6098C17.7086 39.2069 20.737 39.5564 24 39.5564C27.263 39.5564 30.2914 39.2069 32.7927 38.6098C36.8773 37.6346 39.5563 35.9988 39.5563 34.1455Z" fill="currentColor"></path>
                            <path clip-rule="evenodd" d="M10.4485 13.8519C10.4749 13.9271 10.6203 14.246 11.379 14.7361C12.298 15.3298 13.7492 15.9145 15.6717 16.3735C18.0007 16.9296 20.8712 17.2655 24 17.2655C27.1288 17.2655 29.9993 16.9296 32.3283 16.3735C34.2508 15.9145 35.702 15.3298 36.621 14.7361C37.3796 14.246 37.5251 13.9271 37.5515 13.8519C37.5287 13.7876 37.4333 13.5973 37.0635 13.2931C36.5266 12.8516 35.6288 12.3647 34.343 11.9175C31.79 11.0295 28.1333 10.4437 24 10.4437C19.8667 10.4437 16.2099 11.0295 13.657 11.9175C12.3712 12.3647 11.4734 12.8516 10.9365 13.2931C10.5667 13.5973 10.4713 13.7876 10.4485 13.8519ZM37.5563 18.7877C36.3176 19.3925 34.8502 19.8839 33.2571 20.2642C30.5836 20.9025 27.3973 21.2655 24 21.2655C20.6027 21.2655 17.4164 20.9025 14.7429 20.2642C13.1498 19.8839 11.6824 19.3925 10.4436 18.7877V34.1275C10.4515 34.1545 10.5427 34.4867 11.379 35.027C12.298 35.6207 13.7492 36.2054 15.6717 36.6644C18.0007 37.2205 20.8712 37.5564 24 37.5564C27.1288 37.5564 29.9993 37.2205 32.3283 36.6644C34.2508 36.2054 35.702 35.6207 36.621 35.027C37.4573 34.4867 37.5485 34.1546 37.5563 34.1275V18.7877ZM41.5563 13.8546V34.1455C41.5563 36.1078 40.158 37.5042 38.7915 38.3869C37.3498 39.3182 35.4192 40.0389 33.2571 40.5551C30.5836 41.1934 27.3973 41.5564 24 41.5564C20.6027 41.5564 17.4164 41.1934 14.7429 40.5551C12.5808 40.0389 10.6502 39.3182 9.20848 38.3869C7.84205 37.5042 6.44365 36.1078 6.44365 34.1455L6.44365 13.8546C6.44365 12.2684 7.37223 11.0454 8.39581 10.2036C9.43325 9.3505 10.8137 8.67141 12.343 8.13948C15.4203 7.06909 19.5418 6.44366 24 6.44366C28.4582 6.44366 32.5797 7.06909 35.657 8.13948C37.1863 8.67141 38.5667 9.3505 39.6042 10.2036C40.6278 11.0454 41.5563 12.2684 41.5563 13.8546Z" fill="currentColor" fill-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h1 class="text-white text-lg font-bold leading-tight tracking-[-0.015em]">Disaster Assistance &amp; Evacuation Tracking System</h1>
                </div>
            </div>
        </header>
        <main class="flex w-full flex-1 items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="w-full max-w-md space-y-8">
                <div class="flex flex-col gap-3 text-center">
                    <p class="text-white text-4xl font-black leading-tight tracking-[-0.033em]">Log In to Your Account</p>
                    <p class="text-slate-400 text-base font-normal leading-normal">Enter your credentials to access the system.</p>
                </div>
                <div class="flex flex-col rounded-xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-sm">
                    <form id="login-form" class="space-y-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-white text-base font-medium leading-normal" for="username">Email Address / Username</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <span class="material-symbols-outlined text-slate-400">person</span>
                                </div>
                                <input autocomplete="username" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg border border-slate-700 bg-slate-800 py-3 pl-12 pr-4 text-base font-normal leading-normal text-white placeholder:text-slate-400 focus:border-primary focus:outline-0 focus:ring-2 focus:ring-primary/50 h-14" id="username" name="username" placeholder="Enter your email or username" required="" type="text" />
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-white text-base font-medium leading-normal" for="password">Password</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <span class="material-symbols-outlined text-slate-400">lock</span>
                                </div>
                                <input autocomplete="current-password" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg border border-slate-700 bg-slate-800 py-3 pl-12 pr-12 text-base font-normal leading-normal text-white placeholder:text-slate-400 focus:border-primary focus:outline-0 focus:ring-2 focus:ring-primary/50 h-14" id="password" name="password" placeholder="Enter your password" required="" type="password" />
                                <button id="toggle-password" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-white" type="button">
                                    <span id="toggle-password-icon" class="material-symbols-outlined">visibility_off</span>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center justify-end">
                            </div>
                        <div>
                            <button id="submit-button" class="flex w-full items-center justify-center rounded-lg bg-primary px-4 py-3.5 text-base font-bold text-white transition-colors hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-background-dark" type="submit">
                                Log In
                            </button>
                        </div>
                        <div id="login-message" class="hidden flex items-center gap-3 rounded-lg border border-amber-500/50 bg-amber-500/10 p-3 mt-4">
                            <span id="login-message-icon" class="material-symbols-outlined text-amber-400">warning</span>
                            <p id="login-message-text" class="text-sm font-medium text-amber-300">Invalid credentials. Please try again.</p>
                        </div>
                    </form>
                    <div id="login-message" class="hidden flex items-center gap-3 rounded-lg border border-amber-500/50 bg-amber-500/10 p-3 mt-4">
                            </div>
                    </form>
                    
                    <p class="text-center text-sm text-slate-400 pt-6 border-t border-white/10">
                        Don't have an account?
                        <a class="font-medium text-primary hover:underline" href="register.php">
                            Sign Up
                        </a>
                    </p>
                    </div> </div>
        </main>
                </div>
            </div>
        </main>
        <footer class="w-full py-6 px-4 sm:px-8">
            <div class="flex w-full max-w-6xl mx-auto flex-col items-center justify-center gap-2 border-t border-white/10 pt-6 text-center text-sm text-slate-400 sm:flex-row sm:justify-between">
                <p>© 2024 Disaster Assistance &amp; Evacuation Tracking System. All Rights Reserved.</p>
                <div class="flex gap-4">
                    <a class="hover:text-white hover:underline" href="#">Support</a>
                    <a class="hover:text-white hover:underline" href="#">Terms of Service</a>
                    <a class="hover:text-white hover:underline" href="#">Privacy Policy</a>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/login.js"></script>
</body>
</html>