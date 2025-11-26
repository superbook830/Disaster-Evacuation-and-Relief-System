$(document).ready(function() {

    // Get the center_id from the hidden input
    var centerId = $('#center_id').val();

    // --- NEW! Get Modal Elements ---
    var checkOutModal = $('#check-out-modal');
    var confirmCheckOutBtn = $('#confirm-checkout-btn');
    var cancelCheckOutBtn = $('.cancel-checkout-btn');


    // --- 1. FUNCTION TO LOAD CENTER DETAILS & EVACUEES ---
    function loadCenterData() {
        if (!centerId) {
            console.error("No Center ID provided!");
            return;
        }

        $.ajax({
            url: 'api/evacuation/get_center_details.php?id=' + centerId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                
                // --- Fill Header ---
                if (data.center_info) {
                    var info = data.center_info;
                    $('#center-name-title').text(info.center_name);
                    $('#center-occupancy-stats').text(
                        '(' + info.current_occupancy + ' / ' + info.capacity + ' Occupied)'
                    );
                }

                // --- Fill Current Evacuees Table ---
                var tableBody = $('#evacuees-table-body');
                tableBody.empty(); // Clear old data

                if (data.current_evacuees.length > 0) {
                    data.current_evacuees.forEach(function(evacuee) {
                        
                        // This button now has the class "check-out-btn"
                        var checkOutButton = '<button class="check-out-btn text-red-400 text-sm font-bold hover:underline" data-record-id="' + evacuee.evacuee_record_id + '">Check-Out</button>';
                        
                        var row = '<tr class="border-t border-t-[#3b4754]">' +
                            '<td class="h-[60px] px-4 py-2 text-white text-sm">' + evacuee.last_name + ', ' + evacuee.first_name + '</td>' +
                            '<td class="h-[60px] px-4 py-2 text-[#9dabb9] text-sm">' + evacuee.time_checked_in + '</td>' +
                            '<td class="h-[60px] px-4 py-2 text-sm">' + checkOutButton + '</td>' +
                            '</tr>';
                        
                        tableBody.append(row);
                    });
                } else {
                    var row = '<tr class="border-t border-t-[#3b4754]">' +
                        '<td colspan="3" class="h-[60px] px-4 py-2 text-center text-[#9dabb9] text-sm">No residents are currently checked in at this center.</td>' +
                        '</tr>';
                    tableBody.append(row);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error loading center details:", error);
                $('#center-name-title').text('Error loading data');
            }
        });
    }

    // --- 2. SEARCH FORM HANDLER ---
    $('#search-resident-form').on('submit', function(e) {
        e.preventDefault();
        var searchTerm = $('#search_name').val();
        var resultsContainer = $('#search-results-container');
        var messageDiv = $('#search-results-message');

        resultsContainer.empty();
        messageDiv.empty();

        if (searchTerm.length < 2) {
            messageDiv.text('Please type at least 2 letters.').addClass('text-yellow-400');
            return;
        }

        $.ajax({
            url: 'api/resident/search_residents.php',
            type: 'GET',
            data: { term: searchTerm },
            dataType: 'json',
            success: function(data) {
                if (data.length > 0) {
                    messageDiv.text(data.length + ' resident(s) found.').removeClass('text-red-400').addClass('text-green-400');
                    data.forEach(function(res) {
                        var residentHtml = '<div class="flex items-center justify-between p-3 bg-[#283039] rounded-lg">' +
                            '<div>' +
                                '<p class="text-white font-medium">' + res.last_name + ', ' + res.first_name + '</p>' +
                                '<p class="text-sm text-[#9dabb9]">Household: ' + res.household_head_name + '</p>' +
                            '</div>' +
                            '<button class="check-in-btn flex-shrink-0 bg-green-500 text-white px-3 py-1 rounded-lg text-sm font-bold" data-resident-id="' + res.id + '">Check-In</button>' +
                        '</div>';
                        resultsContainer.append(residentHtml);
                    });
                } else {
                    messageDiv.text('No available residents found with that name.').removeClass('text-green-400').addClass('text-red-400');
                }
            },
            error: function() {
                messageDiv.text('Error searching for residents.').addClass('text-red-400');
            }
        });
    });

    // --- 3. CHECK-IN BUTTON HANDLER ---
    $('#search-results-container').on('click', '.check-in-btn', function() {
        var residentId = $(this).data('resident-id');
        var button = $(this);
        var messageDiv = $('#search-results-message');

        button.prop('disabled', true).text('Checking in...');

        $.ajax({
            url: 'api/evacuation/check_in_resident.php',
            type: 'POST',
            data: {
                resident_id: residentId,
                center_id: centerId 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    messageDiv.text(response.message).removeClass('text-red-400').addClass('text-green-400');
                    $('#search-results-container').empty();
                    $('#search_name').val('');
                    loadCenterData(); // Reload everything
                } else {
                    messageDiv.text(response.message).removeClass('text-green-400').addClass('text-red-400');
                    button.prop('disabled', false).text('Check-In');
                }
            },
            error: function() {
                messageDiv.text('A system error occurred.').addClass('text-red-400');
                button.prop('disabled', false).text('Check-In');
            }
        });
    });
    

    // --- 4. CHECK-OUT BUTTON HANDLER (MODIFIED) ---
    // This now *shows* the modal
    $('#evacuees-table-body').on('click', '.check-out-btn', function() {
        // Get the record ID and name
        var recordId = $(this).data('record-id');
        var residentName = $(this).closest('tr').find('td:first').text();
        
        // Put the name and ID into the modal
        checkOutModal.find('.resident-name').text(residentName);
        confirmCheckOutBtn.data('record-id', recordId); // Store the ID on the confirm button
        
        // Show the modal
        checkOutModal.removeClass('hidden').addClass('flex');
    });

    // --- 5. NEW! MODAL CANCEL BUTTON HANDLER ---
    cancelCheckOutBtn.on('click', function() {
        // Just hide the modal
        checkOutModal.addClass('hidden').removeClass('flex');
    });

    // --- 6. NEW! MODAL CONFIRM BUTTON HANDLER ---
    // This now runs the AJAX call
    confirmCheckOutBtn.on('click', function() {
        var recordId = $(this).data('record-id'); // Get the stored ID
        var button = $(this);

        button.prop('disabled', true).text('Checking out...');

        $.ajax({
            url: 'api/evacuation/check_out_resident.php',
            type: 'POST',
            data: { record_id: recordId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Success! Hide the modal and reload the data
                    checkOutModal.addClass('hidden').removeClass('flex');
                    loadCenterData();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('A system error occurred.');
            },
            complete: function() {
                // Always re-enable the button
                button.prop('disabled', false).text('Confirm Check-Out');
            }
        });
    });


    // --- 7. INITIAL LOAD ---
    // Load the data as soon as the page opens
    loadCenterData();

});