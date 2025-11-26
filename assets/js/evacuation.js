$(document).ready(function() {

    // --- 1. FUNCTION TO LOAD EVACUATION CENTERS ---
    function loadCenters() {
        $.ajax({
            url: 'api/evacuation/get_centers.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var tableBody = $('#centers-table-body');
                tableBody.empty();

                data.forEach(function(center) {
                    // --- Action Buttons (same style as Relief Inventory design) ---
                    var checkInLink = `
                        <a href="check_in.php?id=${center.id}"
                           class="text-primary text-sm font-bold hover:underline mr-3">
                            Check-In
                        </a>`;

                    var editButton = `
                        <button type="button"
                                class="text-yellow-400 text-sm font-bold hover:underline edit-btn mr-3"
                                data-id="${center.id}"
                                data-name="${center.center_name.replace(/"/g, '&quot;')}"
                                data-address="${center.address ? center.address.replace(/"/g, '&quot;') : ''}"
                                data-capacity="${center.capacity}"
                                data-active="${center.is_active}">
                            Edit
                        </button>`;

                    var deleteButton = `
                        <button type="button"
                                class="text-red-400 text-sm font-bold hover:underline delete-btn"
                                data-id="${center.id}">
                            Delete
                        </button>`;

                    // --- Status Color ---
                    var statusColor = center.is_active == 1 ? 'text-green-400' : 'text-red-400';
                    var statusText = center.is_active == 1 ? 'Active' : 'Inactive';

                    // --- Row Template ---
                    var row = `
                        <tr class="border-t border-t-[#3b4754]">
                            <td class="h-[60px] px-4 py-2 text-white text-sm">${center.center_name}</td>
                            <td class="h-[60px] px-4 py-2 text-sm font-medium ${statusColor}">${statusText}</td>
                            <td class="h-[60px] px-4 py-2 text-white text-sm">${center.current_occupancy} / ${center.capacity}</td>
                            <td class="h-[60px] px-4 py-2 text-white text-sm font-bold">${center.remaining_capacity}</td>
                            <td class="h-[60px] px-4 py-2 text-[#9dabb9] text-sm">${center.address}</td>
                            <td class="h-[60px] px-4 py-2 text-sm flex gap-3">
                                ${checkInLink}
                                ${editButton}
                                ${deleteButton}
                            </td>
                        </tr>`;
                    
                    tableBody.append(row);
                });
            },
            error: function(xhr, status, error) {
                console.error("Error loading centers:", error);
            }
        });
    }

    // --- 2. ADD NEW CENTER ---
    $('#add-center-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'api/evacuation/add_center.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                var msg = $('#form-message');
                msg.text(response.message)
                   .removeClass('text-green-400 text-red-400')
                   .addClass(response.success ? 'text-green-400' : 'text-red-400');
                if (response.success) {
                    $('#add-center-form')[0].reset();
                    loadCenters();
                }
            },
            error: function() {
                $('#form-message').text('System error.').addClass('text-red-400');
            }
        });
    });

    // --- 3. EDIT BUTTON CLICK ---
    $('#centers-table-body').on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var address = $(this).data('address');
        var capacity = $(this).data('capacity');
        var active = $(this).data('active');

        $('#edit_center_id').val(id);
        $('#edit_center_name').val(name);
        $('#edit_address').val(address);
        $('#edit_capacity').val(capacity);
        $('#edit_is_active').prop('checked', active == 1);

        $('#edit-modal-message').text('');
        $('#edit-modal').removeClass('hidden');
    });

    // --- 4. CLOSE MODALS ---
    $('.cancel-modal-btn').on('click', function() {
        $('#edit-modal').addClass('hidden');
    });
    $('.cancel-delete-modal-btn').on('click', function() {
        $('#delete-modal').addClass('hidden');
    });

    // --- 5. EDIT FORM SUBMIT ---
    $('#edit-center-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'api/evacuation/update_center.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                var msg = $('#edit-modal-message');
                msg.text(response.message)
                   .removeClass('text-green-400 text-red-400')
                   .addClass(response.success ? 'text-green-400' : 'text-red-400');
                if (response.success) {
                    loadCenters();
                    setTimeout(() => $('#edit-modal').addClass('hidden'), 1500);
                }
            },
            error: function() {
                $('#edit-modal-message').text('System error.').addClass('text-red-400');
            }
        });
    });

    // --- 6. DELETE BUTTON CLICK ---
    $('#centers-table-body').on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#delete_center_id').val(id);
        $('#delete-modal-message').text('');
        $('#delete-modal').removeClass('hidden');
    });

    // --- 7. CONFIRM DELETE ---
    $('#confirm-delete-btn').on('click', function() {
        var centerId = $('#delete_center_id').val();
        var msg = $('#delete-modal-message');
        $.ajax({
            url: 'api/evacuation/delete_center.php',
            type: 'POST',
            data: { id: centerId },
            dataType: 'json',
            success: function(response) {
                msg.text(response.message)
                   .removeClass('text-green-400 text-red-400')
                   .addClass(response.success ? 'text-green-400' : 'text-red-400');
                if (response.success) {
                    loadCenters();
                    setTimeout(() => $('#delete-modal').addClass('hidden'), 1500);
                }
            },
            error: function() {
                msg.text('System error.').addClass('text-red-400');
            }
        });
    });

    // --- 8. INITIAL LOAD ---
    loadCenters();
});
