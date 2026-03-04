@extends('layouts.app')

@section('template_title')
    สร้างคำขอสั่งซื้อ
@endsection

@section('extra-css')
    <style>
        html, body { overflow-x: hidden !important; width: 100% !important; max-width: 100vw !important; }
        .wrapper { display: flex !important; flex-direction: row !important; min-height: 100vh; position: relative !important; width: 100vw !important; overflow-x: hidden !important; }
        .sidebar-modern { position: fixed !important; top: 0 !important; left: 0 !important; width: 260px !important; height: 100vh !important; z-index: 1001 !important; display: flex !important; flex-direction: column !important; overflow: hidden !important; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-modern .sidebar-wrapper { flex: 1 !important; overflow-y: auto !important; overflow-x: hidden !important; position: relative !important; height: auto !important; padding-bottom: 20px !important; width: 100% !important; -ms-overflow-style: none; scrollbar-width: none; }
        .sidebar-modern .sidebar-wrapper::-webkit-scrollbar { display: none; }
        .main-panel { margin-left: 260px !important; width: calc(100% - 260px) !important; background: #f1f5f9 !important; min-height: 100vh !important; padding: 0 !important; position: relative !important; float: none !important; flex: 1 !important; overflow-x: hidden !important; }
        .navbar-modern, .navbar, .navbar-expand-lg, .panel-header, .main-panel > .panel-header { display: none !important; }
        .main-panel > .content { display: block !important; padding: 0 !important; margin: 0 !important; width: 100% !important; height: 100% !important; }

        .form-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 24px; margin-bottom: 20px; }
        .form-card h5 { color: #1D8AC9; font-weight: 700; margin-bottom: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; }
        .form-card .form-group label { font-weight: 600; color: #334155; }

        .product-preview { display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-top: 12px; }
        .product-preview.show { display: flex; gap: 16px; align-items: center; }
        .product-preview img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .product-preview .info h6 { margin: 0 0 4px; font-weight: 600; }
        .product-preview .info small { color: #64748b; }

        .site-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .site-mercari { background: #ff4655; color: #fff; }
        .site-yahoo { background: #7b0099; color: #fff; }
        .site-rakuten { background: #bf0000; color: #fff; }
        .site-amazon { background: #ff9900; color: #000; }
        .site-paypay { background: #ff0033; color: #fff; }
        .site-other { background: #6c757d; color: #fff; }

        .btn-fetch { background: #1D8AC9; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-fetch:hover { background: #1672a8; color: #fff; }
        .btn-fetch.loading { opacity: 0.7; pointer-events: none; }

        .sidebar-logout { padding: 20px; margin-top: auto; border-top: 1px solid rgba(255, 255, 255, 0.1); background: inherit; flex-shrink: 0; display: flex; justify-content: center; }
        .sidebar-logout .logout-link { display: flex; align-items: center; gap: 12px; padding: 12px 20px; background: rgba(255, 255, 255, 0.1); border-radius: 10px; color: rgba(255, 255, 255, 0.9); text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: all 0.3s; width: 100%; justify-content: center; }
        .sidebar-logout .logout-link:hover { box-shadow: 0 4px 15px rgba(230, 57, 70, 0.4); background: rgba(230, 57, 70, 0.8) !important; }
    </style>
@endsection

@section('extra-script')
    <script>
        function detectSite(url) {
            if (url.indexOf('mercari.com') !== -1) return 'Mercari';
            if (url.indexOf('yahoo.co.jp') !== -1) return 'Yahoo Auctions';
            if (url.indexOf('rakuten.co.jp') !== -1) return 'Rakuten';
            if (url.indexOf('amazon.co.jp') !== -1) return 'Amazon JP';
            if (url.indexOf('paypayfleamarket') !== -1) return 'PayPay';
            return 'Other';
        }

        function getSiteClass(site) {
            var map = { 'Mercari': 'site-mercari', 'Yahoo Auctions': 'site-yahoo', 'Rakuten': 'site-rakuten', 'Amazon JP': 'site-amazon', 'PayPay': 'site-paypay' };
            return map[site] || 'site-other';
        }

        $(document).ready(function () {
            // Auto-detect site when URL changes
            $('#product_url').on('input change', function () {
                var url = $(this).val().trim();
                if (url) {
                    var site = detectSite(url);
                    $('#site_display').html('<span class="site-badge ' + getSiteClass(site) + '">' + site + '</span>');
                    $('input[name="site"]').val(site);
                } else {
                    $('#site_display').html('');
                }
            });

            // Fetch product info
            $('#btnFetch').on('click', function () {
                var url = $('#product_url').val().trim();
                if (!url) { alert('กรุณาวาง URL สินค้า'); return; }

                var btn = $(this);
                btn.addClass('loading').text('กำลังดึง...');

                $.ajax({
                    url: '/calc.php?action=scrape',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ url: url }),
                    success: function (data) {
                        btn.removeClass('loading').text('ดึงข้อมูล');
                        if (data.title) {
                            $('input[name="product_title"]').val(data.title);
                        }
                        if (data.price > 0) {
                            $('input[name="estimated_price_yen"]').val(data.price);
                        }
                        if (data.shipping > 0) {
                            $('input[name="shipping_jp_yen"]').val(data.shipping);
                        }
                        if (data.image) {
                            $('#product_image').val(data.image);
                            $('#imagePreviewImg').attr('src', data.image).show();
                            $('#imagePlaceholder').hide();
                            $('#imagePreviewBox').css({'border-style':'solid','border-color':'#22c55e'});
                        }

                        // Show preview
                        var preview = $('#productPreview');
                        if (data.title || data.image) {
                            var imgHtml = data.image ? '<img src="' + data.image + '" onerror="this.style.display=\'none\'">' : '';
                            var titleHtml = '<h6>' + (data.title || 'ไม่พบชื่อสินค้า') + '</h6>';
                            var priceHtml = data.price > 0 ? '<small>¥' + Number(data.price).toLocaleString() + '</small>' : '<small class="text-warning">กรุณากรอกราคาด้วยตนเอง</small>';
                            preview.html(imgHtml + '<div class="info">' + titleHtml + priceHtml + '</div>');
                            preview.addClass('show');
                        }

                        if (data.shipping_note) {
                            alert(data.shipping_note);
                        }
                    },
                    error: function () {
                        btn.removeClass('loading').text('ดึงข้อมูล');
                        alert('ไม่สามารถดึงข้อมูลได้ กรุณากรอกด้วยตนเอง');
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
    @include('layouts.partials.side-bar')
    <div class="main-panel">
        <div class="panel-header panel-header-sm"></div>
        <div class="content">
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0"><i class="fa fa-plus"></i> สร้างคำขอสั่งซื้อ</h4>
                    <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> กลับ
                    </a>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('purchase-requests.store') }}">
                    @csrf
                    <input type="hidden" name="site" value="">

                    {{-- Section 1: Product URL --}}
                    <div class="form-card">
                        <h5><i class="fa fa-link"></i> ข้อมูลสินค้า</h5>
                        <div class="form-group">
                            <label>URL สินค้า <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="url" name="product_url" id="product_url" class="form-control" placeholder="https://jp.mercari.com/item/..." value="{{ old('product_url') }}" required>
                                <div class="input-group-append">
                                    <button type="button" id="btnFetch" class="btn-fetch">
                                        <i class="fa fa-search"></i> ดึงข้อมูล
                                    </button>
                                </div>
                            </div>
                            <div id="site_display" class="mt-2"></div>
                        </div>

                        <div id="productPreview" class="product-preview"></div>

                        <div class="row mt-3">
                            <div class="col-md-2 text-center">
                                <label style="font-weight:600;color:#334155;">รูปสินค้า</label>
                                <div id="imagePreviewBox" style="width:100px;height:100px;border:2px dashed #cbd5e1;border-radius:8px;display:flex;align-items:center;justify-content:center;margin:0 auto;overflow:hidden;background:#f8fafc;">
                                    <i class="fa fa-image" style="font-size:28px;color:#cbd5e1;" id="imagePlaceholder"></i>
                                    <img id="imagePreviewImg" src="" style="width:100%;height:100%;object-fit:cover;display:none;">
                                </div>
                                <input type="hidden" name="product_image" id="product_image" value="{{ old('product_image') }}">
                            </div>
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label>ชื่อสินค้า</label>
                                    <input type="text" name="product_title" class="form-control" placeholder="ชื่อสินค้า (ดึงอัตโนมัติเมื่อกดดึงข้อมูล)" value="{{ old('product_title') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Customer & Price --}}
                    <div class="form-card">
                        <h5><i class="fa fa-user"></i> ลูกค้า & ราคา</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>รหัสลูกค้า <span class="text-danger">*</span></label>
                                    <input type="text" name="customerno" class="form-control" placeholder="ANW-XXX" value="{{ old('customerno') }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>จำนวน <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>ราคาค่าสินค้า (¥)</label>
                                    <input type="number" name="estimated_price_yen" class="form-control" placeholder="0" value="{{ old('estimated_price_yen') }}" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>ค่าส่งในญี่ปุ่น (¥)</label>
                                    <input type="number" name="shipping_jp_yen" class="form-control" placeholder="0" value="{{ old('shipping_jp_yen', 0) }}" step="0.01">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Assignment --}}
                    <div class="form-card">
                        <h5><i class="fa fa-cog"></i> การจัดการ</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>สถานะ</label>
                                    <select name="status" class="form-control">
                                        <option value="0">รอดำเนินการ</option>
                                        <option value="1">อนุมัติแล้ว</option>
                                        <option value="2">กำลังสั่งซื้อ</option>
                                        <option value="3">สั่งซื้อแล้ว</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Boss (ผู้รับผิดชอบ)</label>
                                    <select name="boss_id" class="form-control">
                                        <option value="">-- เลือก --</option>
                                        @foreach($bosses as $boss)
                                            <option value="{{ $boss->id }}">{{ $boss->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>เลขอ้างอิงการสั่งซื้อ</label>
                                    <input type="text" name="purchase_ref" class="form-control" placeholder="Order ID จาก Mercari/Yahoo" value="{{ old('purchase_ref') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>หมายเหตุลูกค้า</label>
                                    <textarea name="customer_note" class="form-control" rows="3" placeholder="หมายเหตุจากลูกค้า">{{ old('customer_note') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>หมายเหตุ Admin</label>
                                    <textarea name="admin_note" class="form-control" rows="3" placeholder="หมายเหตุสำหรับ Admin">{{ old('admin_note') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary mr-2">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
