<style>
    /* === Modern Form Card === */
    .modern-form-card { border:none; border-radius:12px; box-shadow:0 2px 16px rgba(0,0,0,0.08); overflow:visible; max-width:800px; margin:0 auto; }
    .modern-form-header { background:#fff !important; border-bottom:2px solid #f0f0f0; padding:14px 24px !important; border-radius:12px 12px 0 0 !important; display:flex !important; justify-content:space-between; align-items:center; }
    .modern-form-title { font-size:18px; font-weight:800; color:#1e293b; display:flex; align-items:center; gap:8px; }
    .modern-form-title i { color:#dc3545; font-size:20px; }
    .btn-back { font-size:12px; font-weight:600; color:#64748b; text-decoration:none; padding:6px 14px; border:2px solid #e2e8f0; border-radius:8px; transition:all 0.2s; display:inline-flex; align-items:center; gap:5px; }
    .btn-back:hover { border-color:#dc3545; color:#dc3545; background:#fff5f5; text-decoration:none; }
    .modern-form-card .card-body { padding:24px !important; }

    /* === Form Section Dividers === */
    .form-section { margin-bottom:24px; padding-bottom:8px; }
    .form-section-header { display:flex; align-items:center; gap:8px; margin-bottom:16px; padding-bottom:8px; border-bottom:2px solid #f1f5f9; }
    .form-section-header i { font-size:16px; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:8px; color:#fff; flex-shrink:0; }
    .form-section-header .icon-blue { background:linear-gradient(135deg,#0084FF,#0066cc); }
    .form-section-header .icon-green { background:linear-gradient(135deg,#28a745,#1e7e34); }
    .form-section-header .icon-red { background:linear-gradient(135deg,#dc3545,#c82333); }
    .form-section-header .icon-orange { background:linear-gradient(135deg,#fd7e14,#e8690a); }
    .form-section-header .icon-purple { background:linear-gradient(135deg,#6f42c1,#5a32a3); }
    .form-section-header .icon-pink { background:linear-gradient(135deg,#e91e63,#c2185b); }
    .form-section-header .icon-teal { background:linear-gradient(135deg,#20c997,#17a085); }
    .form-section-header h6 { font-size:14px; font-weight:700; color:#334155; margin:0; }

    /* === Better Form Controls === */
    .box-body .form-group { margin-bottom:14px; }
    .box-body .form-group > label,
    .box-body .form-group > .form-label { font-size:12px; font-weight:700; color:#475569; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.3px; }
    .box-body .form-control { border:2px solid #e2e8f0; border-radius:8px; padding:8px 12px; font-size:13px; transition:all 0.2s; }
    .box-body .form-control:focus { border-color:#dc3545; box-shadow:0 0 0 3px rgba(220,53,69,0.1); }
    .box-body select.form-control { appearance:auto; }
    .box-body textarea.form-control { min-height:80px; resize:vertical; }

    /* === 2-Column Grid for short fields === */
    .form-row-2col { display:grid; grid-template-columns:1fr 1fr; gap:0 16px; }

    /* === Modern Buttons === */
    .box-footer { padding:20px 0 0; border-top:2px solid #f1f5f9; margin-top:16px; display:flex; gap:10px; }
    .box-footer .btn-primary { background:linear-gradient(135deg,#dc3545,#c82333) !important; border:none !important; border-radius:8px !important; padding:10px 28px !important; font-size:14px !important; font-weight:700 !important; transition:all 0.2s !important; }
    .box-footer .btn-primary:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(220,53,69,0.3) !important; }
    .box-footer .btn-danger { background:#fff !important; color:#64748b !important; border:2px solid #e2e8f0 !important; border-radius:8px !important; padding:10px 28px !important; font-size:14px !important; font-weight:600 !important; transition:all 0.2s !important; }
    .box-footer .btn-danger:hover { border-color:#dc3545 !important; color:#dc3545 !important; }

    /* === Image Preview === */
    .box-body img[id$="_preview"] { border-radius:8px; border:2px solid #e2e8f0; margin-top:8px; }
    .box-body .form-control-file { font-size:12px; }

    /* === Mobile Responsive === */
    @media (max-width: 768px) {
        .modern-form-card { margin:0 -5px; border-radius:8px; }
        .modern-form-header { padding:12px 16px !important; }
        .modern-form-title { font-size:15px; }
        .modern-form-card .card-body { padding:16px !important; }
        .form-row-2col { grid-template-columns:1fr; gap:0; }
        .form-section-header h6 { font-size:13px; }
        .box-footer { flex-direction:column; }
        .box-footer .btn { width:100%; text-align:center; }
        .btn-back { font-size:11px; padding:5px 10px; }
    }
</style>
