<?php
// Include the new session manager
include_once 'api/config/session.php';

// "No Loophole" Bouncer:
// Check if user is logged in AND is a resident.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    // If not, redirect to the login page
    header('Location: login.php');
    exit; // Stop the script from running
}

// --- All good, get user info for the page ---
$user_full_name = htmlspecialchars($_SESSION['full_name']);
$user_role = htmlspecialchars($_SESSION['role']);

// --- Profile Picture Logic ---
$profile_pic_url = $_SESSION['profile_picture_url'];
$profile_pic_path = 'assets/img/default-avatar.png'; // The default

if (!empty($profile_pic_url)) {
    // "No Loophole" Check: Make sure the file actually exists
    if (file_exists($profile_pic_url)) {
        $profile_pic_path = $profile_pic_url;
    } else {
        // The path in the DB is broken. Reset it in the session for now.
        $_SESSION['profile_picture_url'] = null;
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>My Dashboard - Disaster System</title>
    <script src="https://cdn.tailwindcss.com?plugins=container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Public Sans"]
                    },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            font-size: 24px;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
    
    <div class="relative flex w-full">

        <aside class="sticky top-0 flex h-screen w-64 flex-col bg-[#111418] p-4 text-white shrink-0">
            <div class="flex flex-col gap-4">
                
                <div class="flex items-center gap-3">
                    <button id="upload-pic-btn" class="relative group">
                        <img src="<?php echo $profile_pic_path; ?>" alt="Profile Picture" 
                             class="size-10 rounded-full bg-cover bg-center object-cover">
                        <div class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="material-symbols-outlined text-white !text-lg">upload</span>
                        </div>
                    </button>
                    <div class="flex flex-col">
                        <h1 class="text-white text-base font-medium leading-normal"><?php echo $user_full_name; ?></h1>
                        <p class="text-[#9dabb9] text-sm font-normal leading-normal"><?php echo $user_role; ?></p>
                    </div>
                </div>
                <form id="pic-upload-form" class="hidden">
                    <input type="file" id="profile-pic-input" name="profile_pic" accept="image/png, image/jpeg">
                </form>
                <div class="flex flex-col gap-2 mt-4">
                    <a href="my_dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/20 text-primary">
                        <span class="material-symbols-outlined !text-[24px]">dashboard</span>
                        <p class="text-sm font-medium leading-normal">My Dashboard</p>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors duration-200 cursor-pointer">
                        <span class="material-symbols-outlined !text-[24px]">home</span>
                        <p class="text-sm font-medium leading-normal">My Household</p>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors duration-200 cursor-pointer">
                        <span class="material-symbols-outlined !text-[24px]">receipt_long</span>
                        <p class="text-sm font-medium leading-normal">Aid History</p>
                    </a>
                </div>
            </div>

            <div class="flex flex-col gap-4 mt-auto">
                <a href="#" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-10 px-4 bg-green-600 text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-green-700 transition-colors">
                    <span class="material-symbols-outlined !text-[20px]">help</span>
                    <span class="truncate">Request Assistance</span>
                </a>
                <div class="flex flex-col gap-1">
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors duration-200 cursor-pointer">
                        <span class="material-symbols-outlined !text-[24px]">settings</span>
                        <p class="text-sm font-medium leading-normal">Settings</p>
                    </a>
                    <a href="api/auth/logout_process.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors duration-200 cursor-pointer">
                        <span class="material-symbols-outlined !text-[24px]">logout</span>
                        <p class="text-sm font-medium leading-normal">Logout</p>
                    </a>
                </div>
            </div>
        </aside>

        <main class="flex-1">
            <div class="p-6 lg:p-8">
                
                <div class="flex flex-wrap justify-between gap-3 mb-6">
                    <h1 class="text-white text-4xl font-black leading-tight tracking-[-0.033em]">Welcome, <?php echo htmlspecialchars(explode(' ', $user_full_name)[0]); ?>!</h1>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <div class="rounded-xl bg-[#1a222c] p-6 border border-[#283039] md:col-span-2">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-xl font-bold text-white">My Household</h2>
                                <p id="household-address" class="text-slate-300 mt-1 text-sm">Loading...</p>
                            </div>
                            <a href="#" class="text-sm font-bold text-primary hover:underline">Manage</a>
                        </div>
                        
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full min-w-full">
                                <thead class="border-b border-b-slate-600">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-white text-sm font-medium">Name</th>
                                        <th class="px-4 py-3 text-left text-white text-sm font-medium">Birthdate</th>
                                        <th class="px-4 py-3 text-left text-white text-sm font-medium">Gender</th>
                                    </tr>
                                </thead>
                                <tbody id="my-household-list" class="divide-y divide-slate-700">
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-center text-slate-400">Loading members...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="rounded-xl bg-[#1a222c] p-6 border border-[#283039]">
                        <h2 class="text-xl font-bold text-white mb-4">Aid Received</h2>
                        
                        <ul id="my-aid-history-list" class="flex flex-col gap-3 max-h-60 overflow-y-auto">
                            <li class="px-2 py-4 text-center text-slate-400">Loading history...</li>
                        </ul>
                    </div>

                    <div class="rounded-xl bg-[#1a222c] p-6 border border-[#283039]">
                        <h2 class="text-xl font-bold text-white">Evacuation History</h2>
                        <p class="text-slate-300 mt-2">Check your family's evacuation check-in and check-out records.</p>
                        <button class="mt-4 text-sm font-bold text-primary hover:underline">View Records</button>
                    </div>

                    <div class="rounded-xl bg-primary/20 p-6 border border-primary">
                        <h2 class="text-xl font-bold text-white">Request Assistance</h2>
                        <p class="text-slate-300 mt-2">Submit a new request for food, medical, or other assistance.</p>
                        <button class="mt-4 text-sm font-bold text-primary hover:underline">Submit Request</button>
                    </div>
                    
                    <div class="rounded-xl bg-[#1a222c] p-6 border border-[#283039]">
                        <h2 class="text-xl font-bold text-white">Evacuation Centers</h2>
                        <p class="text-slate-300 mt-2">Find the nearest open evacuation center.</p>
                        <button class="mt-4 text-sm font-bold text-primary hover:underline">View Map</button>
                    </div>

                </div>

            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/my_dashboard.js"></script>
</body>
</html>