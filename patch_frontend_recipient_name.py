# -*- coding: utf-8 -*-
f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    c = fh.read()

# Add delivery_fullname under the delivery badge in column 2
old = """                        "targets": 2, // Delivery
                        "render": function (data, type, full, meta) {
                            let badgeClass = 'pending';
                            let icon = 'fa-exclamation-circle';
                            let text = 'เลือกวิธีจัดส่ง';
                            if (data && data.trim() !== '' && data !== '-') {
                                text = data;
                                if (data.indexOf('ปัจจุบัน') !== -1) { badgeClass = 'home'; icon = 'fa-home'; }
                                else if (data.indexOf('เพิ่มที่อยู่') !== -1) { badgeClass = 'ems'; icon = 'fa-truck'; }
                                else if (data.indexOf('รับเอง') !== -1) { badgeClass = 'self'; icon = 'fa-user'; }
                                else { badgeClass = 'ems'; icon = 'fa-truck'; }
                            }
                            return `<span class="delivery-badge ${badgeClass}"><i class="fa ${icon}"></i> ${text}</span>`;
                        }"""

new = """                        "targets": 2, // Delivery
                        "render": function (data, type, full, meta) {
                            let badgeClass = 'pending';
                            let icon = 'fa-exclamation-circle';
                            let text = 'เลือกวิธีจัดส่ง';
                            if (data && data.trim() !== '' && data !== '-') {
                                text = data;
                                if (data.indexOf('ปัจจุบัน') !== -1) { badgeClass = 'home'; icon = 'fa-home'; }
                                else if (data.indexOf('เพิ่มที่อยู่') !== -1) { badgeClass = 'ems'; icon = 'fa-truck'; }
                                else if (data.indexOf('รับเอง') !== -1) { badgeClass = 'self'; icon = 'fa-user'; }
                                else { badgeClass = 'ems'; icon = 'fa-truck'; }
                            }
                            var html = '<span class="delivery-badge ' + badgeClass + '"><i class="fa ' + icon + '"></i> ' + text + '</span>';
                            var name = full.delivery_fullname || '';
                            if (name) {
                                html += '<div style="font-size:11px;color:#0369a1;font-weight:600;margin-top:2px;"><i class="fa fa-user" style="font-size:10px;"></i> ' + name + '</div>';
                            }
                            return html;
                        }"""

if old not in c:
    print('ERROR: delivery badge render not found')
    exit(1)

c = c.replace(old, new, 1)

with open(f, 'w') as fh:
    fh.write(c)
print('DONE: delivery_fullname shown under delivery badge')
