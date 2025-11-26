<?php
// Include the session manager
include_once 'api/config/session.php';

// "No Loophole" Bouncer:
// Check if user is logged in AND is an admin.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // If not, redirect to the login page
    header('Location: login.php');
    exit; // Stop the script from running
}

// --- All good, get user info for the page ---
$user_full_name = htmlspecialchars($_SESSION['full_name']);
$user_role = htmlspecialchars($_SESSION['role']);
$profile_pic_path = $_SESSION['profile_picture_url'] ?? 'assets/img/default-avatar.png';

// "No Loophole" Check: Make sure the file actually exists
if (empty($_SESSION['profile_picture_url']) || !file_exists($profile_pic_path)) {
    $profile_pic_path = 'assets/img/default-avatar.png';
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Manage Relief Goods - Admin Dashboard</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
        /* Style for the modal backdrop */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
    <div class="relative flex min-h-screen w-full">
        
        <aside class="flex flex-col w-64 bg-[#111418] p-4 text-white shrink-0">
            <div class="flex flex-col gap-4">
                
                <div class="flex items-center gap-3">
                    <img src="<?php echo $profile_pic_path; ?>" alt="Profile Picture" 
                         class="size-10 rounded-full bg-cover bg-center object-cover">
                    <div class="flex flex-col">
                        <h1 class="text-white text-base font-medium leading-normal"><?php echo $user_full_name; ?></h1>
                        <p class="text-[#9dabb9] text-sm font-normal leading-normal"><?php echo $user_role; ?></p>
                    </div>
                </div>

                <div class="flex flex-col gap-2 mt-4">
                    <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors duration-200 cursor-pointer">
                        <span class="material-symbols-outlined !text-[24px]">dashboard</span>
                        <p class="text-sm font-medium leading-normal">Dashboard</p>
                    </a>
                    
                    <a href="relief_goods.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/20 text-primary">
                        <span class="material-symbols-outlined !text-[24px]">inventory_2</span>
                        <p class="text-sm font-medium leading-normal">Relief Goods</p>
                    </a>

                    <a href="residents.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors duration-200 cursor-pointer">
                        <span class="material-symbols-outlined !text-[24px]">groups</span>
                        <p class="text-sm font-medium leading-normal">Residents</p>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors duration-200 cursor-pointer">
                        <span class="material-symbols-outlined !text-[24px]">flag</span>
                        <p class="text-sm font-medium leading-normal">Reports</p>
                    </a>
                </div>
            </div>

            <div class="flex flex-col gap-4 mt-auto">
                <a href="api/auth/logout_process.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors duration-200 cursor-pointer">
                    <span class="material-symbols-outlined !text-[24px]">logout</span>
                    <p class="text-sm font-medium leading-normal">Logout</p>
                </a>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto">
            <div class="p-6 lg:p-8">
                
                <div class="flex flex-wrap justify-between gap-3 mb-6">
                    <h1 class="text-white text-4xl font-black leading-tight tracking-[-0.033em]">Relief Goods Management</h1>
                    
                    <button id="add-item-btn" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-12 px-5 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined !text-[20px]">add_circle</span>
                        <span class="truncate">Register New Item</span>
                    </button>
                </div>

                <div class="rounded-xl bg-[#1a222c] p-6 border border-[#283039]">
                    <h2 class="text-xl font-bold text-white mb-4">Item Inventory</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full">
                            <thead class="border-b border-b-slate-600">
                                <tr>
                                    <th class="px-4 py-3 text-left text-white text-sm font-medium">Item Name</th>
                                    <th class="px-4 py-3 text-left text-white text-sm font-medium">Description</th>
                                    <th class="px-4 py-3 text-left text-white text-sm font-medium">In Stock</th>
                                    <th class="px-4 py-3 text-left text-white text-sm font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="items-table-body" class="divide-y divide-slate-700">
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-center text-slate-400">Loading items...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <div id="add-item-modal" class="fixed inset-0 z-50 flex items-center justify-center overflow-auto modal-backdrop hidden">
        <div class="relative w-full max-w-lg p-6 bg-[#1a222c] rounded-xl border border-[#283039] m-4">
            <h2 class="text-2xl font-bold text-white mb-4">Register New Item</h2>
            <form id="add-item-form">
                <div class="space-y-4">
                    <div>
                        <label for="add_item_name" class="block text-sm font-medium text-white mb-1">Item Name</label>
                        <input type="text" id="add_item_name" name="item_name" required class="w-full rounded-lg text-white focus:outline-0 focus:ring-0 border-none bg-[#101922] h-12 placeholder:text-[#9dabb9] px-4">
                    </div>
                    <div>
                        <label for="add_description" class="block text-sm font-medium text-white mb-1">Description</label>
                        <textarea id="add_description" name="description" rows="3" class="w-full rounded-lg text-white focus:outline-0 focus:ring-0 border-none bg-[#101922] placeholder:text-[#9dabb9] px-4 py-3"></textarea>
                    </div>
                    <div>
                        <label for="add_quantity" class="block text-sm font-medium text-white mb-1">Quantity in Stock</Labe>
                        <input type="number" id="add_quantity" name="quantity" min="0" value="0" required class="w-full rounded-lg text-white focus:outline-0 focus:ring-0 border-none bg-[#101922] h-12 placeholder:text-[#9dabb9] px-4">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" id="add-modal-close" class="px-4 py-2 rounded-lg text-white bg-slate-600 hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-white bg-primary hover:bg-primary/90 transition-colors">Save Item</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="edit-item-modal" class="fixed inset-0 z-50 flex items-center justify-center overflow-auto modal-backdrop hidden">
        <div class="relative w-full max-w-lg p-6 bg-[#1a222c] rounded-xl border border-[#283039] m-4">
            <h2 class="text-2xl font-bold text-white mb-4">Edit Item</h2>
            <form id="edit-item-form">
                <input type="hidden" id="edit_item_id" name="item_id">
                <div class="space-y-4">
                    <div>
                        <label for="edit_item_name" class="block text-sm font-medium text-white mb-1">Item Name</label>
                        <input type="text" id="edit_item_name" name="item_name" required class="w-full rounded-lg text-white focus:outline-0 focus:ring-0 border-none bg-[#101922] h-12 placeholder:text-[#9dabb9] px-4">
                    </div>
                    <div>
                        <label for="edit_description" class="block text-sm font-medium text-white mb-1">Description</label>
                        <textarea id="edit_description" name="description" rows="3" class="w-full rounded-lg text-white focus:outline-0 focus:ring-0 border-none bg-[#101922] placeholder:text-[#9dabb9] px-4 py-3"></textarea>
                    </div>
                    <div>
                        <label for="edit_quantity" class="block text-sm font-medium text-white mb-1">Quantity in Stock</Labe>
                        <input type="number" id="edit_quantity" name="quantity" min="0" required class="w-full rounded-lg text-white focus:outline-0 focus:ring-0 border-none bg-[#101922] h-12 placeholder:text-[#9dabb9] px-4">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" id="edit-modal-close" class="px-4 py-2 rounded-lg text-white bg-slate-600 hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-white bg-primary hover:bg-primary/90 transition-colors">Update Item</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/relief_goods.js"></script>
</body>
</html>