<?php
// Include the new session manager
include_once 'api/config/session.php';
// "No Loophole" Bouncer:
// Check if user is logged in AND is an admin.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // If not, redirect to the login page
    header('Location: login.php');
    exit; // Stop the script from running
}

// --- We also need the user's info for the sidebar ---
$user_full_name = htmlspecialchars($_SESSION['full_name']);
$user_role = htmlspecialchars($_SESSION['role']);
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Dashboard - Disaster System</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=container-queries"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
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
                        "display": ["Public Sans", "sans-serif"]
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
<body class="bg-background-light dark:bg-background-dark font-display text-white">

    <div class="flex h-screen w-full">
    
        <aside class="flex w-64 flex-col bg-[#1c2127] p-4 text-white">
            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 bg-primary flex items-center justify-center">
                        <span class="material-symbols-outlined">security</span>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-white text-base font-medium leading-normal"><?php echo $user_full_name; ?></h1>
<p class="text-[#9dabb9] text-sm font-normal leading-normal"><?php echo $user_role; ?></p>
                    </div>
                </div>
                
                <nav class="flex flex-col gap-2">
                    <a class="flex items-center gap-3 rounded-lg bg-primary/20 px-3 py-2" href="dashboard.php">
                        <span class="material-symbols-outlined text-primary">dashboard</span>
                        <p class="text-primary text-sm font-medium leading-normal">Dashboard</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="residents.php">
                        <span class="material-symbols-outlined text-white">groups</span>
                        <p class="text-white text-sm font-medium leading-normal">Residents</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="evacuation.php">
                        <span class="material-symbols-outlined text-white">warehouse</span>
                        <p class="text-white text-sm font-medium leading-normal">Evacuation</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="relief.php">
                        <span class="material-symbols-outlined text-white">volunteer_activism</span>
                        <p class="text-white text-sm font-medium leading-normal">Relief</p>
                    </a>
                </nav>
            </div>
            <div class="mt-auto flex flex-col gap-4">
                <div class="flex flex-col gap-1 border-t border-white/10 pt-4">
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="#">
                        <span class="material-symbols-outlined text-white">settings</span>
                        <p class="text-white text-sm font-medium leading-normal">Settings</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="api/auth/logout_process.php">
                        <span class="material-symbols-outlined text-white">logout</span>
                        <p class="text-white text-sm font-medium leading-normal">Logout</p>
                    </a>
                </div>
            </div>
        </aside>

        <main class="flex flex-1 flex-col gap-6 p-6 overflow-y-auto">
            
            <h1 class="text-2xl font-bold leading-tight text-white">Command Center Dashboard</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="flex flex-col gap-2 rounded-lg bg-[#1c2127] p-4">
                    <p class="text-[#9dabb9] text-sm font-medium leading-normal">Total Households</p>
                    <p id="stats-total-households" class="text-white tracking-light text-2xl font-bold leading-tight">0</p>
                </div>
                <div class="flex flex-col gap-2 rounded-lg bg-[#1c2127] p-4">
                    <p class="text-[#9dabb9] text-sm font-medium leading-normal">Total Residents</p>
                    <p id="stats-total-residents" class="text-white tracking-light text-2xl font-bold leading-tight">0</p>
                </div>
                <div class="flex flex-col gap-2 rounded-lg bg-[#1c2127] p-4">
                    <p class="text-[#9dabb9] text-sm font-medium leading-normal">Affected Households</p>
                    <p id="stats-affected-households" class="text-white tracking-light text-2xl font-bold leading-tight">0</p>
                </div>
                <div class="flex flex-col gap-2 rounded-lg bg-[#1c2127] p-4">
                    <p class="text-[#9dabb9] text-sm font-medium leading-normal">Residents Evacuated</p>
                    <p id="stats-residents-evacuated" class="text-white tracking-light text-2xl font-bold leading-tight">0</p>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
                <div class="lg:col-span-1">
                    <div class="flex flex-col rounded-xl bg-[#1c2127]">
                        <h2 class="text-lg font-semibold text-white p-4">Evacuation Center Status</h2>
                        <div class="overflow-x-auto">
                            <div class="inline-block min-w-full align-middle">
                                <div class="overflow-hidden rounded-b-xl border-t border-[#3b4754]">
                                    <table id="centers-table" class="w-full min-w-full">
                                        <thead class="bg-[#283039]">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Center Name</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Status</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Occupancy</th>
                                            </tr>
                                        </thead>
                                        <tbody id="centers-table-body">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="flex flex-col rounded-xl bg-[#1c2127]">
                        <h2 class="text-lg font-semibold text-white p-4">Inventory Stock Levels</h2>
                        <div class="overflow-x-auto">
                            <div class="inline-block min-w-full align-middle">
                                <div class="overflow-hidden rounded-b-xl border-t border-[#3b4754]">
                                    <table id="inventory-table" class="w-full min-w-full">
                                        <thead class="bg-[#283039]">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Item Name</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Stock</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Unit</th>
                                            </tr>
                                        </thead>
                                        <tbody id="inventory-table-body">
                                            </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/dashboard.js"></script>

</body>
</html>