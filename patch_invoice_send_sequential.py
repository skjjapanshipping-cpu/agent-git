#!/usr/bin/env python3
"""
Replace batch invoice sending with sequential per-customer sending.
Each customer gets immediate ✓/✗ feedback in real-time.
"""

path = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershipping/index.blade.php'
with open(path, 'r') as f:
    content = f.read()

# Find and replace the entire AJAX block
old_code = """                var btn = $(this);
                btn.prop('disabled', true).text('⏳ กำลังส่ง...');
                $('#invoiceChatResult').html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> กำลังส่งบิล ' + selectedCustomers.length + ' ราย กรุณารอ...</div>').show();

                // แสดง spinner ข้างๆ แต่ละราย
                selectedCustomers.forEach(function(cn) {
                    $('.chat-send-result[data-cn="' + cn + '"]').html('<i class="fa fa-spinner fa-spin" style="color:#17a2b8;"></i>');
                });

                // รวม shipping_ids ของลูกค้าที่เลือก
                var customerMap = $('#invoiceChatModal').data('customerMap') || {};
                var shippingIdsMap = {};
                selectedCustomers.forEach(function(cn) {
                    if (customerMap[cn]) shippingIdsMap[cn] = customerMap[cn];
                });

                $.ajax({
                    url: "{{ route('send.invoice.chat') }}",
                    type: 'POST',
                    data: {
                        etd: etdDate,
                        customer_nos: selectedCustomers,
                        shipping_ids_map: shippingIdsMap,
                        message_template: messageTemplate,
                        qr_image_url: qrImageUrl,
                        messenger_fee: messengerFee,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        btn.prop('disabled', false).text('📩 ส่งบิล');

                        // อัพเดทผลลัพธ์ inline ข้างๆ badge เชื่อมต่อของแต่ละราย
                        var successCount = 0, partialCount = 0, failedCount = 0;
                        if (response.results && response.results.details) {
                            response.results.details.forEach(function(d) {
                                var cn = d.customerno;
                                var badge = $('.chat-send-result[data-cn="' + cn + '"]');
                                if (d.status === 'success') {
                                    successCount++;
                                    badge.html('<span class="badge" style="background:#28a745;color:#fff;font-size:10px;">✅ ส่งสำเร็จ</span> <span class="badge" style="background:#fd7e14;color:#fff;font-size:10px;">🟠 รอโอน</span>');
                                } else if (d.status === 'partial') {
                                    partialCount++;
                                    var shortMsg = d.message.replace(/.*→.*\\)/, '').trim();
                                    badge.html('<span class="badge" style="background:#fd7e14;color:#fff;font-size:10px;">⚠️ ส่งได้บางส่วน</span> <span class="badge" style="background:#fd7e14;color:#fff;font-size:10px;">🟠 รอโอน</span>'
                                        + (shortMsg ? '<br><span style="font-size:9px;color:#fd7e14;">' + shortMsg + '</span>' : ''));
                                } else if (d.status === 'not_found') {
                                    failedCount++;
                                    badge.html('<span class="badge" style="background:#ffc107;color:#333;font-size:10px;">🔍 ไม่พบในแชท</span>');
                                } else {
                                    failedCount++;
                                    var errMsg = d.message || 'ส่งไม่สำเร็จ';
                                    if (errMsg.indexOf('24') >= 0 || errMsg.indexOf('window') >= 0) {
                                        errMsg = 'FB เกิน 24 ชม.';
                                    }
                                    badge.html('<span class="badge" style="background:#dc3545;color:#fff;font-size:10px;">❌ ' + errMsg.substring(0, 40) + '</span>');
                                }
                            });
                        }

                        // สรุปย่อด้านล่าง
                        var summaryParts = [];
                        if (successCount > 0) summaryParts.push('✅ สำเร็จ ' + successCount + ' ราย');
                        if (partialCount > 0) summaryParts.push('⚠️ บางส่วน ' + partialCount + ' ราย');
                        if (failedCount > 0) summaryParts.push('❌ ไม่สำเร็จ ' + failedCount + ' ราย');
                        var alertClass = failedCount > 0 ? 'alert-warning' : 'alert-success';
                        $('#invoiceChatResult').html('<div class="alert ' + alertClass + '" style="padding:8px 12px;margin-top:8px;"><b>' + summaryParts.join(' &nbsp;|&nbsp; ') + '</b></div>').show();

                        // รีเฟรชสถานะส่งบิลหลังส่งสำเร็จ
                        setTimeout(function() { refreshInvoiceStatus(); }, 1000);
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('📩 ส่งบิล');
                        var errMsg = 'เกิดข้อผิดพลาด';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        $('#invoiceChatResult').html('<div class="alert alert-danger">' + errMsg + '</div>').show();
                    }
                });"""

