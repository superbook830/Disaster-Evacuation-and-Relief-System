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

// Get the household ID from the URL
$household_id = htmlspecialchars($_GET['id'] ?? 0);
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Household Details - Disaster System</title>
    
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
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="dashboard.php">
                        <span class="material-symbols-outlined text-white">dashboard</span>
                        <p class="text-white text-sm font-medium leading-normal">Dashboard</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg bg-primary/20 px-3 py-2" href="residents.php">
                        <span class="material-symbols-outlined text-primary">groups</span>
                        <p class="text-primary text-sm font-medium leading-normal">Residents</p>
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
            
            <a href="residents.php" class="flex items-center gap-2 text-[#9dabb9] hover:text-primary w-fit">
                <span class="material-symbols-outlined">arrow_back</span>
                Back to All Households
            </a>
            
            <h1 id="household-name-title" class="text-2xl font-bold leading-tight text-white">Loading...</h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
                <div class="lg:col-span-1">
                    <div class="flex flex-col rounded-xl bg-[#1c2127] p-4">
                        <h2 class="text-lg font-semibold text-white mb-4">Add New Resident</h2>
                        
                        <form id="add-resident-form" class="flex flex-col gap-4">
                            <input type="hidden" name="household_id" value="<?php echo $household_id; ?>">
                            
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-[#9dabb9] mb-1">First Name:</label>
                                <input type="text" id="first_name" name="first_name" 
                                       class="w-full h-11 px-4 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white placeholder:text-[#9dabb9] focus:ring-2 focus:ring-primary" required> </div>
                            
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-[#9dabb9] mb-1">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" 
                                       class="w-full h-11 px-4 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white placeholder:text-[#9dabb9] focus:ring-2 focus:ring-primary" required> </div>
                            
                            <div>
                                <label for="birthdate" class="block text-sm font-medium text-[#9dabb9] mb-1">Birthdate:</label>
                                <input type="date" id="birthdate" name="birthdate" 
                                       class="w-full h-11 px-4 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white placeholder:text-[#9dabb9] focus:ring-2 focus:ring-primary"> </div>
                            
                            <div>
                                <label for="gender" class="block text-sm font-medium text-[#9dabb9] mb-1">Gender:</label>
                                <select id="gender" name="gender" 
                                        class="w-full h-11 px-4 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white placeholder:text-[#9dabb9] focus:ring-2 focus:ring-primary"> <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="flex items-center gap-2 text-sm text-white">
                                    <input type="checkbox" name="is_pwd" value="1" 
                                           class="h-5 w-5 rounded bg-gray-100 dark:bg-[#283039] border-none text-primary focus:ring-primary">
                                    Is PWD?
                                </label>
                                <label class="flex items-center gap-2 text-sm text-white">
                                    <input type="checkbox" name="is_senior" value="1" 
                                           class="h-5 w-5 rounded bg-gray-100 dark:bg-[#283039] border-none text-primary focus:ring-primary">
                                    Is Senior Citizen?
                                </label>
                            </div>

                            <div>
                                <label for="remarks" class="block text-sm font-medium text-[#9dabb9] mb-1">Remarks:</label>
                                <textarea id="remarks" name="remarks" 
                                          class="w-full p-3 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white placeholder:text-[#9dabb9] focus:ring-2 focus:ring-primary" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" class="flex min-w-[84px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-11 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/80"> <span class="material-symbols-outlined">person_add</span>
                                <span class="truncate">Add Resident</span>
                            </button>
                        </form>
                        
                        <div id="form-message" class="mt-4 text-sm"></div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="flex flex-col rounded-xl bg-[#1c2127]">
                        <h2 class="text-lg font-semibold text-white p-4">Residents in this Household</h2>
                        
                        <div class="overflow-x-auto">
                            <div class="inline-block min-w-full align-middle">
                                <div class="overflow-hidden rounded-b-xl border-t border-[#3b4754]">
                                    <table id="residents-table" class="w-full min-w-full">
                                        <thead class="bg-[#283039]">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">ID</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">First Name</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Last Name</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Birthdate</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">PWD?</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Senior?</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="residents-table-body">
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

    <div id="edit-resident-modal" class="fixed inset-0 z-50 items-center justify-center p-4 hidden bg-black/50">
        <div class="flex flex-col rounded-xl bg-[#1c2127] p-6 shadow-xl w-full max-w-lg border border-slate-700">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">Edit Resident</h2>
                <button type="button" class="close-edit-modal-btn text-[#9dabb9] hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form id="edit-resident-form" class="flex flex-col gap-4 mt-6">
                <input type="hidden" id="edit_resident_id" name="id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_first_name" class="block text-sm font-medium text-[#9dabb9] mb-1">First Name:</label>
                        <input type="text" id="edit_first_name" name="first_name" 
                               class="w-full h-11 px-4 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white" required>
                    </div>
                    
                    <div>
                        <label for="edit_last_name" class="block text-sm font-medium text-[#9dabb9] mb-1">Last Name:</label>
                        <input type="text" id="edit_last_name" name="last_name" 
                               class="w-full h-11 px-4 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white" required>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_birthdate" class="block text-sm font-medium text-[#9dabb9] mb-1">Birthdate:</label>
                        <input type="date" id="edit_birthdate" name="birthdate" 
                               class="w-full h-11 px-4 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white">
                    </div>
                    
                    <div>
                        <label for="edit_gender" class="block text-sm font-medium text-[#9dabb9] mb-1">Gender:</label>
                        <select id="edit_gender" name="gender" 
                                class="w-full h-11 px-4 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-6 mt-2">
                    <label class="flex items-center gap-2 text-sm text-white">
                        <input type="checkbox" id="edit_is_pwd" name="is_pwd" value="1" 
                               class="h-5 w-5 rounded bg-gray-100 dark:bg-[#283039] border-none text-primary focus:ring-primary">
                        Is PWD?
                    </label>
                    <label class="flex items-center gap-2 text-sm text-white">
                        <input type="checkbox" id="edit_is_senior" name="is_senior" value="1" 
                               class="h-5 w-5 rounded bg-gray-100 dark:bg-[#283039] border-none text-primary focus:ring-primary">
                        Is Senior Citizen?
                    </label>
                </div>

                <div>
                    <label for="edit_remarks" class="block text-sm font-medium text-[#9dabb9] mb-1">Remarks:</label>
                    <textarea id="edit_remarks" name="remarks" 
                              class="w-full p-3 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white" rows="3"></textarea>
                </div>
                
                <button type="submit" class="flex min-w-[84px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-11 px-4 bg-primary text-white text-sm font-bold hover:bg-primary/80">
                    <span class="material-symbols-outlined">save</span>
                    <span class="truncate">Save Changes</span>
                </button>
            </form>
            <div id="edit-form-message" class="mt-4 text-sm"></div>
        </div>
    </div>
    <div id="delete-resident-modal" class="fixed inset-0 z-50 items-center justify-center p-4 hidden bg-black/50">
        <div class="flex flex-col rounded-xl bg-[#1c2127] p-6 shadow-xl w-full max-w-md border border-slate-700">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-red-400 !text-3xl">warning</span>
                <h2 class="text-xl font-bold text-white">Confirm Deletion</h2>
            </div>
            <p class="mt-4 text-sm text-[#9dabb9]">
                Are you sure you want to delete <strong id="delete-resident-name" class="text-white">...</strong>?
                This is a "soft delete" and can be recovered from the database if needed.
            </p>
            <div class="mt-6 flex justify-end gap-4">
                <button type="button" class="close-delete-modal-btn flex-1 rounded-lg bg-[#283039] px-4 py-2.5 text-sm font-medium text-white hover:bg-[#3b4754]">
                    Cancel
                </button>
                <button type="button" id="confirm-delete-resident-btn" class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700">
                    Confirm Delete
                </button>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/household_details.js"></script>

</body>
</html>