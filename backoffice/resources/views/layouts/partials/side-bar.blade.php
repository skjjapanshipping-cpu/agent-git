<style>
  /* ==========================================
       SIDEBAR UNIVERSAL MOBILE FIX
       ========================================== */
  @media (max-width: 992px) {

    /* 1. FORCE SIDEBAR VISIBILITY & SIZE */
    .sidebar-modern {
      width: 260px !important;
      display: flex !important;
      flex-direction: column !important;
      transform: translateX(-100%);
      transition: transform 0.3s ease;
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      height: 100vh !important;
      z-index: 9999 !important;
      /* Highest priority */
      background: #1a1a2e !important;
      /* Ensure background */
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.5) !important;
    }

    .sidebar-modern.show {
      transform: translateX(0) !important;
    }

    /* 1.5 FORCE HEADER VISIBILITY */
    .sidebar-modern .sidebar-header {
      display: flex !important;
      flex-direction: column !important;
      align-items: center !important;
      justify-content: center !important;
      padding: 30px 20px 20px !important;
      background: transparent !important;
      width: 100% !important;
      opacity: 1 !important;
      visibility: visible !important;
      min-height: 150px !important;
    }

    .sidebar-modern .sidebar-header a {
      display: block !important;
      margin-bottom: 15px !important;
    }

    .sidebar-modern .sidebar-header img {
      display: block !important;
      height: 60px !important;
      width: auto !important;
      max-width: 100% !important;
      margin: 0 auto !important;
      visibility: visible !important;
      opacity: 1 !important;
    }

    /* 2. USER INFO - ALWAYS SHOW */
    .sidebar-modern .user-info {
      display: block !important;
      background: rgba(255, 255, 255, 0.1) !important;
      padding: 15px !important;
      border-radius: 12px !important;
      margin: 15px 10px !important;
      border-left: 4px solid #1D8AC9 !important;
    }

    .sidebar-modern .user-name {
      display: block !important;
      font-size: 16px !important;
      font-weight: 700 !important;
      color: #fff !important;
      margin-bottom: 5px !important;
    }

    .sidebar-modern .user-email {
      display: block !important;
      font-size: 13px !important;
      opacity: 0.9 !important;
      color: rgba(255, 255, 255, 0.8) !important;
      word-break: break-word !important;
      text-decoration: none !important;
      border: none !important;
      outline: none !important;
      box-shadow: none !important;
      background: transparent !important;
      padding-bottom: 0 !important;
      margin-bottom: 0 !important;
    }

    /* Remove any potential separator lines */
    .sidebar-modern .user-info,
    .sidebar-modern .sidebar-header,
    .sidebar-modern .user-name {
      border: none !important;
      box-shadow: none !important;
      text-decoration: none !important;
    }

    .sidebar-modern .user-info * {
      border: none !important;
      text-decoration: none !important;
    }

    /* Remove any potential separator lines */
    .sidebar-modern .user-info,
    .sidebar-modern .sidebar-header {
      border-bottom: none !important;
    }

    /* 3. MENU TEXT - ALWAYS SHOW */
    .sidebar-modern .nav li a {
      padding: 15px 20px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: flex-start !important;
      min-height: 50px !important;
    }

    .sidebar-modern .nav li a i {
      font-size: 22px !important;
      margin-right: 15px !important;
      min-width: 30px !important;
      text-align: center !important;
    }

    .sidebar-modern .nav li a span,
    .sidebar-modern .nav li a p {
      display: inline-block !important;
      font-size: 15px !important;
      font-weight: 500 !important;
      opacity: 1 !important;
      visibility: visible !important;
    }

    /* 4. ACTIVE MENU ITEM - GRADIENT BACKGROUND */
    .sidebar-modern .nav li.active>a {
      background: linear-gradient(90deg, rgba(29, 138, 201, 0.2) 0%, rgba(29, 138, 201, 0.05) 100%) !important;
      border-left: 4px solid #1D8AC9 !important;
      color: #fff !important;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
    }

    .sidebar-modern .nav li.active>a i {
      color: #1D8AC9 !important;
    }

    .sidebar-modern .nav li.active>a span {
      font-weight: 700 !important;
    }

    /* 5. LOGOUT SECTION */
    .sidebar-logout {
      margin-top: auto !important;
      padding: 20px !important;
    }

    .sidebar-logout .logout-link {
      display: flex !important;
      align-items: center !important;
      gap: 10px !important;
      padding: 15px !important;
    }

    .sidebar-logout .logout-link span {
      display: inline !important;
    }
  }
