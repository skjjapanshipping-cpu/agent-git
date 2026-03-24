#!/usr/bin/env python3
"""
Replace delivery_type_id single-select dropdown with multi-select checkbox dropdown.
Uses line-number based replacement to avoid whitespace matching issues.
"""

path = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershipping/index.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Find the start line: "this.api().columns([16]).every(function () {"
start_line = None
end_line = None
for i, line in enumerate(lines):
    if 'this.api().columns([16]).every(function ()' in line:
        start_line = i
    if start_line is not None and i > start_line:
        # Find the closing "});" that ends this columns block
        stripped = line.strip()
        if stripped == '});' and i > start_line + 5:
            end_line = i
            break

if start_line is None or end_line is None:
    print(f'FAILED: Could not find delivery_type column block (start={start_line}, end={end_line})')
    exit(1)

print(f'Found delivery_type block at lines {start_line+1}-{end_line+1}')

# Get the indentation from the start line
indent = '                    '  # match existing indentation

new_block = f"""{indent}this.api().columns([16]).every(function () {{
{indent}    var column = this;
{indent}    var $header = $(column.header()).empty();

{indent}    // Multi-select checkbox dropdown for delivery type
{indent}    var dtDropdown = $(
{indent}        '<div class="dt-multiselect-wrap" style="position:relative;display:inline-block;width:100%;">'
{indent}        + '<button type="button" class="dt-multiselect-btn" style="width:100%;text-align:left;padding:4px 8px;font-size:12px;border:1px solid #aaa;border-radius:3px;background:#fff;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'
{indent}        + '\\u0E01\\u0E32\\u0E23\\u0E08\\u0E31\\u0E14\\u0E2A\\u0E48\\u0E07(\\u0E17\\u0E31\\u0E49\\u0E07\\u0E2B\\u0E21\\u0E14) \\u25BE</button>'
{indent}        + '<div class="dt-multiselect-menu" style="display:none;position:absolute;z-index:9999;background:#fff;border:1px solid #ccc;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,.15);min-width:160px;padding:4px 0;top:100%;left:0;">'
{indent}        + '<label style="display:block;padding:4px 10px;margin:0;cursor:pointer;font-weight:normal;font-size:12px;white-space:nowrap;"><input type="checkbox" class="dt-cb-delivery" value="1" style="margin-right:6px;"> \\u0E23\\u0E31\\u0E1A\\u0E40\\u0E2D\\u0E07</label>'
{indent}        + '<label style="display:block;padding:4px 10px;margin:0;cursor:pointer;font-weight:normal;font-size:12px;white-space:nowrap;"><input type="checkbox" class="dt-cb-delivery" value="2" style="margin-right:6px;"> \\u0E17\\u0E35\\u0E48\\u0E2D\\u0E22\\u0E39\\u0E48\\u0E1B\\u0E31\\u0E08\\u0E08\\u0E38\\u0E1A\\u0E31\\u0E19</label>'
{indent}        + '<label style="display:block;padding:4px 10px;margin:0;cursor:pointer;font-weight:normal;font-size:12px;white-space:nowrap;"><input type="checkbox" class="dt-cb-delivery" value="3" style="margin-right:6px;"> \\u0E40\\u0E1E\\u0E34\\u0E48\\u0E21\\u0E17\\u0E35\\u0E48\\u0E2D\\u0E22\\u0E39\\u0E48\\u0E40\\u0E2D\\u0E07</label>'
{indent}        + '<hr style="margin:4px 0;border:none;border-top:1px solid #eee;">'
{indent}        + '<div style="text-align:center;padding:2px 6px;">'
{indent}        + '<button type="button" class="dt-cb-delivery-apply btn btn-xs btn-primary" style="font-size:11px;padding:2px 12px;">\\u0E15\\u0E01\\u0E25\\u0E07</button>'
{indent}        + '</div>'
{indent}        + '</div>'
{indent}        + '</div>'
{indent}    );
{indent}    $header.append(dtDropdown);

{indent}    // Toggle dropdown menu
{indent}    dtDropdown.find('.dt-multiselect-btn').on('click', function(e) {{
{indent}        e.stopPropagation();
{indent}        var $menu = dtDropdown.find('.dt-multiselect-menu');
{indent}        $menu.toggle();
{indent}    }});

{indent}    // Apply filter on button click
{indent}    dtDropdown.find('.dt-cb-delivery-apply').on('click', function(e) {{
{indent}        e.stopPropagation();
{indent}        dtDropdown.find('.dt-multiselect-menu').hide();
{indent}        var checked = dtDropdown.find('.dt-cb-delivery:checked');
{indent}        if (checked.length === 0 || checked.length === 3) {{
{indent}            dtDropdown.find('.dt-multiselect-btn').text('\\u0E01\\u0E32\\u0E23\\u0E08\\u0E31\\u0E14\\u0E2A\\u0E48\\u0E07(\\u0E17\\u0E31\\u0E49\\u0E07\\u0E2B\\u0E21\\u0E14) \\u25BE');
{indent}        }} else {{
{indent}            var labels = [];
{indent}            checked.each(function() {{ labels.push($(this).parent().text().trim()); }});
{indent}            dtDropdown.find('.dt-multiselect-btn').text(labels.join(', ') + ' \\u25BE');
{indent}        }}
{indent}        $('#shipping-export').addClass('disabled');
{indent}        dataTable.ajax.reload(null, false);
{indent}        updateInvoiceButtonState();
{indent}    }});

{indent}    // Close dropdown when clicking outside
{indent}    $(document).on('click', function(e) {{
{indent}        if (!$(e.target).closest('.dt-multiselect-wrap').length) {{
{indent}            $('.dt-multiselect-menu').hide();
{indent}        }}
{indent}    }});

{indent}    // Highlight on hover
{indent}    dtDropdown.find('label').hover(
{indent}        function() {{ $(this).css('background', '#f0f4ff'); }},
{indent}        function() {{ $(this).css('background', ''); }}
{indent}    );
{indent}}});
"""

# Replace lines
new_lines = lines[:start_line] + [new_block] + lines[end_line+1:]

with open(path, 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print('SUCCESS: Replaced delivery_type dropdown with multi-select checkbox dropdown')
