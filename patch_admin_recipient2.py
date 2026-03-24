# -*- coding: utf-8 -*-
import sys

# ==============================
# PATCH 1: Admin Blade
# ==============================
f1 = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershipping/index.blade.php'
with open(f1, 'r') as fh:
    content1 = fh.read()

errors = []

# --- 1A: Add recipient dropdown in the date-filter-bar ---
old_date_bar_end = """                                <input type="text" id="end_date" placeholder="วว/ดด/ปปปป" readonly style="cursor:pointer;">
                            </div>"""

new_date_bar_end = """                                <input type="text" id="end_date" placeholder="วว/ดด/ปปปป" readonly style="cursor:pointer;">
                                <span style="margin-left:8px;"></span>
                                <i class="fa fa-user" style="color:#dc3545;font-size:16px;"></i>
                                <label for="recipient_filter">ผู้รับ</label>
                                <select id="recipient_filter" style="border-radius:8px; border:2px solid #e2e8f0; padding:6px 12px; font-size:13px; max-width:220px; cursor:pointer;">
                                    <option value="">ผู้รับทั้งหมด</option>
                                </select>
                            </div>"""

if old_date_bar_end not in content1:
    errors.append('BLADE: date-filter-bar end not found')
else:
    content1 = content1.replace(old_date_bar_end, new_date_bar_end, 1)

# --- 1B: Add recipient_filter to DataTable AJAX data ---
old_ajax_data = "d.box_no = $('#boxNoSearch').val() ? $.trim($('#boxNoSearch').val()) : '';"

new_ajax_data = """d.box_no = $('#boxNoSearch').val() ? $.trim($('#boxNoSearch').val()) : '';
                        d.recipient_filter = $('#recipient_filter').val();"""

if old_ajax_data not in content1:
    errors.append('BLADE: box_no AJAX data not found')
else:
    content1 = content1.replace(old_ajax_data, new_ajax_data, 1)

# --- 1C: Modify delivery_type_name column to also show recipient name ---
old_col16 = '{ "targets": 16, "data": "delivery_type_name" ,orderable:false},'

new_col16 = """{ "targets": 16, "data": "delivery_type_name", orderable:false, "render": function(data, type, row) {
                            var name = row.delivery_fullname || '';
                            var html = '<div>' + (data || '-') + '</div>';
                            if (name) {
                                html += '<div style="font-size:11px;color:#0369a1;font-weight:600;margin-top:2px;"><i class="fa fa-user" style="font-size:10px;"></i> ' + name + '</div>';
                            }
                            return html;
                        }
                    },"""

if old_col16 not in content1:
    errors.append('BLADE: column 16 delivery_type_name not found')
else:
    content1 = content1.replace(old_col16, new_col16, 1)

# --- 1D: Add JS for recipient filter ---
old_box_clear = """            $('#boxSearchClear').on('click', function() {
                $('#boxNoSearch').val('');
                $('#dt-mant-table-1 tbody tr').css('background', '');
                $('#boxSearchResult').html('');
                dataTable.ajax.reload(null, false);
            });"""

new_box_clear = """            $('#boxSearchClear').on('click', function() {
                $('#boxNoSearch').val('');
                $('#dt-mant-table-1 tbody tr').css('background', '');
                $('#boxSearchResult').html('');
                dataTable.ajax.reload(null, false);
            });

            // === Recipient Filter ===
            function loadAdminRecipients() {
                var startDate = $('#start_date').val();
                var searchVal = $("input[type='search']").val();
                $.ajax({
                    url: "{{ route('fetch.admin.recipients') }}",
                    type: "POST",
                    data: {
                        start_date: startDate,
                        customerno: searchVal ? $.trim(searchVal) : '',
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        var sel = $('#recipient_filter');
                        var currentVal = sel.val();
                        sel.find('option:not(:first)').remove();
                        if (res.recipients && res.recipients.length > 0) {
                            res.recipients.forEach(function(r) {
                                sel.append('<option value="' + r.value + '">' + r.label + ' (' + r.count + ')</option>');
                            });
                        }
                        if (currentVal && sel.find('option[value="' + currentVal + '"]').length > 0) {
                            sel.val(currentVal);
                        } else {
                            sel.val('');
                        }
                    }
                });
            }

            $('#recipient_filter').on('change', function() {
                dataTable.ajax.reload();
            });

            // Reload recipients after DataTable finishes loading
            dataTable.on('xhr.dt', function() {
                setTimeout(function() { loadAdminRecipients(); }, 300);
            });

            // Initial load
            setTimeout(function() { loadAdminRecipients(); }, 1000);"""

