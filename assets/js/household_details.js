$(document).ready(function() {

    // --- Get Modal Elements ---
    var editModal = $('#edit-resident-modal');
    var editForm = $('#edit-resident-form');
    var editFormMessage = $('#edit-form-message');
    var deleteModal = $('#delete-resident-modal');
    var deleteResidentName = $('#delete-resident-name');
    var confirmDeleteBtn = $('#confirm-delete-resident-btn');

    // --- 1. GET THE HOUSEHOLD ID FROM THE URL ---
    var urlParams = new URLSearchParams(window.location.search);
    var householdId = urlParams.get('id');

    // --- 2. FUNCTION TO LOAD ALL DATA ---
    function loadHouseholdDetails() {
        if (!householdId) {
            console.error("No Household ID provided!");
            return;
        }

        $.ajax({
            url: 'api/resident/get_residents.php?id=' + householdId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Set the page title
                if (data.household_info) {
                    $('#household-name-title').text('Managing Household: ' + data.household_info.household_head_name);
                }

                // Fill the residents table
                var tableBody = $('#residents-table-body');
                tableBody.empty(); // Clear old data

                data.residents.forEach(function(res) {
                    
                    // --- NEW! Action Buttons ---
                    var editButton = '<button type="button" class="edit-resident-btn text-yellow-400 font-medium hover:underline" ' +
                                     'data-id="' + res.id + '" ' +
                                     'data-first_name="' + escape(res.first_name) + '" ' +
                                     'data-last_name="' + escape(res.last_name) + '" ' +
                                     'data-birthdate="' + res.birthdate + '" ' +
                                     'data-gender="' + res.gender + '" ' +
                                     'data-is_pwd="' + res.is_pwd + '" ' +
                                     'data-is_senior="' + res.is_senior + '" ' +
                                     'data-remarks="' + escape(res.remarks) + '">' +
                                     'Edit</button>';
                    
                    var deleteButton = '<button type="button" class="delete-resident-btn text-red-400 font-medium hover:underline ml-4" ' +
                                       'data-id="' + res.id + '" data-name="' + escape(res.first_name + ' ' + res.last_name) + '">' +
                                       'Delete</button>';

                    var row = '<tr class="border-t border-t-[#3b4754]">' +
                        '<td class="h-[60px] px-4 py-2 text-[#9dabb9] text-sm">' + res.id + '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-white text-sm">' + res.first_name + '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-white text-sm">' + res.last_name + '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-[#9dabb9] text-sm">' + (res.birthdate ? res.birthdate : 'N/A') + '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-sm ' + (res.is_pwd == 1 ? 'text-orange-400' : 'text-gray-400') + '">' +
                            (res.is_pwd == 1 ? 'Yes' : 'No') +
                        '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-sm ' + (res.is_senior == 1 ? 'text-orange-400' : 'text-gray-400') + '">' +
                            (res.is_senior == 1 ? 'Yes' : 'No') +
                        '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-sm">' + editButton + deleteButton + '</td>' +
                        '</tr>';
                    tableBody.append(row);
                });
            },
            error: function(xhr, status, error) {
                console.error("Error loading details:", error);
                $('#household-name-title').text('Error loading data.');
            }
        });
    }

    // --- 3. SUBMIT HANDLER FOR ADDING RESIDENTS ---
    $('#add-resident-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'api/resident/add_resident.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                var messageDiv = $('#form-message');
                if (response.success) {
                    messageDiv.text(response.message).removeClass('text-red-400').addClass('text-green-400');
                    $('#add-resident-form')[0].reset(); // Clear the form
                    loadHouseholdDetails(); // Reload the table!
                } else {
                    messageDiv.text(response.message).removeClass('text-green-400').addClass('text-red-400');
                }
            },
            error: function() {
                $('#form-message').text('A system error occurred.').removeClass('text-green-400').addClass('text-red-400');
            }
        });
    });

    // --- 4. NEW! "EDIT RESIDENT" BUTTON HANDLER (Show Modal) ---
    $('#residents-table-body').on('click', '.edit-resident-btn', function() {
        // Get data from the button's data-* attributes
        var id = $(this).data('id');
        var first_name = unescape($(this).data('first_name'));
        var last_name = unescape($(this).data('last_name'));
        var birthdate = $(this).data('birthdate');
        var gender = $(this).data('gender');
        var is_pwd = $(this).data('is_pwd') == 1;
        var is_senior = $(this).data('is_senior') == 1;
        var remarks = unescape($(this).data('remarks'));

        // Pre-fill the modal's form
        $('#edit_resident_id').val(id);
        $('#edit_first_name').val(first_name);
        $('#edit_last_name').val(last_name);
        $('#edit_birthdate').val(birthdate);
        $('#edit_gender').val(gender);
        $('#edit_remarks').val(remarks);
        $('#edit_is_pwd').prop('checked', is_pwd);
        $('#edit_is_senior').prop('checked', is_senior);
        
        editFormMessage.empty().removeClass('text-red-400 text-green-400');
        editModal.removeClass('hidden').addClass('flex');
    });

    // --- 5. NEW! "CLOSE EDIT MODAL" BUTTON HANDLER ---
    $('.close-edit-modal-btn').on('click', function() {
        editModal.removeClass('flex').addClass('hidden');
    });

    // --- 6. NEW! "SAVE CHANGES" (SUBMIT EDIT FORM) HANDLER ---
    $('#edit-resident-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            url: 'api/resident/update_resident.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    editFormMessage.text(response.message).removeClass('text-red-400').addClass('text-green-400');
                    setTimeout(function() {
                        editModal.removeClass('flex').addClass('hidden');
                    }, 1000);
                    loadHouseholdDetails(); // Reload the table
                } else {
                    editFormMessage.text(response.message).removeClass('text-green-400').addClass('text-red-400');
                }
            },
            error: function() {
                editFormMessage.text('A system error occurred.').removeClass('text-green-400').addClass('text-red-400');
            }
        });
    });

    // --- 7. NEW! "DELETE RESIDENT" BUTTON HANDLER (Show Modal) ---
    $('#residents-table-body').on('click', '.delete-resident-btn', function() {
        var residentId = $(this).data('id');
        var residentName = unescape($(this).data('name'));

        deleteResidentName.text(residentName);
        confirmDeleteBtn.data('id', residentId);
        
        deleteModal.removeClass('hidden').addClass('flex');
    });

    // --- 8. NEW! DELETE CONFIRMATION HANDLERS ---
    $('.close-delete-modal-btn').on('click', function() {
        deleteModal.removeClass('flex').addClass('hidden');
    });

    $('#confirm-delete-resident-btn').on('click', function() {
        var residentId = $(this).data('id');
        
        $.ajax({
            url: 'api/resident/delete_resident.php',
            type: 'POST',
            data: { id: residentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    deleteModal.removeClass('flex').addClass('hidden');
                    loadHouseholdDetails(); // Reload the table
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('A system error occurred.');
            }
        });
    });

    // --- 9. INITIAL LOAD ---
    loadHouseholdDetails();

});