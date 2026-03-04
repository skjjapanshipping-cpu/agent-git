<!-- Navbar -->
<nav class="navbar-modern">
  <div class="navbar-left">
    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle-btn" id="navSidebarToggle" aria-label="Toggle sidebar">
      <i class="fa fa-bars"></i>
    </button>
    
  </div>
  <div class="navbar-right">
    <div class="navbar-user-info">
      <span class="navbar-username">{{ Auth::user()->name }}</span>
      <span class="navbar-role">{{ ucfirst(Auth::user()->getRoleNames()->first() ?? 'Member') }}</span>
    </div>
    <a href="{{ route('logout') }}" class="logout-btn"
      onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
      <i class="fa fa-sign-out"></i>
      <span>ออกจากระบบ</span>
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
      {{ csrf_field() }}
    </form>
  </div>
</nav>
<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay-global" id="sidebarOverlayGlobal"></div>