content1 = content1.replace(old_box_clear, new_box_clear, 1)

if errors:
    print('BLADE ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f1, 'w') as fh:
    fh.write(content1)
print('BLADE: Admin recipient filter UI + JS added')

# ==============================
# PATCH 2: Controller
# ==============================
f2 = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/CustomershippingController.php'
with open(f2, 'r') as fh:
    content2 = fh.read()

errors2 = []

# --- 2A: Add recipient_filter to the query ---
old_box_filter = """            if (!empty($request->box_no) && !empty($request->start_date)) {
                $boxSearchTerm = '%'.$request->box_no.'%';
                $queryAll->whereRaw("box_no like ?", [$boxSearchTerm]);
            }"""

new_box_filter = """            if (!empty($request->box_no) && !empty($request->start_date)) {
                $boxSearchTerm = '%'.$request->box_no.'%';
                $queryAll->whereRaw("box_no like ?", [$boxSearchTerm]);
            }

            // Recipient filter
            if (!empty($request->recipient_filter)) {
                if ($request->recipient_filter === '__empty__') {
                    $queryAll->where(function($q) {
                        $q->whereNull('delivery_fullname')->orWhere('delivery_fullname', '');
                    });
                } else {
                    $queryAll->where('delivery_fullname', $request->recipient_filter);
                }
            }"""

if old_box_filter not in content2:
    errors2.append('CONTROLLER: box_no filter block not found')
else:
    content2 = content2.replace(old_box_filter, new_box_filter, 1)

# --- 2B: Add getAdminRecipients method before last closing brace ---
last_brace = content2.rfind('}')
new_method = r"""
    public function getAdminRecipients(Request $request)
    {
        $query = Customershipping::where('excel_status', 1);

        if (!empty($request->start_date)) {
            $query->whereRaw('DATE(etd) = ?', [$request->start_date]);
        }

        if (!empty($request->customerno)) {
            $searchTerm = '%' . $request->customerno . '%';
            $query->where('customerno', 'LIKE', $searchTerm);
        }

        $recipients = $query->selectRaw("COALESCE(NULLIF(TRIM(delivery_fullname), ''), '__empty__') as recipient_name")
            ->selectRaw('COUNT(id) as cnt')
            ->groupBy('recipient_name')
            ->orderByRaw('cnt DESC')
            ->get()
            ->map(function($item) {
                $name = $item->recipient_name;
                if ($name === '__empty__') {
                    return ['name' => '', 'label' => 'ยังไม่ระบุผู้รับ', 'count' => $item->cnt, 'value' => '__empty__'];
                }
                return ['name' => $name, 'label' => $name, 'count' => $item->cnt, 'value' => $name];
            });

        return response()->json(['recipients' => $recipients]);
    }

"""
content2 = content2[:last_brace] + new_method + content2[last_brace:]

if errors2:
    print('CONTROLLER ERRORS: ' + ', '.join(errors2))
    sys.exit(1)

with open(f2, 'w') as fh:
    fh.write(content2)
print('CONTROLLER: recipient filter + getAdminRecipients added')

# ==============================
# PATCH 3: Routes
# ==============================
f3 = '/var/www/vhosts/skjjapanshipping.com/backoffice/routes/web.php'
with open(f3, 'r') as fh:
    content3 = fh.read()

old_route = "Route::post('fetchcustomershippings', 'CustomershippingController@fetchCustomershippings')->name('fetch.customershippings');"

new_route = """Route::post('fetchcustomershippings', 'CustomershippingController@fetchCustomershippings')->name('fetch.customershippings');
    Route::post('fetch-admin-recipients', 'CustomershippingController@getAdminRecipients')->name('fetch.admin.recipients');"""

if old_route not in content3:
    print('ROUTES ERROR: fetch.customershippings route not found')
    sys.exit(1)

content3 = content3.replace(old_route, new_route, 1)

with open(f3, 'w') as fh:
    fh.write(content3)
print('ROUTES: admin fetch-recipients route added')

print('\n=== ALL PATCHES APPLIED SUCCESSFULLY ===')
