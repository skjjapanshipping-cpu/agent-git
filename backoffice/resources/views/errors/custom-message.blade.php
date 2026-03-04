@if(Session::has('primary') || Session::has('info') || Session::has('success') || Session::has('warning') || Session::has('danger') || Session::has('error'))
<div class="global-alert-container" id="globalAlertContainer">
    @if (session('primary'))
    <div class="alert alert-primary alert-dismissible fade show global-alert" role="alert">
        <div class="alert-icon"><i class="fa fa-info-circle"></i></div>
        <div class="alert-text">{{ session('primary') }}</div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="fa fa-times"></i>
        </button>
    </div>
    @endif

    @if (session('info'))
    <div class="alert alert-info alert-dismissible fade show global-alert" role="alert">
        <div class="alert-icon"><i class="fa fa-info-circle"></i></div>
        <div class="alert-text">{{ session('info') }}</div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="fa fa-times"></i>
        </button>
    </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show global-alert" role="alert">
        <div class="alert-icon"><i class="fa fa-check-circle"></i></div>
        <div class="alert-text">{{ session('success') }}</div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="fa fa-times"></i>
        </button>
    </div>
    @endif

    @if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show global-alert" role="alert">
        <div class="alert-icon"><i class="fa fa-exclamation-triangle"></i></div>
        <div class="alert-text">{{ session('warning') }}</div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="fa fa-times"></i>
        </button>
    </div>
    @endif

    @if (session('danger'))
    <div class="alert alert-danger alert-dismissible fade show global-alert" role="alert">
        <div class="alert-icon"><i class="fa fa-exclamation-circle"></i></div>
        <div class="alert-text">{{ session('danger') }}</div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="fa fa-times"></i>
        </button>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show global-alert" role="alert">
        <div class="alert-icon"><i class="fa fa-exclamation-circle"></i></div>
        <div class="alert-text">{{ session('error') }}</div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="fa fa-times"></i>
        </button>
    </div>
    @endif
</div>
<script>
(function(){
    var alerts = document.querySelectorAll('.global-alert');
    alerts.forEach(function(alert){
        setTimeout(function(){
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function(){ alert.remove(); }, 500);
        }, 4000);
    });
})();
</script>
@endif
