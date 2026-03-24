import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

# Add mobile CSS for recipient filter - hide label, set flex
old_mobile = """            #date-filter-group { flex: 4; }
            #length-container { flex: 2; min-width: 50px; }
            #filter-container { flex: 4; }"""

new_mobile = """            #date-filter-group { flex: 4; }
            #recipient-filter-group { flex: 3; }
            #length-container { flex: 2; min-width: 50px; }
            #filter-container { flex: 4; }

            #recipient_filter {
                padding: 0 5px !important;
                font-size: 13px !important;
                height: 40px !important;
            }"""

if old_mobile not in content:
    print('ERROR: mobile CSS block not found')
    sys.exit(1)

content = content.replace(old_mobile, new_mobile)

with open(f, 'w') as fh:
    fh.write(content)

print('Mobile CSS patched successfully')
