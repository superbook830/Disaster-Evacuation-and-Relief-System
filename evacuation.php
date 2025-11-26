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
    <title>Evacuation Management - Disaster System</title>
    <script src="https://cdn.tailwindcss.com?plugins=container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        display: ["Public Sans", "sans-serif"]
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

    <!-- Sidebar -->
    <aside class="flex w-64 flex-col bg-[#1c2127] p-4 text-white">
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-3">
                <div class="bg-primary rounded-full size-10 flex items-center justify-center">
                    <span class="material-symbols-outlined">security</span>
                </div>
                <div>
                   <h1 class="text-white text-base font-medium leading-normal"><?php echo $user_full_name; ?></h1>
<p class="text-[#9dabb9] text-sm font-normal leading-normal"><?php echo $user_role; ?></p>
                </div>
            </div>

            <nav class="flex flex-col gap-2">
                <a class="flex items-center gap-3 px-3 py-2 hover:bg-white/10 rounded-lg" href="dashboard.php">
                    <span class="material-symbols-outlined">dashboard</span>
                    <p>Dashboard</p>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 hover:bg-white/10 rounded-lg" href="residents.php">
                    <span class="material-symbols-outlined">groups</span>
                    <p>Residents</p>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 bg-primary/20 rounded-lg" href="evacuation.php">
                    <span class="material-symbols-outlined text-primary">warehouse</span>
                    <p class="text-primary">Evacuation</p>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 hover:bg-white/10 rounded-lg" href="relief.php">
                    <span class="material-symbols-outlined">volunteer_activism</span>
                    <p>Relief</p>
                </a>
            </nav>
        </div>

        <div class="mt-auto border-t border-white/10 pt-4">
            <a href="#" class="flex items-center gap-3 px-3 py-2 hover:bg-white/10 rounded-lg">
                <span class="material-symbols-outlined">settings</span><p>Settings</p>
            </a>
            <a href="api/auth/logout_process.php" class="flex items-center gap-3 px-3 py-2 hover:bg-white/10 rounded-lg">
                <span class="material-symbols-outlined">logout</span><p>Logout</p>
            </a>
        </div>
    </aside>

    <!-- Main -->
    <main class="flex-1 p-6 overflow-y-auto">
        <h1 class="text-2xl font-bold mb-6">Evacuation Service</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add Center Form -->
            <div class="lg:col-span-1">
                <div class="bg-[#1c2127] p-4 rounded-xl">
                    <h2 class="text-lg font-semibold mb-4">Add New Center</h2>
                    <form id="add-center-form" class="flex flex-col gap-4">
                        <input type="text" name="center_name" placeholder="Center Name" class="w-full h-11 px-4 rounded-lg dark:bg-[#283039]" required>
                        <input type="text" name="address" placeholder="Address" class="w-full h-11 px-4 rounded-lg dark:bg-[#283039]" required>
                        <input type="number" name="capacity" placeholder="Total Capacity" class="w-full h-11 px-4 rounded-lg dark:bg-[#283039]" required>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="is_active" value="1" checked class="h-5 w-5 text-primary"> Active
                        </label>
                        <button type="submit" class="bg-primary hover:bg-primary/80 rounded-lg h-11 text-white font-bold flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">add_location</span> Add Center
                        </button>
                    </form>
                    <div id="form-message" class="mt-4 text-sm"></div>
                </div>
            </div>

            <!-- Table -->
            <div class="lg:col-span-2">
                <div class="bg-[#1c2127] rounded-xl">
                    <h2 class="text-lg font-semibold p-4">Current Evacuation Centers</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-[#283039]">
                                <tr>
                                    <th class="px-4 py-3 text-left">Center Name</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-left">Occupancy</th>
                                    <th class="px-4 py-3 text-left">Remaining</th>
                                    <th class="px-4 py-3 text-left">Address</th>
                                    <th class="px-4 py-3 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="centers-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
    <div class="w-full max-w-md rounded-xl bg-[#1c2127] p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-white">Edit Evacuation Center</h2>
            <button type="button" class="cancel-modal-btn text-[#9dabb9] hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

       <form id="edit-center-form" class="space-y-4">
    <input type="hidden" name="id" id="edit_center_id">

    <div>
        <label for="edit_center_name" class="block text-sm font-medium text-gray-300">Center Name</label>
        <input type="text" name="center_name" id="edit_center_name" class="w-full rounded-md p-2 bg-[#1e293b] text-white">
    </div>

    <div>
        <label for="edit_address" class="block text-sm font-medium text-gray-300">Address</label>
        <input type="text" name="address" id="edit_address" class="w-full rounded-md p-2 bg-[#1e293b] text-white">
    </div>

    <div>
        <label for="edit_capacity" class="block text-sm font-medium text-gray-300">Capacity</label>
        <input type="number" name="capacity" id="edit_capacity" class="w-full rounded-md p-2 bg-[#1e293b] text-white">
    </div>

    <div class="flex items-center space-x-2">
        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="h-5 w-5 text-primary">
        <label for="edit_is_active" class="text-gray-300 text-sm">Active</label>
    </div>

    <p id="edit-modal-message" class="text-sm text-center"></p>

    <div class="flex justify-end space-x-2">
        <button type="button" class="cancel-modal-btn bg-gray-600 text-white px-4 py-2 rounded-md">Cancel</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Save Changes</button>
    </div>
</form>


    </div>
</div>

<!-- Delete Modal -->
<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
    <div class="w-full max-w-md rounded-xl bg-[#1c2127] p-6 shadow-xl">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-semibold text-white">Confirm Deletion</h2>
            <button type="button" class="cancel-delete-modal-btn text-[#9dabb9] hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <p class="text-[#9dabb9] text-sm mb-4">Are you sure you want to delete this evacuation center? This action cannot be undone.</p>
        <input type="hidden" id="delete_center_id" value="0">

        <div id="delete-modal-message" class="mt-2 text-sm mb-4"></div>

        <div class="flex justify-end gap-4 mt-4">
            <button type="button" class="cancel-delete-modal-btn rounded-lg h-11 px-4 bg-[#283039] text-white text-sm font-bold hover:bg-[#3b4754]">Cancel</button>
            <button type="button" id="confirm-delete-btn" class="flex items-center gap-2 rounded-lg h-11 px-4 bg-red-600 text-white text-sm font-bold hover:bg-red-700">
                <span class="material-symbols-outlined">delete</span> Delete
            </button>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/evacuation.js"></script>
</body>
</html>
