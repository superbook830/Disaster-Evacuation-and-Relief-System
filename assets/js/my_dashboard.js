$(document).ready(function() {

    // ==========================================
    // 1. PROFILE PICTURE HANDLING
    // ==========================================
    $('#upload-pic-btn').on('click', function() { $('#profile-pic-input').click(); });
    $('#profile-pic-input').on('change', function() {
        var file_data = $(this).prop('files')[0];
        var formData = new FormData();
        formData.append('profile_pic', file_data);
        $.ajax({ url: 'api/resident/upload_profile_pic.php', type: 'POST', data: formData, dataType: 'json', contentType: false, processData: false,
            success: function(response) { if (response.success) location.reload(); else alert('Error: ' + response.message); }
        });
    });

    // ==========================================
    // 2. LOAD HOUSEHOLD DATA (With Edit/Delete)
    // ==========================================
    var myMembers = []; // Local storage for edit data

    function loadMyHousehold() {
        $.ajax({
            url: 'api/resident/get_my_household.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var tableBody = $('#my-household-list');
                
                if (response.success) {
                    if (response.household) {
                        $('#household-address').text(response.household.address_notes);
                    }
                    
                    tableBody.empty();
                    myMembers = response.members; // Save data
                    
                    if(response.members.length > 0) {
                        response.members.forEach(function(member, index) {
                            var row = `
                                <tr class="group hover:bg-white/5 transition-colors">
                                    <td class="px-4 py-3 text-white text-sm">${member.first_name} ${member.last_name}</td>
                                    <td class="px-4 py-3 text-slate-300 text-sm">${member.birthdate || 'N/A'}</td>
                                    <td class="px-4 py-3 text-slate-300 text-sm capitalize">${member.gender || 'N/A'}</td>
                                    <td class="px-4 py-3 text-right">
                                        <button class="edit-btn text-blue-400 hover:text-blue-300 mr-2 transition-colors" data-index="${index}" title="Edit">
                                            <span class="material-symbols-outlined !text-[20px]">edit</span>
                                        </button>
                                        <button class="delete-btn text-red-400 hover:text-red-300 transition-colors" data-id="${member.id}" title="Delete">
                                            <span class="material-symbols-outlined !text-[20px]">delete</span>
                                        </button>
                                    </td>
                                </tr>`;
                            tableBody.append(row);
                        });
                    } else {
                        tableBody.append('<tr><td colspan="4" class="px-4 py-4 text-center text-slate-400">No members found in this household.</td></tr>');
                    }
                }
            }
        });
    }

    // ==========================================
    // 3. LOAD AID HISTORY (Handles both Dashboard & History Page)
    // ==========================================
    function loadMyAidHistory() {
        $.ajax({
            url: 'api/resident/get_my_aid_history.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                
                // 1. Handle Dashboard Widget (List View)
                var widgetList = $('#my-aid-history-list');
                if (widgetList.length) {
                    widgetList.empty();
                    if (Array.isArray(response) && response.length > 0) {
                        response.forEach(function(item) {
                            var dateStr = item.date_distributed || item.distribution_date || item.created_at;
                            var formattedDate = dateStr ? new Date(dateStr).toLocaleDateString() : 'N/A';
                            var rightText = item.quantity ? 'x' + item.quantity : 'Received';
                            widgetList.append(`<li class="flex justify-between items-center p-2 rounded-lg hover:bg-white/5"><div><p class="text-white font-medium">${item.item_name}</p><p class="text-slate-400 text-xs">${formattedDate}</p></div><p class="text-white font-bold text-sm">${rightText}</p></li>`);
                        });
                    } else if (Array.isArray(response)) {
                        widgetList.append('<li class="px-2 py-4 text-center text-slate-400">No aid recorded.</li>');
                    }
                }

                // 2. Handle Full Page Table (Table View)
                var fullTable = $('#aid-history-table-body');
                if (fullTable.length) {
                    fullTable.empty();
                    if (Array.isArray(response) && response.length > 0) {
                        response.forEach(function(item) {
                            var dateStr = item.date_distributed || item.distribution_date || item.created_at;
                            var formattedDate = dateStr ? new Date(dateStr).toLocaleString('en-US', { month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute:'2-digit' }) : 'N/A';
                            var qty = item.quantity ? item.quantity : 1;
                            
                            var row = `
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4 font-medium text-white">${item.item_name}</td>
                                    <td class="px-6 py-4 text-slate-300">${formattedDate}</td>
                                    <td class="px-6 py-4 text-right font-bold text-green-400">x${qty}</td>
                                </tr>
                            `;
                            fullTable.append(row);
                        });
                    } else if (Array.isArray(response)) {
                        fullTable.append('<tr><td colspan="3" class="px-6 py-8 text-center text-slate-400">No aid history found for your household.</td></tr>');
                    }
                }
            }
        });
    }

    // ==========================================
    // 4. LOAD EVACUATION HISTORY
    // ==========================================
    function loadEvacuationHistory() {
        var tableBody = $('#evacuation-history-table');
        tableBody.html('<tr><td colspan="4" class="px-4 py-4 text-center">Loading records...</td></tr>');
        $.ajax({
            url: 'api/resident/get_evacuation_history.php', type: 'GET', dataType: 'json',
            success: function(response) {
                tableBody.empty();
                if (response.success && response.history.length > 0) {
                    response.history.forEach(function(log) {
                        var checkIn = new Date(log.check_in_time).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' });
                        var statusBadge = (log.status === 'Checked Out' || log.check_out_time) 
                            ? '<span class="px-2 py-1 rounded bg-slate-700 text-slate-300 text-xs">Checked Out</span>' 
                            : '<span class="px-2 py-1 rounded bg-green-500/20 text-green-500 text-xs font-bold animate-pulse">Active</span>';
                        tableBody.append(`<tr class="hover:bg-white/5 transition-colors"><td class="px-4 py-3 text-white font-medium">${log.first_name}</td><td class="px-4 py-3">${log.center_name}</td><td class="px-4 py-3">${checkIn}</td><td class="px-4 py-3">${statusBadge}</td></tr>`);
                    });
                } else if (response.success && response.history.length === 0) {
                    tableBody.html('<tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">No evacuation records found.</td></tr>');
                } else { tableBody.html('<tr><td colspan="4" class="px-4 py-4 text-center text-red-400">Error loading data.</td></tr>'); }
            }
        });
    }

    // ==========================================
    // 5. MODAL LOGIC (ADD/EDIT MEMBER)
    // ==========================================
    function openModal(isEdit = false) {
        $('#add-member-modal').removeClass('hidden');
        $('body').addClass('overflow-hidden');
        
        if(isEdit) {
            $('#add-member-modal h3').text('Edit Family Member');
            $('#add-member-form button[type="submit"]').text('Update Member');
        } else {
            $('#add-member-modal h3').text('Add Family Member');
            $('#add-member-form button[type="submit"]').text('Save Member');
            $('#member_id').val(''); 
            $('#add-member-form')[0].reset(); 
        }
    }

    function closeModal() {
        $('#add-member-modal').addClass('hidden');
        $('#add-member-form')[0].reset();
        $('#member_id').val('');
        $('body').removeClass('overflow-hidden');
    }

    $('#open-add-member-modal').on('click', function(e) { e.preventDefault(); openModal(false); });
    $('#close-modal-btn, #cancel-modal-btn').on('click', closeModal);
    $('#add-member-modal').on('click', function(e) { if (e.target === this) closeModal(); });

    // Handle Edit Click
    $(document).on('click', '.edit-btn', function() {
        var index = $(this).data('index');
        var member = myMembers[index]; 
        
        $('#member_id').val(member.id);
        $('input[name="first_name"]').val(member.first_name);
        $('input[name="last_name"]').val(member.last_name);
        $('input[name="birthdate"]').val(member.birthdate);
        $('select[name="gender"]').val(member.gender);
        $('textarea[name="remarks"]').val(member.remarks);
        $('input[name="is_pwd"]').prop('checked', member.is_pwd == 1);
        $('input[name="is_senior"]').prop('checked', member.is_senior == 1);

        openModal(true);
    });

    // Handle Delete Click
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        if(confirm('Are you sure you want to delete this family member?')) {
            $.ajax({
                url: 'api/resident/delete_member.php', type: 'POST', data: { member_id: id }, dataType: 'json',
                success: function(response) {
                    if(response.success) loadMyHousehold();
                    else alert(response.message);
                }
            });
        }
    });

    // Handle Form Submit (Add or Update)
    $('#add-member-form').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        var originalText = btn.text();
        btn.text('Saving...').prop('disabled', true);

        var id = $('#member_id').val();
        var apiUrl = id ? 'api/resident/update_member.php' : 'api/resident/add_member.php';

        $.ajax({
            url: apiUrl, type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(response) {
                if(response.success) {
                    closeModal();
                    loadMyHousehold();
                    $('#success-modal').removeClass('hidden'); 
                } else { alert('Error: ' + response.message); }
            },
            error: function() { alert('System error.'); },
            complete: function() { btn.text(originalText).prop('disabled', false); }
        });
    });

    // ==========================================
    // 6. EVACUATION MODAL & SUCCESS MODAL
    // ==========================================
    $('#view-evac-history-btn').on('click', function(e){ e.preventDefault(); $('#evacuation-history-modal').removeClass('hidden'); loadEvacuationHistory(); });
    $('#close-evac-modal-btn, #close-evac-btn-bottom').on('click', function(){ $('#evacuation-history-modal').addClass('hidden'); });
    $('#close-success-btn').on('click', function(){ $('#success-modal').addClass('hidden'); });

    // ==========================================
    // 7. INITIAL LOAD
    // ==========================================
    loadMyHousehold();
    loadMyAidHistory();
});