</style>
<div class="sidebar-modern" data-color="dark">
  <!-- Logo & User Info -->
  <div class="sidebar-header">
    <a href="{{ route('home') }}">
      <img src="{{ asset('img/skj-logo-icon.png') }}" alt="SKJ Japan Shipping">
    </a>
    <div class="user-info">
      <div class="user-name">{{ strtoupper(Auth::user()->customerno ?? Auth::user()->name) }}</div>
      <div class="user-email">{{ Auth::user()->email }}</div>
    </div>
  </div>

  <!-- Navigation Menu -->
  <div class="sidebar-wrapper">
    <ul class="nav">
      @role('admin')
      <li class="{{ Route::is('home') ? 'active' : '' }}">
        <a href="{{ route('home') }}">
          <i class="fa fa-dashboard"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <li class="{{ Route::is('permissions.*') || Route::is('roles.*') || Route::is('users.*') ? 'active' : '' }}">
        <a data-toggle="collapse" href="#users-menu" aria-expanded="false" class="collapsed">
          <i class="fa fa-users"></i>
          <span>Manage Users</span>
          <b class="caret"></b>
        </a>
        <div class="collapse" id="users-menu">
          <ul class="nav">
            <li class="{{ Route::is('permissions.*') ? 'active' : '' }}">
              <a href="{{ route('permissions.index') }}">
                <i class="fa fa-key"></i>
                <span>Permissions</span>
              </a>
            </li>
            <li class="{{ Route::is('roles.*') ? 'active' : '' }}">
              <a href="{{ route('roles.index') }}">
                <i class="fa fa-shield"></i>
                <span>Roles</span>
              </a>
            </li>
            <li class="{{ Route::is('users.*') ? 'active' : '' }}">
              <a href="{{ route('users.index') }}">
                <i class="fa fa-user"></i>
                <span>Users</span>
              </a>
            </li>
          </ul>
        </div>
      </li>

      <li class="{{ Route::is('tracks.*') ? 'active' : '' }}">
        <a href="{{ route('tracks.index') }}">
          <i class="fa fa-th-large"></i>
          <span>Stock</span>
        </a>
      </li>

      <li class="{{ Route::is('customerorders.*') ? 'active' : '' }}">
        <a href="{{ route('customerorders.index') }}">
          <i class="fa fa-shopping-cart"></i>
          <span>Orders</span>
        </a>
      </li>


      <li class="{{ Route::is('customers.*') ? 'active' : '' }}">
        <a href="{{ route('customers.index') }}">
          <i class="fa fa-address-book"></i>
          <span>Customers</span>
        </a>
      </li>

      <li class="{{ Route::is('customershippings.*') ? 'active' : '' }}">
        <a href="{{ route('customershippings.index') }}">
          <i class="fa fa-truck"></i>
          <span>Shipping</span>
        </a>
      </li>


      <li class="{{ Route::is('scan-history') ? 'active' : '' }}">
        <a href="{{ route('scan-history') }}">
          <i class="fa fa-history"></i>
          <span>ประวัติสแกนพัสดุ</span>
        </a>
      </li>

      <li class="{{ Route::is('qrscan.print-labels') ? 'active' : '' }}">
        <a href="{{ route('qrscan.print-labels') }}">
          <i class="fa fa-barcode"></i>
          <span>พิมพ์สติ๊กเกอร์</span>
        </a>
      </li>
      @endrole

      @role('user')
      <li class="{{ Route::is('shipment-analytics') ? 'active' : '' }}">
        <a href="{{ route('shipment-analytics') }}">
          <i class="fa fa-bar-chart"></i>
          <span>Shipment Analytics</span>
        </a>
      </li>
      <li class="{{ Route::is('shippingview.*') ? 'active' : '' }}">
        <a href="{{ route('clear.session') }}">
          <i class="fa fa-cube"></i>
          <span>My Shipping</span>
        </a>
      </li>
      <li class="{{ Route::is('orderview.*') ? 'active' : '' }}">
        <a href="{{ route('orderview.index') }}">
          <i class="fa fa-shopping-cart"></i>
          <span>My Orders</span>
        </a>
      </li>
      @endrole

      <li class="{{ Route::is('profile.*') ? 'active' : '' }}">
        <a href="{{ route('profile.index') }}">
          <i class="fa fa-user-circle"></i>
          <span>Profile</span>
        </a>
      </li>
    </ul>
  </div>

  <!-- Logout at bottom -->
  <div class="sidebar-logout">
    <a href="{{ route('logout') }}" class="logout-link"
      onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
      <i class="fa fa-sign-out"></i>
      <span>ออกจากระบบ</span>
    </a>
    <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" style="display: none;">
      {{ csrf_field() }}
    </form>
  </div>
</div>