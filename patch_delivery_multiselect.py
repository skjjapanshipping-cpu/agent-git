#!/usr/bin/env python3
"""
Convert delivery_type_id single-select dropdown to multi-select checkbox dropdown.
Admin can check multiple delivery types to filter (e.g. ที่อยู่ปัจจุบัน + เพิ่มที่อยู่เอง without รับเอง).
"""

path = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershipping/index.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

changes = 0

# === 1. Replace single-select dropdown with multi-select checkbox dropdown ===
old_dropdown = """                    this.api().columns([16]).every(function () {
                        var column = this;
                        var select = $('<select class="delivery_type_id"><option value="">การจัดส่ง(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                // ปิดปุ่ม SHIPPING EXPORT EXCEL เมื่อมีการเปลี่ยน filter
                                $('#shipping-export').addClass('disabled');
                                dataTable.ajax.reload(null, false);

                            });

                        select.append('<option value="1">รับเอง</option>')
                        select.append('<option value="2">ที่อยู่ปัจจุบัน        </option>')
                        select.append('<option value="3">เพิ่มที่อยู่เอง</option>')
                        // });
                    });"""

new_dropdown = """                    this.api().columns([16]).every(function () {
                        var column = this;
                        var $header = $(column.header()).empty();

                        // Multi-select checkbox dropdown for delivery type
                        var dtDropdown = $(
                            '<div class="dt-multiselect-wrap" style="position:relative;display:inline-block;width:100%;">'
                            + '<button type="button" class="dt-multiselect-btn" style="width:100%;text-align:left;padding:4px 8px;font-size:12px;border:1px solid #aaa;border-radius:3px;background:#fff;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'
                            + 'การจัดส่ง(ทั้งหมด) ▾</button>'
                            + '<div class="dt-multiselect-menu" style="display:none;position:absolute;z-index:9999;background:#fff;border:1px solid #ccc;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,.15);min-width:160px;padding:4px 0;top:100%;left:0;">'
                            + '<label style="display:block;padding:4px 10px;margin:0;cursor:pointer;font-weight:normal;font-size:12px;white-space:nowrap;"><input type="checkbox" class="dt-cb-delivery" value="1" style="margin-right:6px;"> รับเอง</label>'
                            + '<label style="display:block;padding:4px 10px;margin:0;cursor:pointer;font-weight:normal;font-size:12px;white-space:nowrap;"><input type="checkbox" class="dt-cb-delivery" value="2" style="margin-right:6px;"> ที่อยู่ปัจจุบัน</label>'
                            + '<label style="display:block;padding:4px 10px;margin:0;cursor:pointer;font-weight:normal;font-size:12px;white-space:nowrap;"><input type="checkbox" class="dt-cb-delivery" value="3" style="margin-right:6px;"> เพิ่มที่อยู่เอง</label>'
                            + '<hr style="margin:4px 0;border:none;border-top:1px solid #eee;">'
                            + '<div style="text-align:center;padding:2px 6px;">'
                            + '<button type="button" class="dt-cb-delivery-apply btn btn-xs btn-primary" style="font-size:11px;padding:2px 12px;">ตกลง</button>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                        );
                        $header.append(dtDropdown);

                        // Toggle dropdown menu
                        dtDropdown.find('.dt-multiselect-btn').on('click', function(e) {
                            e.stopPropagation();
                            var $menu = dtDropdown.find('.dt-multiselect-menu');
                            $menu.toggle();
                        });

                        // Apply filter on button click
                        dtDropdown.find('.dt-cb-delivery-apply').on('click', function(e) {
                            e.stopPropagation();
                            dtDropdown.find('.dt-multiselect-menu').hide();
                            // Update button label
                            var checked = dtDropdown.find('.dt-cb-delivery:checked');
                            if (checked.length === 0 || checked.length === 3) {
                                dtDropdown.find('.dt-multiselect-btn').text('การจัดส่ง(ทั้งหมด) ▾');
                            } else {
                                var labels = [];
                                checked.each(function() { labels.push($(this).parent().text().trim()); });
                                dtDropdown.find('.dt-multiselect-btn').text(labels.join(', ') + ' ▾');
                            }
                            $('#shipping-export').addClass('disabled');
                            dataTable.ajax.reload(null, false);
                            updateInvoiceButtonState();
                        });

                        // Close dropdown when clicking outside
                        $(document).on('click', function(e) {
                            if (!$(e.target).closest('.dt-multiselect-wrap').length) {
                                $('.dt-multiselect-menu').hide();
                            }
                        });

                        // Highlight checkbox row on hover
                        dtDropdown.find('label').hover(
                            function() { $(this).css('background', '#f0f4ff'); },
                            function() { $(this).css('background', ''); }
                        );
                    });"""

if old_dropdown in content:
    content = content.replace(old_dropdown, new_dropdown)
    changes += 1
    print('1. Replaced single-select dropdown with multi-select checkbox dropdown')
else:
    print('1. FAILED: Could not find old dropdown code')

# === 2. Update data function to send multiple delivery_type_id values ===
old_data = "d.delivery_type_id = $(\"select.delivery_type_id\").val();"

new_data = """d.delivery_type_id = (function() {
                            var checked = $('.dt-cb-delivery:checked');
                            if (checked.length === 0 || checked.length === 3) return '';
                            var vals = [];
                            checked.each(function() { vals.push($(this).val()); });
                            return vals.join(',');
                        })();"""

if old_data in content:
    content = content.replace(old_data, new_data)
    changes += 1
    print('2. Updated data function to send multiple delivery_type_id values')
else:
    print('2. FAILED: Could not find old data code')

# === 3. Update event listener reference ===
old_listener = '$("select.delivery_type_id").on(\'change\', updateInvoiceButtonState);'
new_listener = '$(document).on(\'change\', \'.dt-cb-delivery\', updateInvoiceButtonState);'

if old_listener in content:
    content = content.replace(old_listener, new_listener)
    changes += 1
    print('3. Updated event listener for multi-select')
else:
    print('3. FAILED: Could not find old event listener')

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print(f'\nTotal changes: {changes}/3')
