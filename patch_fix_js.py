import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# === FIX 1: Add thai-address-search.js script tag before extra-script section ===
old_extra_script = "@section('extra-script')\n    <script>"
new_extra_script = "@section('extra-script')\n    <script src=\"{{ asset('js/thai-address-search.js') }}\"></script>\n    <script>"

if old_extra_script not in content:
    errors.append('extra-script section not found')
else:
    content = content.replace(old_extra_script, new_extra_script, 1)

# === FIX 2: Fix customer search API URL from /skjtrack/search-customer to /api/address/searchCustomerAddress ===
content = content.replace(
    "$.get('/skjtrack/search-customer', { q: query, field: 'name' }",
    "$.get('/api/address/searchCustomerAddress', { term: query, field: 'delivery_fullname' }"
)
content = content.replace(
    "$.get('/skjtrack/search-customer', { q: query, field: 'mobile' }",
    "$.get('/api/address/searchCustomerAddress', { term: query, field: 'delivery_mobile' }"
)

# === FIX 3: Fix search result rendering to use the same format as thai-address-search.js ===
# The API returns objects with: text, fullname, mobile, address, province, amphoe, tambon, zipcode
old_name_render = """                        data.forEach(function(c) {
                            $results.append('<div class="search-result-item" data-customer=\\'' + JSON.stringify(c) + '\\'>' + c.name + ' - ' + (c.mobile || '') + '</div>');
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
                $.get('/api/address/searchCustomerAddress', { term: query, field: 'delivery_mobile' }, function(data) {
                    var $results = $('#batch_mobile-results').empty();
                    if (data.length > 0) {
                        data.forEach(function(c) {
                            $results.append('<div class="search-result-item" data-customer=\\'' + JSON.stringify(c) + '\\'>' + c.name + ' - ' + (c.mobile || '') + '</div>');
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
    }"""

new_name_render = """                        data.forEach(function(c) {
                            var $item = $('<div>').addClass('search-result-item')
                                .text(c.text || (c.fullname + ' - ' + (c.mobile || '')))
                                .data('fullname', c.fullname || '').data('mobile', c.mobile || '')
                                .data('address', c.address || '').data('province', c.province || '')
                                .data('amphoe', c.amphoe || '').data('tambon', c.tambon || '')
                                .data('zipcode', c.zipcode || '');
                            $results.append($item);
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
                $.get('/api/address/searchCustomerAddress', { term: query, field: 'delivery_mobile' }, function(data) {
                    var $results = $('#batch_mobile-results').empty();
                    if (data.length > 0) {
                        data.forEach(function(c) {
                            var $item = $('<div>').addClass('search-result-item')
                                .text(c.text || ((c.fullname || '') + ' - ' + (c.mobile || '')))
                                .data('fullname', c.fullname || '').data('mobile', c.mobile || '')
                                .data('address', c.address || '').data('province', c.province || '')
                                .data('amphoe', c.amphoe || '').data('tambon', c.tambon || '')
                                .data('zipcode', c.zipcode || '');
                            $results.append($item);
                        });
                        $results.show();
                    } else { $results.hide(); }
                });
            }, 300);
        });

        // Handle click on search result
        $(document).off('click', '#batch_fullname-results .search-result-item, #batch_mobile-results .search-result-item')
            .on('click', '#batch_fullname-results .search-result-item, #batch_mobile-results .search-result-item', function() {
            var $this = $(this);
            $('#batch_fullname').val($this.data('fullname') || '');
            $('#batch_mobile').val($this.data('mobile') || '');
            $('#batch_address').val($this.data('address') || '');
            $('#batch_subdistrict').val($this.data('tambon') || '');
            $('#batch_district').val($this.data('amphoe') || '');
            $('#batch_province').val($this.data('province') || '');
            $('#batch_postcode').val($this.data('zipcode') || '');
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
    }"""

if old_name_render not in content:
    errors.append('customer search render block not found')
else:
    content = content.replace(old_name_render, new_name_render)

# === FIX 4: Fix the Thai address search to also use the correct search-results CSS class ===
# The search-results divs use class "search-results" but initThaiAddressSearch expects
# result items with class "search-item". My modal uses "search-result-item".
# Actually the existing thai-address-search.js creates items with class "search-item"
# and the click handler listens for ".search-item" globally.
# My batch_subdistrict-results etc. divs will get populated by initThaiAddressSearch
# which creates search-item elements. That should work fine.

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('JS fixes applied successfully')
