$(document).ready(function() {

    // --- 1. Handle Profile Picture Click ---
    $('#upload-pic-btn').on('click', function() {
        $('#profile-pic-input').click();
    });

    // --- 2. Handle File Selection ---
    $('#profile-pic-input').on('change', function() {
        var file_data = $(this).prop('files')[0];
        var formData = new FormData();
        formData.append('profile_pic', file_data);

        $.ajax({
            url: 'api/resident/upload_profile_pic.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('A system error occurred during upload. Please try again.');
            }
        });
    });

    // --- 3. Load Household Data ---
    function loadMyHousehold() {
        $.ajax({
            url: 'api/resident/get_my_household.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var tableBody = $('#my-household-list');
                
                if (response.success) {
                    $('#household-address').text(response.household_info.address_notes);
                    tableBody.empty();
                    
                    if(response.members.length > 0) {
                        response.members.forEach(function(member) {
                            var row = '<tr>' +
                                '<td class="px-4 py-3 text-white text-sm">' + member.first_name + ' ' + member.last_name + '</td>' +
                                '<td class="px-4 py-3 text-slate-300 text-sm">' + (member.birthdate ? member.birthdate : 'N/A') + '</td>' +
                                '<td class="px-4 py-3 text-slate-300 text-sm capitalize">' + (member.gender ? member.gender : 'N/A') + '</td>' +
                                '</tr>';
                            tableBody.append(row);
                        });
                    } else {
                        tableBody.append('<tr><td colspan="3" class="px-4 py-4 text-center text-slate-400">No members found in this household.</td></tr>');
                    }
                } else {
                    tableBody.html('<tr><td colspan="3" class="px-4 py-4 text-center text-red-400">' + response.message + '</td></tr>');
                }
            },
            error: function() {
                $('#my-household-list').html('<tr><td colspan="3" class="px-4 py-4 text-center text-red-400">Error loading household data.</td></tr>');
            }
        });
    }

    // --- 4. NEW! Load Aid History ---
    function loadMyAidHistory() {
        $.ajax({
            url: 'api/resident/get_my_aid_history.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var list = $('#my-aid-history-list');
                
                if (response.success) {
                    list.empty(); // Clear "Loading..."
                    
                    if(response.history.length > 0) {
                        response.history.forEach(function(item) {
                            // Format the date
                            var date = new Date(item.distribution_date);
                            var formattedDate = date.toLocaleDateString('en-US', {
                                month: 'short', day: 'numeric', year: 'numeric'
                            });

                            var listItem = '<li class="flex justify-between items-center p-2 rounded-lg hover:bg-white/5">' +
                                '<div>' +
                                    '<p class="text-white font-medium">' + item.item_name + '</p>' +
                                    '<p class="text-slate-400 text-xs">' + formattedDate + '</p>' +
                                '</div>' +
                                '<p class="text-white font-bold text-sm">x' + item.quantity + '</p>' +
                                '</li>';
                            list.append(listItem);
                        });
                    } else {
                        list.append('<li class="px-2 py-4 text-center text-slate-400">No aid has been recorded for your household.</li>');
                    }
                } else {
                    list.html('<li class="px-2 py-4 text-center text-red-400">' + response.message + '</li>');
                }
            },
            error: function() {
                $('#my-aid-history-list').html('<li class="px-2 py-4 text-center text-red-400">Error loading aid history.</li>');
            }
        });
    }


    // --- 5. Initial Page Load ---
    loadMyHousehold();
    loadMyAidHistory(); // <-- We are now calling both

});