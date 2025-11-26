$(document).ready(function() {

    // --- 1. FUNCTION TO LOAD THE 4 BIG STATS ---
    function loadDashboardStats() {
        $.ajax({
            // === FIX 1: Use the correct URL we fixed before ===
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
                // We can leave the "0"s on the page if this fails
            }
        });
    }

    // --- 2. FUNCTION TO LOAD EVACUATION CENTER STATUS ---
    function loadCenterStatus() {
        $.ajax({
            url: 'api/evacuation/get_centers.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var tableBody = $('#centers-table-body');
                tableBody.empty(); // Clear old data

                if (data.length === 0) {
                     tableBody.append('<tr class="border-t border-t-[#3b4754]"><td colspan="3" class="h-[60px] px-4 py-2 text-center text-[#9dabb9] text-sm">No evacuation centers found.</td></tr>');
                     return;
                }

                data.forEach(function(center) {
                    var statusColor = center.is_active == 1 ? 'text-green-400' : 'text-red-400';
                    var statusText = center.is_active == 1 ? 'Active' : 'Inactive';
                    var occupancy = center.current_occupancy + ' / ' + center.capacity;

                    var row = '<tr class="border-t border-t-[#3b4754]">' +
                        '<td class="h-[60px] px-4 py-2 text-white text-sm">' + center.center_name + '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-sm font-medium ' + statusColor + '">' + statusText + '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-white text-sm">' + occupancy + '</td>' +
                        '</tr>';
                    tableBody.append(row);
                });
            },
            error: function(xhr, status, error) {
                console.error("Error loading centers:", error);
                $('#centers-table-body').append('<tr class="border-t border-t-[#3b4754]"><td colspan="3" class="h-[60px] px-4 py-2 text-center text-red-400">Could not load centers.</td></tr>');
            }
        });
    }

    // --- 3. FUNCTION TO LOAD INVENTORY STOCK (FIXED!) ---
    function loadInventoryStatus() {
        $.ajax({
            url: 'api/relief/get_items.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var tableBody = $('#inventory-table-body');
                tableBody.empty(); // Clear old data

                // === FIX 1: Check for data.success and data.items ===
                if (!data.success || !data.items || data.items.length === 0) {
                     tableBody.append('<tr class="border-t border-t-[#3b4754]"><td colspan="3" class="h-[60px] px-4 py-2 text-center text-[#9dabb9] text-sm">No inventory items found.</td></tr>');
                     return;
                }

                // === FIX 2: Loop over data.items, not data ===
                data.items.forEach(function(item) {
                    // Set stock color
                    var stockColor = 'text-white';
                    if (item.stock_quantity == 0) {
                        stockColor = 'text-red-400';
                    } else if (item.stock_quantity < 20) {
                        stockColor = 'text-yellow-400';
                    }
                    
                    // === FIX 3: Make sure unit_of_measure is not null ===
                    var unit = item.unit_of_measure ? item.unit_of_measure : 'N/A';

                    var row = '<tr class="border-t border-t-[#3b4754]">' +
                        '<td class="h-[60px] px-4 py-2 text-white text-sm">' + item.item_name + '</td>' +
                        '<td class="h-[60px] px-4 py-2 text-sm font-bold ' + stockColor + '">' + item.stock_quantity + '</td>' +
                        '<td class="h-[6G0px] px-4 py-2 text-[#9dabb9] text-sm">' + unit + '</td>' +
                        '</tr>';
                    tableBody.append(row);
                });
            },
            error: function(xhr, status, error) {
                console.error("Error loading items:", error);
                $('#inventory-table-body').append('<tr class="border-t border-t-[#3b4754]"><td colspan="3" class="h-[60px] px-4 py-2 text-center text-red-400">Error loading inventory.</td></tr>');
            }
        });
    }

    // --- 4. INITIAL LOAD ---
    function loadAllData() {
        // === FIX 2: UN-COMMENT THESE LINES ===
        loadDashboardStats();
        loadCenterStatus();
        
        // We will ONLY load the inventory, which we know works.
        loadInventoryStatus();
    }
    
    loadAllData();

    // --- 5. (OPTIONAL) AUTO-REFRESH ---
    // setInterval(loadAllData, 30000); // 30000 milliseconds = 30 seconds

});