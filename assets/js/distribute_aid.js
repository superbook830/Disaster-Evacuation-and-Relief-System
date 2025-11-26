$(document).ready(function() {
    
    // --- 1. GLOBAL VARS ---
    const $itemId = $('#item_id').val();
    const $formMessage = $('#form-message');
    const $confirmBtn = $('#confirm-distribution-btn');
    const $noHouseholdMsg = $('#no-household-selected');
    
    // --- NEW: This holds the list of selected households ---
    let selectedHouseholds = [];
    const $selectedHouseholdsList = $('#selected-households-list');

    // --- 2. INITIAL PAGE LOAD ---
    function loadPageData() {
        if (!$itemId) {
            $('main').html('<h1 class="text-2xl font-bold text-red-400">Error: No item ID specified.</h1>');
            return;
        }

        $.ajax({
            url: `api/relief/get_item_details.php?item_id=${$itemId}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.item) {
                    $('#item-name-title').text(`Distributing: ${response.item.item_name}`);
                    $('#item-stock-count').text(response.item.stock_quantity);
                    $('#item-stock-unit').text(response.item.unit_of_measure);
                    $('#quantity').attr('max', response.item.stock_quantity);
                    
                    // Populate log table
                    const $logBody = $('#log-table-body');
                    $logBody.empty();
                    if (response.log.length > 0) {
                        response.log.forEach(function(log) {
                            const date = new Date(log.distribution_date);
                            const formattedTime = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                            const row = `
                                <tr class="border-t border-t-[#3b4754]">
                                    <td class="h-[50px] px-4 py-2 text-white text-sm">${log.household_head_name}</td>
                                    <td class="h-[50px] px-4 py-2 text-white text-sm font-bold">${log.quantity}</td>
                                    <td class="h-[50px] px-4 py-2 text-[#9dabb9] text-sm">${formattedTime}</td>
                                </tr>`;
                            $logBody.append(row);
                        });
                    } else {
                        $logBody.html('<tr><td colspan="3" class="h-[50px] px-4 py-2 text-center text-[#9dabb9] text-sm">No distributions logged for this item yet.</td></tr>');
                    }
                } else {
                    $formMessage.text(response.message || 'Error loading item data.').addClass('text-red-400');
                }
            },
            error: function() {
                 $formMessage.text('System error loading page data.').addClass('text-red-400');
            }
        });
    }

    // --- 3. HOUSEHOLD SEARCH ---
    $('#search-household-form').on('submit', function(e) {
        e.preventDefault();
        const searchTerm = $('#search_name').val();
        const $resultsMsg = $('#search-results-message');
        const $resultsContainer = $('#search-results-container');
        
        $resultsMsg.text('Searching...').removeClass('text-red-400 text-green-400');
        $resultsContainer.empty();
        
        // ** NOTE: This function no longer clears the selection **

        $.ajax({
            url: `api/relief/search_households.php?search=${searchTerm}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.households.length > 0) {
                    $resultsMsg.text(`${response.households.length} household(s) found.`).addClass('text-green-400');
                    response.households.forEach(function(hh) {
                        
                        // Check if this household is *already* in our selected list
                        const isAlreadySelected = selectedHouseholds.some(selected => selected.id === hh.id);

                        const resultHTML = `
                            <div class="p-3 bg-[#283039] rounded-lg border border-transparent 
                                 ${isAlreadySelected ? 'opacity-50 cursor-not-allowed' : 'hover:border-primary cursor-pointer select-household-btn'}" 
                                 data-id="${hh.id}" 
                                 data-name="${hh.household_head_name.replace(/"/g, '&quot;')}"
                                 data-zone="${hh.zone_purok ? hh.zone_purok.replace(/"/g, '&quot;') : ''}">
                                
                                <p class="text-white font-medium">${hh.household_head_name}</p>
                                
                                <div class="flex justify-between items-center mt-1">
                                    <p class="text-sm text-[#9dabb9]">${hh.zone_purok}</p>
                                    <span class="text-xs text-white bg-primary/20 px-2 py-0.5 rounded-full">${hh.member_count} members</span>
                                </div>
                                ${isAlreadySelected ? '<p class="text-xs text-yellow-400 mt-1">Already in list</p>' : ''}
                            </div>`;
                            
                        $resultsContainer.append(resultHTML);
                    });
                } else {
                    $resultsMsg.text(response.message || 'No households found.').addClass('text-red-400');
                }
            },
            error: function() {
                $resultsMsg.text('Error searching households.').addClass('text-red-400');
            }
        });
    });

    // --- 4. NEW: RENDER THE SELECTED HOUSEHOLDS LIST ---
    function renderSelectedList() {
        $selectedHouseholdsList.empty(); // Clear the list

        if (selectedHouseholds.length === 0) {
            $noHouseholdMsg.removeClass('hidden');
            $confirmBtn.prop('disabled', true); // Disable confirm button
        } else {
            $noHouseholdMsg.addClass('hidden');
            $confirmBtn.prop('disabled', false); // Enable confirm button
            
            selectedHouseholds.forEach(function(hh) {
                const selectedHTML = `
                    <div class="p-3 bg-[#283039] rounded-lg flex items-center justify-between">
                        <div>
                            <p class="text-white font-medium">${hh.name}</p>
                            <p class="text-sm text-[#9dabb9]">${hh.zone}</p>
                        </div>
                        <button type="button" class="remove-household-btn text-red-400 hover:text-red-600" data-id="${hh.id}">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>`;
                $selectedHouseholdsList.append(selectedHTML);
            });
        }
    }

    // --- 5. ADD A HOUSEHOLD TO THE LIST ---
    $('#search-results-container').on('click', '.select-household-btn', function() {
        const $this = $(this);
        const id = $this.data('id');
        
        // Prevent re-adding
        if (selectedHouseholds.some(hh => hh.id === id)) {
            return;
        }

        // Add to our array
        selectedHouseholds.push({
            id: id,
            name: $this.data('name'),
            zone: $this.data('zone')
        });
        
        // Re-render the selected list
        renderSelectedList();

        // Clear search results
        $('#search-results-message').text('');
        $('#search-results-container').empty();
        $('#search_name').val('');
    });

    // --- 6. REMOVE A HOUSEHOLD FROM THE LIST ---
    $selectedHouseholdsList.on('click', '.remove-household-btn', function() {
        const idToRemove = $(this).data('id');
        // Filter the array to keep everyone *except* this ID
        selectedHouseholds = selectedHouseholds.filter(hh => hh.id !== idToRemove);
        // Re-render the list
        renderSelectedList();
    });

    // --- 7. CLEAR ENTIRE SELECTION ---
    $('#clear-selection-btn').on('click', function() {
        selectedHouseholds = []; // Empty the array
        renderSelectedList(); // Re-render (which will show the "empty" message)
    });

    // --- 8. CONFIRM DISTRIBUTION ---
    $confirmBtn.on('click', function() {
        // Get all the IDs from our array
        const householdIds = selectedHouseholds.map(hh => hh.id);
        const quantity = $('#quantity').val();
        
        if (householdIds.length === 0) {
            $formMessage.text('Please select at least one household.').addClass('text-red-400');
            return;
        }

        $formMessage.text('Processing...').removeClass('text-red-400 text-green-400');

        $.ajax({
            url: 'api/relief/log_distribution.php',
            type: 'POST',
            data: {
                item_id: $itemId,
                household_ids: householdIds, // Send the array
                quantity_per_household: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $formMessage.text(response.message).addClass('text-green-400');
                    // Reset the form
                    $('#clear-selection-btn').click();
                    $('#quantity').val(1);
                    // Reload all page data to show new stock and log
                    loadPageData();
                } else {
                    $formMessage.text(response.message).addClass('text-red-400');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                $formMessage.text('A system error occurred. Check the console.').addClass('text-red-400');
            }
        });
    });

    // --- 9. INITIAL PAGE LOAD ---
    loadPageData();
    renderSelectedList(); // Call this once to set the initial "empty" state
});