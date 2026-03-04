#!/bin/bash

# Script สำหรับตั้งค่า Laravel Scheduler (Production)
# จะรัน schedule:run ทุกนาที และ Laravel จะจัดการเวลาเอง

echo "ตั้งค่า Laravel Scheduler..."

# ดึง path อัตโนมัติจากตำแหน่งที่รัน script
PROJECT_PATH=$(pwd)

echo "ใช้ Production Path: $PROJECT_PATH"

if [ ! -f "$PROJECT_PATH/artisan" ]; then
    echo "❌ ไม่พบไฟล์ artisan ใน path: $PROJECT_PATH"
    echo "กรุณารัน script จาก directory ของ Laravel project"
    echo "ตัวอย่าง: cd /var/www/vhosts/skjjapanshipping.com/backoffice && bash setup_laravel_scheduler.sh"
    exit 1
fi

echo "✅ พบไฟล์ artisan ใน path: $PROJECT_PATH"

# ตรวจสอบ PHP path
PHP_PATH=$(which php)
if [ -z "$PHP_PATH" ]; then
    echo "⚠️  ไม่พบ PHP ในระบบ"
    echo "ลองหา PHP ในตำแหน่งอื่น..."
    
    # ลองหา PHP ในตำแหน่งที่พบบ่อย
    POSSIBLE_PATHS=(
        "/usr/bin/php"
        "/usr/local/bin/php"
        "/opt/php/bin/php"
        "/usr/local/php/bin/php"
        "/usr/local/php8.1/bin/php"
        "/usr/local/php8.2/bin/php"
        "/usr/local/php8.3/bin/php"
    )
    
    for path in "${POSSIBLE_PATHS[@]}"; do
        if [ -x "$path" ]; then
            PHP_PATH="$path"
            echo "✅ พบ PHP ที่: $PHP_PATH"
            break
        fi
    done
    
    if [ -z "$PHP_PATH" ]; then
        echo "❌ ไม่พบ PHP ในตำแหน่งใดๆ"
        echo "กรุณาระบุ PHP path:"
        read -r PHP_PATH
        
        if [ ! -x "$PHP_PATH" ]; then
            echo "❌ ไม่พบ PHP ที่ path: $PHP_PATH"
            exit 1
        fi
    fi
else
    echo "✅ ใช้ PHP path: $PHP_PATH"
fi

# แสดง PHP version
PHP_VERSION=$($PHP_PATH -v | head -n 1)
echo "📦 PHP Version: $PHP_VERSION"

# สร้าง log directory ถ้าไม่มี
mkdir -p "$PROJECT_PATH/storage/logs"
echo "✅ สร้าง log directory แล้ว"

# แสดง scheduled tasks ที่มี
echo ""
echo "📋 Scheduled Tasks ที่ตั้งค่าไว้:"
cd "$PROJECT_PATH" && $PHP_PATH artisan schedule:list

# เพิ่ม Laravel Scheduler cronjob
CRON_JOB="* * * * * cd $PROJECT_PATH && $PHP_PATH artisan schedule:run >> /dev/null 2>&1"

# ตรวจสอบว่า cronjob มีอยู่แล้วหรือไม่
if crontab -l 2>/dev/null | grep -q "$PROJECT_PATH.*schedule:run"; then
    echo ""
    echo "⚠️  พบ Laravel Scheduler cronjob ที่มีอยู่แล้ว:"
    crontab -l 2>/dev/null | grep "$PROJECT_PATH.*schedule:run"
    echo ""
    echo "ต้องการแทนที่หรือไม่? (y/n)"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        # ลบ cronjob เก่า
        crontab -l 2>/dev/null | grep -v "$PROJECT_PATH.*schedule:run" | crontab -
        echo "✅ ลบ cronjob เก่าแล้ว"
    else
        echo "⏭️  ยกเลิกการตั้งค่า (ใช้ cronjob เดิมต่อ)"
        exit 0
    fi
fi

# ลบ cronjob แบบเก่าถ้ามี (images:cleanup โดยตรง)
if crontab -l 2>/dev/null | grep -q "images:cleanup"; then
    echo ""
    echo "⚠️  พบ cronjob แบบเก่า (images:cleanup โดยตรง)"
    echo "ต้องการลบและใช้ Laravel Scheduler แทนหรือไม่? (y/n)"
    read -r response
    if [[ "$response" =~ ^[Yy]$ ]]; then
        crontab -l 2>/dev/null | grep -v "images:cleanup" | crontab -
        echo "✅ ลบ cronjob แบบเก่าแล้ว"
    fi
fi

# เพิ่ม cronjob ใหม่
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

echo ""
echo "🎉 ตั้งค่า Laravel Scheduler สำเร็จ!"
echo ""
echo "📌 รายละเอียด:"
echo "   - Laravel Scheduler จะทำงานทุกนาที"
echo "   - ตรวจสอบ schedule และรันตามเวลาที่กำหนดใน Kernel.php"
echo "   - ปัจจุบันมี scheduled tasks:"
echo "     • images:cleanup - รันทุกวันเวลา 03:00 น."
echo ""
echo "📂 Log files:"
echo "   - Laravel log: $PROJECT_PATH/storage/logs/laravel.log"
echo "   - Cron log: ดูด้วย 'grep CRON /var/log/syslog'"
echo ""

# แสดง cronjob ที่ตั้งค่าไว้
echo "✅ Cronjob ที่ตั้งค่าไว้:"
crontab -l | grep "schedule:run"
echo ""

# ทดสอบรัน schedule
echo "🧪 ต้องการทดสอบรัน scheduler ตอนนี้หรือไม่? (y/n)"
read -r test_response
if [[ "$test_response" =~ ^[Yy]$ ]]; then
    echo "กำลังทดสอบ..."
    cd "$PROJECT_PATH" && $PHP_PATH artisan schedule:run --verbose
fi

echo ""
echo "✨ เสร็จสิ้น! Laravel Scheduler พร้อมใช้งานแล้ว"

