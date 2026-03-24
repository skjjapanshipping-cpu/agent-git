import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# === PATCH 1: Add "กำหนดผู้รับ" button next to existing "เลือกจัดส่งที่อยู่ปัจจุบัน" button ===
old_button = """                        <button type="button" id="updateSelected" class="btn btn-modern btn-modern-primary" onclick="checkAndUpdateSelection()">
                            <i class="fa fa-check-circle"></i> เลือกจัดส่งที่อยู่ปัจจุบัน
                        </button>"""

new_button = """                        <button type="button" id="updateSelected" class="btn btn-modern btn-modern-primary" onclick="checkAndUpdateSelection()">
                            <i class="fa fa-check-circle"></i> เลือกจัดส่งที่อยู่ปัจจุบัน
                        </button>
                        <button type="button" class="btn btn-modern btn-modern-accent" onclick="openBatchRecipientModal()">
                            <i class="fa fa-users"></i> กำหนดผู้รับ
                        </button>"""

if old_button not in content:
    errors.append('existing button not found')
else:
    content = content.replace(old_button, new_button)

# === PATCH 2: Add CSS for accent button + modal ===
old_css_end = """    /* Search Hint */
    .search-hint {
        font-size: 0.78rem;
        margin-top: 4px;
    }"""

# We need to find this in the customershippingview form.blade.php's css section
# Actually, let me add CSS for the btn-modern-accent in the index.blade.php
# Let me find where btn-modern styles are defined

if old_css_end in content:
    # This is in form.blade.php, not index.blade.php
    pass

# Let me find a better place - look for btn-modern-primary CSS
old_css_marker = None
# Search for existing btn-modern styles
if '.btn-modern-primary' in content:
    # Find the block
    pass

# Actually let me just add the CSS and modal before the @endsection at the very end
# The script block I added before ends with </script>\n\n@endsection
old_end_section = """</script>

@endsection"""

