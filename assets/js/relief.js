$(document).ready(function() {

    // --- 1. FUNCTION TO LOAD INVENTORY ITEMS ---
    function loadItems() {
        $.ajax({
            url: 'api/relief/get_items.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Check if the server response indicates success and has items
                if (data.success && data.items) {
                    var tableBody = $('#items-table-body');
                    tableBody.empty(); // Clear old data

                    if (data.items.length === 0) {
                        tableBody.html('<tr><td colspan="5" class="h-[60px] px-4 py-2 text-center text-[#9dabb9] text-sm">No items in inventory.</td></tr>');
                        return;
                    }

                    data.items.forEach(function(item) {
                        
                        var unit = item.unit_of_measure ? item.unit_of_measure : 'N/A';
                        var desc = item.description ? item.description : 'N/A';
                        if(desc.length > 50) desc = desc.substring(0, 50) + '...'; // Snippet

                        // === "No Loophole" Fix: The "Distribute" button is now active ===
                        var distributeLink = '<a href="distribute_aid.php?item_id=' + item.id + 
                                             '" class="text-primary text-sm font-bold hover:underline mr-3">Distribute</a>';
                        
                        var editButton = '<button type="button" class="text-yellow-400 text-sm font-bold hover:underline edit-btn mr-3" ' +
                                         'data-id="' + item.id + '" ' +
                                         'data-name="' + (item.item_name || '').replace(/"/g, '&quot;') + '" ' +
                                         'data-unit="' + (unit || '').replace(/"/g, '&quot;') + '" ' +
                                         'data-stock="' + item.stock_quantity + '" ' +
                                         'data-description="' + (item.description || '').replace(/"/g, '&quot;') + '" ' +
                                         '>Edit</button>';

                        var deleteButton = '<button type="button" class="text-red-400 text-sm font-bold hover:underline delete-btn" ' +
                                             'data-id="' + item.id + '">Delete</button>';

                        var stockColor = 'text-white';
                        if (item.stock_quantity == 0) {
                            stockColor = 'text-red-400';
                        } else if (item.stock_quantity < 20) {
                            stockColor = 'text-yellow-400';
                        }

                        // === "No Loophole" Fix: The 'distributeLink' variable is now added to the row ===
                        var row = '<tr class="border-t border-t-[#3b4754]">' +
                            '<td class="h-[60px] px-4 py-2 text-white text-sm">' + item.item_name + '</td>' +
                            '<td class="h-[60px] px-4 py-2 text-sm font-bold ' + stockColor + '">' + item.stock_quantity + '</td>' +
                            '<td class="h-[60px] px-4 py-2 text-[#9dabb9] text-sm">' + unit + '</td>' +
                            '<td class="h-[60px] px-4 py-2 text-[#9dabb9] text-sm">' + desc + '</td>' +
                            '<td class="h-[60px] px-4 py-2 text-sm">' + distributeLink + editButton + deleteButton + '</td>' +
                            '</tr>';
                        
                        tableBody.append(row);
                    });
                } else {
                     $('#items-table-body').html('<tr><td colspan="5" class="h-[60px] px-4 py-2 text-center text-[#9dabb9] text-sm">No items found.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error loading items:", error);
                 $('#items-table-body').html('<tr><td colspan="5" class="h-[60px] px-4 py-2 text-center text-red-400">Error loading data.</td></tr>');
            }
        });
    }

    // --- 2. SUBMIT HANDLER FOR ADDING A NEW ITEM ---
    $('#add-item-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'api/relief/add_item.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                var messageDiv = $('#form-message');
                if (response.success) {
                    messageDiv.text(response.message).removeClass('text-red-400').addClass('text-green-400');
                    $('#add-item-form')[0].reset(); 
                    loadItems(); 
                } else {
                    messageDiv.text(response.message).removeClass('text-green-400').addClass('text-red-400');
                }
            },
            error: function() {
                $('#form-message').text('A system error occurred.').removeClass('text-green-400').addClass('text-red-400');
            }
        });
    });

    // --- 3. INITIAL LOAD ---
    loadItems();

    // --- 4. CLICK HANDLER FOR "EDIT" BUTTON (Show Modal) ---
    $('#items-table-body').on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var unit = $(this).data('unit');
        var stock = $(this).data('stock');
        var description = $(this).data('description');

        $('#edit_item_id').val(id);
        $('#edit_item_name').val(name);
        $('#edit_unit_of_measure').val(unit);
        $('#edit_stock_quantity').val(stock);
        $('#edit_description').val(description);
        $('#edit-modal-message').text('');
        $('#edit-modal').removeClass('hidden');
    });

    // --- 5. CLICK HANDLER FOR "CANCEL" BUTTONS (Hide Edit Modal) ---
    $('.cancel-modal-btn').on('click', function() {
        $('#edit-modal').addClass('hidden');
    });

    // --- 6. SUBMIT HANDLER FOR EDIT ITEM FORM ---
    $('#edit-item-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'api/relief/update_item.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                var messageDiv = $('#edit-modal-message');
                if (response.success) {
                    messageDiv.text(response.message).removeClass('text-red-400').addClass('text-green-400');
                    loadItems(); 
                    
                    setTimeout(function() {
                        $('#edit-modal').addClass('hidden');
                    }, 1500);

                } else {
                    messageDiv.text(response.message).removeClass('text-green-400').addClass('text-red-400');
                }
            },
            error: function() {
                $('#edit-modal-message').text('A system error occurred.').removeClass('text-green-400').addClass('text-red-400');
            }
        });
    });

    // --- 7. MODIFIED: CLICK HANDLER FOR "DELETE" BUTTON (Show Delete Modal) ---
    $('#items-table-body').on('click', '.delete-btn', function() {
        var itemId = $(this).data('id');
        
        $('#delete_item_id').val(itemId);
        $('#delete-modal-message').text('');
        $('#delete-modal').removeClass('hidden');
    });

    // --- 8. NEW: CLICK HANDLER FOR "CANCEL" BUTTONS (Hide Delete Modal) ---
    $('.cancel-delete-modal-btn').on('click', function() {
        $('#delete-modal').addClass('hidden');
    });

    // --- 9. NEW: CLICK HANDLER FOR "CONFIRM DELETE" BUTTON ---
    $('#confirm-delete-btn').on('click', function() {
        var itemId = $('#delete_item_id').val();
        var messageDiv = $('#delete-modal-message');

        $.ajax({
            url: 'api/relief/delete_item.php',
            type: 'POST',
            data: { item_id: itemId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    messageDiv.text(response.message).removeClass('text-red-400').addClass('text-green-400');
                    loadItems();
                    
                    setTimeout(function() {
                        $('#delete-modal').addClass('hidden');
                    }, 1500);

                } else {
                    messageDiv.text(response.message).removeClass('text-green-400').addClass('text-red-400');
                }
            },
            error: function() {
                messageDiv.text('A system error occurred.').removeClass('text-green-400').addClass('text-red-400');
            }
        });
    });

});