new_code = """                var btn = $(this);
                btn.prop('disabled', true).text('⏳ กำลังส่ง...');
                var totalToSend = selectedCustomers.length;
                $('#invoiceChatResult').html('<div class="alert alert-info" id="invoiceChatProgress"><i class="fa fa-spinner fa-spin"></i> กำลังส่งบิล <span id="invoiceSentCount">0</span>/' + totalToSend + ' ราย กรุณารอ...</div>').show();

                // แสดง spinner ข้างๆ แต่ละราย
                selectedCustomers.forEach(function(cn) {
                    $('.chat-send-result[data-cn="' + cn + '"]').html('<i class="fa fa-spinner fa-spin" style="color:#17a2b8;"></i>');
                });

                // รวม shipping_ids ของลูกค้าที่เลือก
                var customerMap = $('#invoiceChatModal').data('customerMap') || {};

                // === ส่งทีละราย (sequential) เพื่ออัพเดท ✓/✗ real-time ===
                var successCount = 0, partialCount = 0, failedCount = 0, sentSoFar = 0;

                async function sendOneByOne() {
                    for (var i = 0; i < selectedCustomers.length; i++) {
                        var cn = selectedCustomers[i];
                        var singleShippingMap = {};
                        if (customerMap[cn]) singleShippingMap[cn] = customerMap[cn];

                        // Scroll ไปที่แถวที่กำลังส่ง
                        var rowEl = document.getElementById('chat-row-' + cn.replace(/[^a-zA-Z0-9\\-]/g, '_'));
                        if (rowEl) rowEl.scrollIntoView({ behavior: 'smooth', block: 'center' });

                        try {
                            var response = await $.ajax({
                                url: "{{ route('send.invoice.chat') }}",
                                type: 'POST',
                                timeout: 60000,
                                data: {
                                    etd: etdDate,
                                    customer_nos: [cn],
                                    shipping_ids_map: singleShippingMap,
                                    message_template: messageTemplate,
                                    qr_image_url: qrImageUrl,
                                    messenger_fee: messengerFee,
                                    _token: "{{ csrf_token() }}"
                                }
                            });

                            // อัพเดทผลลัพธ์ทันที
                            var badge = $('.chat-send-result[data-cn="' + cn + '"]');
                            if (response.results && response.results.details) {
                                var d = response.results.details[0];
                                if (d && d.status === 'success') {
                                    successCount++;
                                    badge.html('<i class="fa fa-check-circle" style="color:#28a745;font-size:14px;" title="ส่งสำเร็จ"></i>');
                                } else if (d && d.status === 'partial') {
                                    partialCount++;
                                    var shortMsg = (d.message || '').replace(/.*→.*\\)/, '').trim();
                                    badge.html('<i class="fa fa-exclamation-circle" style="color:#fd7e14;font-size:14px;" title="ส่งได้บางส่วน: ' + shortMsg + '"></i>');
                                } else if (d && d.status === 'not_found') {
                                    failedCount++;
                                    badge.html('<i class="fa fa-times-circle" style="color:#ffc107;font-size:14px;" title="ไม่พบในแชท"></i>');
                                } else {
                                    failedCount++;
                                    var errMsg = (d && d.message) ? d.message.substring(0, 60) : 'ส่งไม่สำเร็จ';
                                    badge.html('<i class="fa fa-times-circle" style="color:#dc3545;font-size:14px;" title="' + errMsg + '"></i>');
                                }
                            } else {
                                successCount++;
                                badge.html('<i class="fa fa-check-circle" style="color:#28a745;font-size:14px;" title="ส่งสำเร็จ"></i>');
                            }
                        } catch (err) {
                            failedCount++;
                            var errMsg = 'เกิดข้อผิดพลาด';
                            if (err.responseJSON && err.responseJSON.message) errMsg = err.responseJSON.message;
                            else if (err.statusText === 'timeout') errMsg = 'หมดเวลา (timeout)';
                            $('.chat-send-result[data-cn="' + cn + '"]').html('<i class="fa fa-times-circle" style="color:#dc3545;font-size:14px;" title="' + errMsg.substring(0, 60) + '"></i>');
                        }

                        sentSoFar++;
                        $('#invoiceSentCount').text(sentSoFar);
                    }

                    // === เสร็จทุกราย — สรุปผล ===
                    btn.prop('disabled', false).text('📩 ส่งบิล');
                    var summaryParts = [];
                    if (successCount > 0) summaryParts.push('✅ สำเร็จ ' + successCount + ' ราย');
                    if (partialCount > 0) summaryParts.push('⚠️ บางส่วน ' + partialCount + ' ราย');
                    if (failedCount > 0) summaryParts.push('❌ ไม่สำเร็จ ' + failedCount + ' ราย');
                    var alertClass = failedCount > 0 ? 'alert-warning' : 'alert-success';
                    $('#invoiceChatResult').html('<div class="alert ' + alertClass + '" style="padding:8px 12px;margin-top:8px;"><b>' + summaryParts.join(' &nbsp;|&nbsp; ') + '</b></div>').show();
                    setTimeout(function() { refreshInvoiceStatus(); }, 1000);
                }

                sendOneByOne();"""

if old_code in content:
    content = content.replace(old_code, new_code)
    with open(path, 'w') as f:
        f.write(content)
    print('SUCCESS: Replaced batch sending with sequential per-customer sending')
else:
    print('FAILED: Could not find the old code block')
    # Debug: try to find parts of it
    checks = [
        "btn.prop('disabled', true).text('⏳ กำลังส่ง...');",
        "$.ajax({",
        "error: function(xhr) {",
        "var errMsg = 'เกิดข้อผิดพลาด';",
    ]
    for c in checks:
        if c in content:
            print(f'  Found: {c[:50]}')
        else:
            print(f'  NOT found: {c[:50]}')
