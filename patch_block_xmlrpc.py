#!/usr/bin/env python3
"""Block xmlrpc.php via .htaccess"""

path = '/var/www/vhosts/skjjapanshipping.com/httpdocs/.htaccess'
with open(path, 'r') as f:
    content = f.read()

block_rule = """# BEGIN Block xmlrpc.php
<Files xmlrpc.php>
    Order Deny,Allow
    Deny from all
</Files>
# END Block xmlrpc.php

"""

if 'Block xmlrpc.php' not in content:
    content = block_rule + content
    with open(path, 'w') as f:
        f.write(content)
    print('Added xmlrpc.php block rule to .htaccess')
else:
    print('xmlrpc.php block rule already exists')
