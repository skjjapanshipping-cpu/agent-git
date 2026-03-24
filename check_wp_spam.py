#!/usr/bin/env python3
"""Check WordPress DB for spam posts"""
import subprocess, json

cmd = """python3 -c "
import pymysql
conn = pymysql.connect(host='localhost', user='skjjapan_wp426', password='p067Q6co?', database='skjjapan_wp426')
cur = conn.cursor()

# Check recent posts
cur.execute('''SELECT ID, post_title, post_name, post_status, post_date, post_type 
FROM wpl9_posts WHERE post_status=\"publish\" AND post_type=\"post\" 
ORDER BY ID DESC LIMIT 20''')
print('=== RECENT PUBLISHED POSTS ===')
for r in cur.fetchall():
    print(r)

# Check for casino/spam posts
cur.execute('''SELECT COUNT(*) FROM wpl9_posts WHERE post_title LIKE \"%casino%\" OR post_title LIKE \"%gambling%\" OR post_title LIKE \"%poker%\" OR post_title LIKE \"%slot%\" OR post_title LIKE \"%betting%\"''')
print('\\n=== SPAM POST COUNT ===')
print(cur.fetchone())

# Check suspicious users
cur.execute('SELECT ID, user_login, user_email, user_registered FROM wpl9_users ORDER BY ID DESC LIMIT 10')
print('\\n=== USERS ===')
for r in cur.fetchall():
    print(r)

conn.close()
"
"""
print(cmd)
