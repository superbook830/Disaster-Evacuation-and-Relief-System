<?php
include_once 'api/config/session.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Manage Family - Disaster System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;700&display=swap" rel="stylesheet" />
    <style> body { font-family: 'Public Sans', sans-serif; } </style>
</head>
<body class="bg-[#101922] text-white flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-lg bg-[#1a222c] rounded-xl border border-[#283039] p-8">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Add Family Member</h1>
            <a href="my_dashboard.php" class="text-sm text-gray-400 hover:text-white">Cancel</a>
        </div>

        <form id="add-member-form" class="flex flex-col gap-5">
            
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-300">First Name</label>
                    <input type="text" name="first_name" required placeholder="e.g. Maria" 
                        class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-300">Last Name</label>
                    <input type="text" name="last_name" required placeholder="e.g. Balbon" 
                        class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-300">Birthdate</label>
                    <input type="date" name="birthdate" 
                        class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-300">Gender</label>
                    <select name="gender" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-5 py-3 text-center mt-4">
                Add Member
            </button>
        </form>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#add-member-form').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: 'api/resident/add_member.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            alert('Member added successfully!');
                            window.location.href = 'my_dashboard.php'; // Go back to dashboard
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('System error. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>