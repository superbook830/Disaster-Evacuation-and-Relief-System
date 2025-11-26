
$(document).ready(function() {

    // --- Get Modal Elements ---
    var editModal = $('#edit-household-modal');
    var editFormMessage = $('#edit-form-message');
    // --- NEW! Delete Modal Elements ---
    var deleteModal = $('#delete-confirm-modal');
    var deleteHouseholdName = $('#delete-household-name');
    var confirmDeleteBtn = $('#confirm-delete-btn');

    // --- 1. FUNCTION TO LOAD HOUSEHOLDS ---
    function loadHouseholds() {
        $.ajax({
            url: 'api/resident/get_households.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var tableBody = $('#households-table-body');
                tableBody.empty(); // Clear old data

                data.forEach(function(household) {
                    
                    var manageLink = '<a href="household_details.php?id=' + household.id + 
                                     '" class="text-primary font-medium hover:underline">Manage</a>';
                    
                    var editButton = '<button type="button" class="edit-btn text-yellow-400 font-medium hover:underline ml-4" ' +
                                     'data-id="' + household.id + '" ' +
                                     'data-name="' + escape(household.household_head_name) + '" ' +
                                     'data-zone="' + escape(household.zone_purok) + '" ' +
                                     'data-address="' + escape(household.address_notes) + '">' +
                                     'Edit</button>';
                    
                    var deleteButton = '<button type="button" class="delete-btn text-red-400 font-medium hover:underline ml-4" ' +
                                       'data-id="' + household.id + '" data-name="' + escape(household.household_head_name) + '">' +
                                       'Delete</button>';

                    var row = '<tr class="border-t border-t-[#3b4754]">' +
                        '<td class="h-[60px] px-4 py-2 text-[#9dabb9] text-sm">' + household.id + '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-white text-sm">' + household.household_head_name + '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-[#9dabb9] text-sm">' + household.zone_purok + '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-[#9dabb9] text-sm">' + household.member_count + '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-sm">' + manageLink + editButton + deleteButton + '</td>' +
                        '</tr>';
                    
                    tableBody.append(row);
                });
            },
            error: function(xhr, status, error) {
                console.error("Error loading households:", error);
            }
        });
    }

    // --- 2. FUNCTION TO LOAD DASHBOARD STATS ---
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

    // --- 3. SUBMIT HANDLER FOR ADDING HOUSEHOLD ---
    $('#add-household-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'api/resident/add_household.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                var messageDiv = $('#form-message');
                if (response.success) {
                    messageDiv.text(response.message).removeClass('text-red-400').addClass('text-green-400');
                    $('#add-household-form')[0].reset(); // Clear the form
                    loadHouseholds(); // Reload the table!
                    loadDashboardStats(); // Reload stats
                } else {
                    messageDiv.text(response.message).removeClass('text-green-400').addClass('text-red-400');
                }
            },
            error: function() {
                $('#form-message').text('A system error occurred.').removeClass('text-green-400').addClass('text-red-400');
            }
        });
    });
    
    // --- 4. "DELETE" BUTTON HANDLER (UPDATED) ---
    // This now just shows the modal
    $('#households-table-body').on('click', '.delete-btn', function() {
        var householdId = $(this).data('id');
        var householdName = unescape($(this).data('name'));

        // Set the modal's text and store the ID on the confirm button
        deleteHouseholdName.text(householdName);
        confirmDeleteBtn.data('id', householdId); // Store the ID
        
        // Show the modal
        deleteModal.removeClass('hidden').addClass('flex');
    });

    // --- 5. "EDIT" BUTTON HANDLER (Show Modal) ---
    $('#households-table-body').on('click', '.edit-btn', function() {
        // Get data from the button's data-* attributes
        var id = $(this).data('id');
        var name = unescape($(this).data('name'));
        var zone = unescape($(this).data('zone'));
        var address = unescape($(this).data('address'));

        // Pre-fill the modal's form
        $('#edit_household_id').val(id);
        $('#edit_head_name').val(name);
        $('#edit_zone').val(zone);
        $('#edit_address').val(address);
        
        editFormMessage.empty().removeClass('text-red-400 text-green-400');

        // Show the modal
        editModal.removeClass('hidden').addClass('flex');
    });

    // --- 6. "CLOSE EDIT MODAL" BUTTON HANDLER ---
    $('.close-modal-btn').on('click', function() {
        editModal.removeClass('flex').addClass('hidden');
    });

    // --- 7. "SAVE CHANGES" (SUBMIT EDIT FORM) HANDLER ---
    $('#edit-household-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            url: 'api/resident/update_household.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    editFormMessage.text(response.message).removeClass('text-red-400').addClass('text-green-400');
                    
                    setTimeout(function() {
                        editModal.removeClass('flex').addClass('hidden');
                    }, 1000);
                    
                    loadHouseholds(); // Reload the table
                } else {
                    editFormMessage.text(response.message).removeClass('text-green-400').addClass('text-red-400');
                }
            },
            error: function() {
                editFormMessage.text('A system error occurred.').removeClass('text-green-400').addClass('text-red-400');
            }
        });
    });


    // --- 8. INITIAL PAGE LOAD ---
    loadHouseholds();
    loadDashboardStats();


    // --- 9. NEW! DELETE CONFIRMATION HANDLERS ---
    
    // "Cancel" button on the delete modal
    $('.close-delete-modal-btn').on('click', function() {
        deleteModal.removeClass('flex').addClass('hidden');
    });

    // "Confirm Delete" button
    $('#confirm-delete-btn').on('click', function() {
        var householdId = $(this).data('id'); // Get the stored ID
        
        $.ajax({
            url: 'api/resident/delete_household.php',
            type: 'POST',
            data: { id: householdId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    deleteModal.removeClass('flex').addClass('hidden');
                    loadHouseholds(); // Reload the table
                    loadDashboardStats(); // Reload stats
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('A system error occurred.');
            }
        });
    });

});