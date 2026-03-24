# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/form.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# === PATCH 1: Fix deliveryOnLoad for type 1 - show delivery_fullname ===
old_onload_type1 = """        if(_delivery_type_id==1){
            $('[name^="delivery"]').not('[name="delivery_type_id"]').addClass('d-none');
            $('[name^="delivery"]').not('[name="delivery_type_id"]').removeAttr('required');
            $('[for^="delivery"]').not('[for="delivery_type_id"]').addClass('d-none');
            $('.search-hint').addClass('d-none');"""

new_onload_type1 = """        if(_delivery_type_id==1){
            $('[name^="delivery"]').not('[name="delivery_type_id"]').addClass('d-none');
            $('[name^="delivery"]').not('[name="delivery_type_id"]').removeAttr('required');
            $('[for^="delivery"]').not('[for="delivery_type_id"]').addClass('d-none');
            $('.search-hint').addClass('d-none');
            // Show pickup person name
            $('input[name="delivery_fullname"]').removeClass('d-none');
            $('label[for="delivery_fullname"]').removeClass('d-none');"""

if old_onload_type1 not in content:
    errors.append('deliveryOnLoad type1 block not found')
else:
    content = content.replace(old_onload_type1, new_onload_type1)

# === PATCH 2: Fix deliveryLoadChange case 1 - show delivery_fullname ===
old_change_type1 = """            case '1':
                $('[name^="delivery"]').not('[name="delivery_type_id"]').addClass('d-none');
                $('[name^="delivery"]').not('[name="delivery_type_id"]').removeAttr('required');
                $('[for^="delivery"]').not('[for="delivery_type_id"]').addClass('d-none');
                $('.search-hint').addClass('d-none');
                break;"""

new_change_type1 = """            case '1':
                $('[name^="delivery"]').not('[name="delivery_type_id"]').addClass('d-none');
                $('[name^="delivery"]').not('[name="delivery_type_id"]').removeAttr('required');
                $('[for^="delivery"]').not('[for="delivery_type_id"]').addClass('d-none');
                $('.search-hint').addClass('d-none');
                // Show pickup person name
                $('input[name="delivery_fullname"]').removeClass('d-none').removeAttr('readonly');
                $('label[for="delivery_fullname"]').removeClass('d-none');
                // Clear other fields
                $('input[name="delivery_mobile"]').val('');
                $('input[name="delivery_address"]').val('');
                $('input[name="delivery_province"]').val('');
                $('input[name="delivery_district"]').val('');
                $('input[name="delivery_subdistrict"]').val('');
                $('input[name="delivery_postcode"]').val('');
                break;"""

if old_change_type1 not in content:
    errors.append('deliveryLoadChange case 1 block not found')
else:
    content = content.replace(old_change_type1, new_change_type1)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Edit page: pickup name field shown for type 1')
