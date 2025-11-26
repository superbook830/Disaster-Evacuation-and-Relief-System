<?php
// my_aid_history.php
include_once 'api/config/session.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header('Location: login.php');
    exit;
}

$user_full_name = htmlspecialchars($_SESSION['full_name']);
$user_role = htmlspecialchars($_SESSION['role']);
$profile_pic = $_SESSION['profile_picture_url'] ?? 'assets/img/default-avatar.png';
if (!file_exists($profile_pic)) $profile_pic = 'assets/img/default-avatar.png';
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Aid History - Disaster System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script>
        tailwind.config = { darkMode: "class", theme: { extend: { colors: { "primary": "#137fec", "background-dark": "#101922" }, fontFamily: { "display": ["Public Sans"] } } } }
    </script>
</head>
<body class="bg-[#f6f7f8] dark:bg-background-dark font-display text-white">
    <div class="relative flex w-full">
        
        <aside class="sticky top-0 flex h-screen w-64 flex-col bg-[#111418] p-4 shrink-0 border-r border-[#223649]">
            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <img src="<?php echo $profile_pic; ?>" class="size-10 rounded-full bg-cover object-cover border border-slate-600">
                    <div class="flex flex-col"><h1 class="text-base font-medium truncate w-40"><?php echo $user_full_name; ?></h1><p class="text-[#9dabb9] text-sm capitalize"><?php echo $user_role; ?></p></div>
                </div>
                <div class="flex flex-col gap-2 mt-4">
                    <a href="my_dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">dashboard</span><p class="text-sm font-medium">My Dashboard</p>
                    </a>
                    <a href="my_household.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">home</span><p class="text-sm font-medium">My Household</p>
                    </a>
                    <a href="my_aid_history.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/20 text-primary">
                        <span class="material-symbols-outlined">receipt_long</span><p class="text-sm font-medium">Aid History</p>
                    </a>
                </div>
            </div>
            
            <div class="flex flex-col gap-4 mt-auto">
                <a href="#" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-10 px-4 bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition-colors">
                    <span class="material-symbols-outlined !text-[20px]">help</span><span class="truncate">Request Assistance</span>
                </a>
                <div class="flex flex-col gap-1">
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors"><span class="material-symbols-outlined">settings</span><p class="text-sm font-medium">Settings</p></a>
                    <a href="api/auth/logout_process.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors"><span class="material-symbols-outlined">logout</span><p class="text-sm font-medium">Logout</p></a>
                </div>
            </div>
        </aside>

        <main class="flex-1 h-screen overflow-y-auto bg-background-dark p-8">
            <div class="max-w-5xl mx-auto">
                
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-3xl font-bold">Aid History</h1>
                        <p class="text-slate-400 mt-1">Record of all relief goods received by your household.</p>
                    </div>
                </div>

                <div class="bg-[#1a222c] rounded-xl border border-[#283039] overflow-hidden shadow-xl">
                    <table class="w-full text-left">
                        <thead class="bg-[#223649] text-white uppercase text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Item Name</th>
                                <th class="px-6 py-4 font-semibold">Date Received</th>
                                <th class="px-6 py-4 font-semibold text-right">Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="aid-history-table-body" class="divide-y divide-[#283039] text-sm">
                            <tr><td colspan="3" class="px-6 py-8 text-center text-slate-400">Loading history...</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/my_dashboard.js"></script>
</body>
</html>