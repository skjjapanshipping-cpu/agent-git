#!/usr/bin/env python3
"""Remove malware plugins from WordPress active_plugins option"""
import pymysql

conn = pymysql.connect(host='localhost', user='skjjapan_wp426', password='p067Q6co?', database='skjjapan_wp426')
cur = conn.cursor()

# Get current active plugins
cur.execute("SELECT option_value FROM wpl9_options WHERE option_name = 'active_plugins'")
row = cur.fetchone()
print('Current:', row[0][:200])

# Build clean list without malware plugins
import phpserialize
plugins = phpserialize.loads(row[0].encode())
clean = {}
idx = 0
for k, v in plugins.items():
    name = v.decode() if isinstance(v, bytes) else v
    if 'wp-security-helper' in name or 'wp-posts-cache-engine' in name:
        print(f'REMOVING: {name}')
        continue
    clean[idx] = name.encode()
    idx += 1

new_val = phpserialize.dumps(clean).decode()
print('New:', new_val[:200])

cur.execute("UPDATE wpl9_options SET option_value = %s WHERE option_name = 'active_plugins'", (new_val,))
conn.commit()
print('Updated active_plugins - removed malware')
conn.close()