new_end_section = """</script>

<!-- Batch Recipient Modal -->
<div id="batchRecipientModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; z-index:9999; background:rgba(0,0,0,0.5); backdrop-filter:blur(2px);">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; border-radius:20px; width:95%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 60px rgba(0,0,0,0.3);">
        <!-- Header -->
        <div style="padding:24px 28px 16px; border-bottom:1px solid #f1f5f9;">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:42px; height:42px; background:linear-gradient(135deg,#1D8AC9,#0ea5e9); border-radius:12px; display:flex; align-items:center; justify-content:center; color:white; font-size:1.1rem;">
                        <i class="fa fa-users"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1.1rem; font-weight:700; color:#1e293b;">กำหนดผู้รับ</h3>
                        <p id="batchRecipientCount" style="margin:0; font-size:0.82rem; color:#64748b;">0 รายการ</p>
                    </div>
                </div>
                <button onclick="closeBatchRecipientModal()" style="background:none; border:none; cursor:pointer; padding:8px;">
                    <i class="fa fa-times" style="font-size:1.2rem; color:#94a3b8;"></i>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div style="padding:20px 28px;">
            <!-- Delivery Type -->
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">วิธีจัดส่ง</label>
                <select id="batch_delivery_type" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem; color:#1e293b;">
                    <option value="3" selected>เพิ่มที่อยู่เอง</option>
                    <option value="2">ที่อยู่ปัจจุบัน</option>
                    <option value="1">รับเอง</option>
                </select>
            </div>

            <!-- Recipient Fields (shown for type 3) -->
            <div id="batchRecipientFields">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">ชื่อ-นามสกุล</label>
                        <input type="text" id="batch_fullname" placeholder="ชื่อ-นามสกุล" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <small style="color:#ef4444; font-size:0.72rem;">*ค้นหาด้วยชื่อ (ประวัติการส่งที่ผ่านมา)*</small>
                        <div id="batch_fullname-results" class="search-results"></div>
                    </div>
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">เบอร์โทร</label>
                        <input type="text" id="batch_mobile" placeholder="เบอร์โทร" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <small style="color:#ef4444; font-size:0.72rem;">*ค้นหาด้วยเบอร์โทร (ประวัติการส่งที่ผ่านมา)*</small>
                        <div id="batch_mobile-results" class="search-results"></div>
                    </div>
                </div>
                <div style="margin-bottom:10px;">
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">ที่อยู่</label>
                    <input type="text" id="batch_address" placeholder="บ้านเลขที่ ซอย ถนน" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">แขวง/ตำบล</label>
                        <input type="text" id="batch_subdistrict" placeholder="พิมพ์เพื่อค้นหาตำบล" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_subdistrict-results" class="search-results"></div>
                    </div>
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">เขต/อำเภอ</label>
                        <input type="text" id="batch_district" placeholder="พิมพ์เพื่อค้นหาอำเภอ" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_district-results" class="search-results"></div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">จังหวัด</label>
                        <input type="text" id="batch_province" placeholder="พิมพ์เพื่อค้นหาจังหวัด" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_province-results" class="search-results"></div>
                    </div>
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">รหัสไปรษณีย์</label>
                        <input type="text" id="batch_postcode" placeholder="รหัสไปรษณีย์" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_postcode-results" class="search-results"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding:16px 28px 24px; border-top:1px solid #f1f5f9; display:flex; gap:10px; justify-content:flex-end;">
            <button onclick="closeBatchRecipientModal()" style="padding:12px 24px; background:#f1f5f9; color:#64748b; border:1.5px solid #e2e8f0; border-radius:12px; font-size:0.9rem; font-weight:600; cursor:pointer;">
                ยกเลิก
            </button>
            <button onclick="submitBatchRecipient()" id="batchSubmitBtn" style="padding:12px 28px; background:linear-gradient(135deg,#1D8AC9,#0ea5e9); color:white; border:none; border-radius:12px; font-size:0.9rem; font-weight:600; cursor:pointer; box-shadow:0 4px 15px rgba(29,138,201,0.3);">
                <i class="fa fa-check"></i> บันทึก
            </button>
        </div>
    </div>
</div>

<style>
    .btn-modern-accent {
        background: linear-gradient(135deg, #8b5cf6, #6d28d9) !important;
        color: white !important;
        border: none !important;
    }
    .btn-modern-accent:hover {
        background: linear-gradient(135deg, #7c3aed, #5b21b6) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
    }
    #batchRecipientModal .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 10000;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        display: none;
    }
    #batchRecipientModal .search-results .search-result-item {
        padding: 10px 14px;
        cursor: pointer;
        font-size: 0.85rem;
        border-bottom: 1px solid #f8fafc;
    }
    #batchRecipientModal .search-results .search-result-item:hover {
        background: #f0f9ff;
    }
    @media (max-width: 768px) {
        #batchRecipientModal > div > div:nth-child(2) {
            padding: 16px 20px !important;
        }
        #batchRecipientFields [style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
    // === Batch Recipient Modal Logic ===
    var batchSelectedIds = [];

    function openBatchRecipientModal() {
        var selectedCheckboxes = $('#dt-mant-table-1 tbody input[type="checkbox"]:checked');
        if (selectedCheckboxes.length === 0) {
            Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: 'กรุณาเลือกรายการที่ต้องการกำหนดผู้รับ', confirmButtonColor: '#1D8AC9' });
            return;
        }
        batchSelectedIds = [];
        selectedCheckboxes.each(function() { batchSelectedIds.push(parseInt($(this).val())); });
        $('#batchRecipientCount').text(batchSelectedIds.length + ' รายการ');

        // Reset form
        $('#batch_delivery_type').val('3');
        $('#batch_fullname, #batch_mobile, #batch_address, #batch_subdistrict, #batch_district, #batch_province, #batch_postcode').val('');
        $('#batchRecipientFields').show();

        $('#batchRecipientModal').fadeIn(200);

        // Initialize customer search for batch modal
        initBatchCustomerSearch();
        initBatchThaiAddressSearch();
    }

    function closeBatchRecipientModal() {
        $('#batchRecipientModal').fadeOut(200);
        // Clean up search results
        $('#batchRecipientModal .search-results').hide().empty();
    }

    // Toggle fields based on delivery type
    $('#batch_delivery_type').on('change', function() {
        var val = $(this).val();
        if (val === '3') {
            $('#batchRecipientFields').slideDown(200);
        } else {
            $('#batchRecipientFields').slideUp(200);
        }
    });

    function submitBatchRecipient() {
        var deliveryType = $('#batch_delivery_type').val();
        var data = {
            ids: batchSelectedIds,
            delivery_type_id: parseInt(deliveryType),
            _token: '{{ csrf_token() }}'
        };

        if (deliveryType === '3') {
            // Validate required fields
            var fullname = $('#batch_fullname').val().trim();
            var mobile = $('#batch_mobile').val().trim();
            var address = $('#batch_address').val().trim();
            var subdistrict = $('#batch_subdistrict').val().trim();
            var district = $('#batch_district').val().trim();
            var province = $('#batch_province').val().trim();
            var postcode = $('#batch_postcode').val().trim();

            if (!fullname || !mobile || !address || !subdistrict || !district || !province || !postcode) {
                Swal.fire({ icon: 'warning', title: 'กรุณากรอกข้อมูลให้ครบ', text: 'กรุณากรอกชื่อ เบอร์โทร และที่อยู่ให้ครบถ้วน', confirmButtonColor: '#1D8AC9' });
                return;
            }

            data.delivery_fullname = fullname;
            data.delivery_mobile = mobile;
            data.delivery_address = address;
            data.delivery_subdistrict = subdistrict;
            data.delivery_district = district;
            data.delivery_province = province;
            data.delivery_postcode = postcode;
        }

        var typeName = deliveryType === '1' ? 'รับเอง' : (deliveryType === '2' ? 'ที่อยู่ปัจจุบัน' : data.delivery_fullname);

        Swal.fire({
            title: 'ยืนยันกำหนดผู้รับ?',
            html: 'อัพเดท <b>' + batchSelectedIds.length + '</b> รายการ<br>ผู้รับ: <b>' + typeName + '</b>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1D8AC9',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then(function(result) {
            if (result.isConfirmed) {
                $('#batchSubmitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> กำลังบันทึก...');

                $.ajax({
                    url: '{{ route("batch.update.recipient") }}',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        closeBatchRecipientModal();
                        $('#batchSubmitBtn').prop('disabled', false).html('<i class="fa fa-check"></i> บันทึก');

                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: res.message,
                            confirmButtonColor: '#1D8AC9',
                            timer: 2500
                        });

                        // Reload DataTable + recipients dropdown
                        $('#dt-mant-table-1').DataTable().ajax.reload();
                        loadRecipients();
                    },
                    error: function(xhr) {
                        $('#batchSubmitBtn').prop('disabled', false).html('<i class="fa fa-check"></i> บันทึก');
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'เกิดข้อผิดพลาด';
                        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg, confirmButtonColor: '#1D8AC9' });
                    }
                });
            }
        });
    }

    // Customer search for batch modal (name + phone)
    function initBatchCustomerSearch() {
        var debounceTimer;

        $('#batch_fullname').off('input').on('input', function() {
            var query = $(this).val().trim();
            clearTimeout(debounceTimer);
            if (query.length < 2) { $('#batch_fullname-results').hide().empty(); return; }
            debounceTimer = setTimeout(function() {
                $.get('/skjtrack/search-customer', { q: query, field: 'name' }, function(data) {
                    var $results = $('#batch_fullname-results').empty();
                    if (data.length > 0) {
                        data.forEach(function(c) {
                            $results.append('<div class="search-result-item" data-customer=\'' + JSON.stringify(c) + '\'>' + c.name + ' - ' + (c.mobile || '') + '</div>');
                        });
                        $results.show();
                    } else { $results.hide(); }
                });
            }, 300);
        });

        $('#batch_mobile').off('input').on('input', function() {
            var query = $(this).val().trim();
            clearTimeout(debounceTimer);
            if (query.length < 3) { $('#batch_mobile-results').hide().empty(); return; }
            debounceTimer = setTimeout(function() {
                $.get('/skjtrack/search-customer', { q: query, field: 'mobile' }, function(data) {
                    var $results = $('#batch_mobile-results').empty();
                    if (data.length > 0) {
                        data.forEach(function(c) {
                            $results.append('<div class="search-result-item" data-customer=\'' + JSON.stringify(c) + '\'>' + c.name + ' - ' + (c.mobile || '') + '</div>');
                        });
                        $results.show();
                    } else { $results.hide(); }
                });
            }, 300);
        });

        // Handle click on search result
        $(document).off('click', '#batch_fullname-results .search-result-item, #batch_mobile-results .search-result-item')
            .on('click', '#batch_fullname-results .search-result-item, #batch_mobile-results .search-result-item', function() {
            var c = JSON.parse($(this).attr('data-customer'));
            $('#batch_fullname').val(c.name || '');
            $('#batch_mobile').val(c.mobile || '');
            $('#batch_address').val(c.address || '');
            $('#batch_subdistrict').val(c.tambon || c.subdistrict || '');
            $('#batch_district').val(c.amphoe || c.district || '');
            $('#batch_province').val(c.province || '');
            $('#batch_postcode').val(c.zipcode || c.postcode || '');
            $('#batch_fullname-results, #batch_mobile-results').hide().empty();
        });

        // Close results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#batch_fullname, #batch_fullname-results').length) {
                $('#batch_fullname-results').hide();
            }
            if (!$(e.target).closest('#batch_mobile, #batch_mobile-results').length) {
                $('#batch_mobile-results').hide();
            }
        });
    }

    // Thai address search for batch modal
    function initBatchThaiAddressSearch() {
        if (typeof initThaiAddressSearch === 'function') {
            initThaiAddressSearch({
                formId: '#batchRecipientModal',
                provinceField: '#batch_province',
                amphoeField: '#batch_district',
                tambonField: '#batch_subdistrict',
                zipcodeField: '#batch_postcode',
                onAddressSelect: function(address) {
                    console.log('Batch address selected:', address);
                }
            });
        }
    }

    // Close modal on ESC or backdrop click
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') closeBatchRecipientModal();
    });
    $('#batchRecipientModal').on('click', function(e) {
        if (e.target === this) closeBatchRecipientModal();
    });
</script>

@endsection"""

if old_end_section not in content:
    errors.append('@endsection block not found')
else:
    # Replace only the LAST occurrence
    idx = content.rfind(old_end_section)
    content = content[:idx] + new_end_section + content[idx + len(old_end_section):]

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Blade: batch recipient UI added successfully')
