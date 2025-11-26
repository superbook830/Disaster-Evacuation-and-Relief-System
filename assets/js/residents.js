$(document).ready(function() {

    // --- 1. FUNCTION TO LOAD THE 4 BIG STATS ---
    function loadDashboardStats() {
        $.ajax({
            url: 'api/resident/get_resident_stats.php', 
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#stats-total-households').text(data.total_households);
                $('#stats-total-residents').text(data.total_residents);
                $('#stats-affected-households').text(data.affected_households);
                $('#stats-residents-evacuated').text(data.residents_evacuated);
            },
            error: function(xhr, status, error) {
                console.error("Error loading resident stats:", error);
            }
        });
    }

    // --- 2. FUNCTION TO LOAD THE "ALL HOUSEHOLDS" TABLE ---
    function loadHouseholds() {
        const $tableBody = $('#households-table-body');
        $tableBody.html('<tr><td colspan="5" class="h-[60px] px-4 py-2 text-center text-[#9dabb9] text-sm">Loading households...</td></tr>');

        $.ajax({
            url: 'api/resident/get_households.php', 
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $tableBody.empty();
                if (response.success && response.households.length > 0) {
                    response.households.forEach(function(hh) {
                        
                        // "No Loophole" Fix: Added 'delete-btn' class and 'data-id'
                        const row = `
                            <tr class="border-t border-t-[#3b4754]">
                                <td class="h-[60px] px-4 py-2 text-white text-sm">${hh.id}</td>
                                <td class="h-[60px] px-4 py-2 text-white text-sm font-medium">${hh.household_head_name}</td>
                                <td class="h-[60px] px-4 py-2 text-[#9dabb9] text-sm">${hh.zone_purok}</td>
                                <td class="h-[60px] px-4 py-2 text-white text-sm">${hh.member_count}</td>
                                <td class="h-[60px] px-4 py-2 text-sm">
                                    <button class="text-primary hover:underline mr-3">View</button>
                                    <button class="delete-btn text-red-400 hover:underline" data-id="${hh.id}">Delete</button>
                                </td>
                            </tr>
                        `;
                        $tableBody.append(row);
                    });
                } else {
                    $tableBody.html('<tr><td colspan="5" class="h-[60px] px-4 py-2 text-center text-[#9dabb9] text-sm">No households found.</td></tr>');
                }
            },
            error: function() {
                $tableBody.html('<tr><td colspan="5" class="h-[60px] px-4 py-2 text-center text-red-400">Error loading households.</td></tr>');
            }
        });
    }
    
    // --- 3. FUNCTION TO HANDLE "ADD NEW HOUSEHOLD" ---
    $('#add-household-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $message = $('#form-message');
        const formData = $form.serialize();

        $.ajax({
            url: 'api/resident/add_household.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $message.text(response.message).removeClass('text-red-400').addClass('text-green-400').removeClass('hidden');
                    $form[0].reset(); // Clear the form
                    loadHouseholds(); // Refresh the table
                    loadDashboardStats(); // Refresh the stats
                } else {
                    $message.text(response.message).removeClass('text-green-400').addClass('text-red-400').removeClass('hidden');
                }
            },
            error: function() {
                $message.text('A system error occurred.').removeClass('text-green-400').addClass('text-red-400').removeClass('hidden');
            }
        });
    });
    
    // --- 4. NEW: CLICK HANDLER FOR "DELETE" BUTTON ---
    $('#households-table-body').on('click', '.delete-btn', function() {
        const householdId = $(this).data('id');
        $('#delete_household_id').val(householdId);
        $('#delete-modal-message').text('').addClass('hidden');
        $('#delete-modal').removeClass('hidden');
    });

    // --- 5. NEW: CLICK HANDLER FOR "CANCEL DELETE" ---
    $('.cancel-delete-modal-btn').on('click', function() {
        $('#delete-modal').addClass('hidden');
    });

    // --- 6. NEW: CLICK HANDLER FOR "CONFIRM DELETE" ---
    $('#confirm-delete-btn').on('click', function() {
        const householdId = $('#delete_household_id').val();
        const $messageDiv = $('#delete-modal-message');

        $.ajax({
            url: 'api/resident/delete_household.php',
            type: 'POST',
            data: { household_id: householdId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $messageDiv.text(response.message).removeClass('text-red-400').addClass('text-green-400').removeClass('hidden');
                    
                    // === THIS IS THE FIX ===
                    loadHouseholds();     // 1. Reload the table
                    loadDashboardStats(); // 2. Reload the stats
                    // =======================

                    setTimeout(function() {
                        $('#delete-modal').addClass('hidden');
                    }, 1500);
                } else {
                    $messageDiv.text(response.message).removeClass('text-green-400').addClass('text-red-400').removeClass('hidden');
                }
            },
            error: function() {
                $messageDiv.text('A system error occurred.').removeClass('text-green-400').addClass('text-red-400').removeClass('hidden');
            }
        });
    });

    // --- 7. INITIAL PAGE LOAD ---
    loadDashboardStats();
    loadHouseholds();

});