@role('admin')
<div class="dashboard-content">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h2>ยินดีต้อนรับ, {{ Auth::user()->name }}!</h2>
            <p>ระบบจัดการ SKJ Japan Shipping</p>
        </div>
        <div class="welcome-date">
            {{ \Carbon\Carbon::now()->locale('th')->translatedFormat('วันl ที่ j F Y') }}
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <a href="{{ route('users.index') }}" class="stat-card">
            <div class="stat-icon blue">
                <i class="fa fa-users"></i>
            </div>
            <div class="stat-value">{{ App\User::userCount() }}</div>
            <div class="stat-label">Users</div>
        </a>

        <a href="{{ route('dailyrates.index') }}" class="stat-card">
            <div class="stat-icon green">
                <i class="fa fa-jpy"></i>
            </div>
            <div class="stat-value">{{ \App\Models\Dailyrate::curRatePerBath() }} /
                {{ \App\Models\Dailyrate::getCodRate() }}</div>
            <div class="stat-label">Rate / COD Rate</div>
        </a>

        <a href="{{ route('tracks.index') }}" class="stat-card">
            <div class="stat-icon orange">
                <i class="fa fa-th-large"></i>
            </div>
            <div class="stat-value">Stock</div>
            <div class="stat-label">คลังสินค้า</div>
        </a>

        <a href="{{ route('customerorders.index') }}" class="stat-card">
            <div class="stat-icon red">
                <i class="fa fa-shopping-cart"></i>
            </div>
            <div class="stat-value">Orders</div>
            <div class="stat-label">คำสั่งซื้อ</div>
        </a>

        <a href="{{ route('purchase-requests.index') }}" class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#ff4655,#7b0099);">
                <i class="fa fa-shopping-bag"></i>
            </div>
            <div class="stat-value">Purchase</div>
            <div class="stat-label">คำขอสั่งซื้อ</div>
        </a>

        <a href="{{ route('customers.index') }}" class="stat-card">
            <div class="stat-icon purple">
                <i class="fa fa-address-book"></i>
            </div>
            <div class="stat-value">Customer</div>
            <div class="stat-label">ลูกค้า</div>
        </a>

        <a href="{{ route('customershippings.index') }}" class="stat-card">
            <div class="stat-icon cyan">
                <i class="fa fa-truck"></i>
            </div>
            <div class="stat-value">Shipping</div>
            <div class="stat-label">การจัดส่ง</div>
        </a>
    </div>

    <!-- Quick Actions -->
    <div class="section-title">
        <span class="title-bar"></span>
        Quick Actions
    </div>
    <div class="quick-actions">
        <a href="https://skjjapanshipping.com/skjtrack/customershippings/create" target="_blank" class="action-card">
            <div class="action-icon gradient-blue">
                <i class="fa fa-plus"></i>
            </div>
            <div class="action-content">
                <h3>เพิ่มรายการพัสดุรอบปิดตู้</h3>
                <p>เพิ่มสินค้าเข้าคลัง</p>
            </div>
        </a>
        <a href="https://skjjapanshipping.com/skjtrack/tracking" target="_blank" class="action-card">
            <div class="action-icon gradient-red">
                <i class="fa fa-search"></i>
            </div>
            <div class="action-content">
                <h3>ค้นหาพัสดุ</h3>
                <p>ติดตามสถานะพัสดุ</p>
            </div>
        </a>
    </div>

    <!-- Mobile Logout Button -->
    <div class="mobile-logout-section">
        <a href="{{ route('logout') }}" class="mobile-logout-btn"
            onclick="event.preventDefault(); document.getElementById('logout-form-dashboard').submit();">
            <i class="fa fa-sign-out"></i>
            <span>ออกจากระบบ</span>
        </a>
        <form id="logout-form-dashboard" action="{{ route('logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    </div>
</div>
@endrole

@role('warden')
<div class="dashboard-content">
    <div class="welcome-banner">
        <div class="welcome-text">
            <h2>ยินดีต้อนรับ, {{ Auth::user()->name }}!</h2>
            <p>Warden Panel</p>
        </div>
    </div>

    <div class="stats-grid">
        <a href="{{ route('warden-process-gc') }}" class="stat-card">
            <div class="stat-icon orange">
                <i class="fa fa-truck"></i>
            </div>
            <div class="stat-value">{{ App\Capturegc::gcAlottedCount() }}</div>
            <div class="stat-label">Alotted GC</div>
        </a>
    </div>

    <!-- Mobile Logout Button -->
    <div class="mobile-logout-section">
        <a href="{{ route('logout') }}" class="mobile-logout-btn"
            onclick="event.preventDefault(); document.getElementById('logout-form-dashboard-w').submit();">
            <i class="fa fa-sign-out"></i>
            <span>ออกจากระบบ</span>
        </a>
        <form id="logout-form-dashboard-w" action="{{ route('logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    </div>
</div>
@endrole

@role('user')
<div class="dashboard-content">
    <div class="welcome-banner">
        <div class="welcome-text">
            <h2>ยินดีต้อนรับ, {{ Auth::user()->name }}!</h2>
            <p>ระบบติดตามพัสดุ SKJ Japan Shipping</p>
        </div>
    </div>

    <div class="quick-actions">
        <a href="{{ route('clear.session') }}" class="action-card">
            <div class="action-icon gradient-blue">
                <i class="fa fa-box"></i>
            </div>
            <div class="action-content">
                <h3>My Shipping</h3>
                <p>ดูรายการพัสดุของคุณ</p>
            </div>
        </a>
        <a href="{{ route('orderview.index') }}" class="action-card">
            <div class="action-icon gradient-red">
                <i class="fa fa-shopping-cart"></i>
            </div>
            <div class="action-content">
                <h3>My Orders</h3>
                <p>ดูรายการสั่งซื้อของคุณ</p>
            </div>
        </a>
    </div>

    <!-- Mobile Logout Button -->
    <div class="mobile-logout-section">
        <a href="{{ route('logout') }}" class="mobile-logout-btn"
            onclick="event.preventDefault(); document.getElementById('logout-form-dashboard-u').submit();">
            <i class="fa fa-sign-out"></i>
            <span>ออกจากระบบ</span>
        </a>
        <form id="logout-form-dashboard-u" action="{{ route('logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    </div>
</div>
@endrole