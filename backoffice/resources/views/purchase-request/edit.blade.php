@extends('layouts.app')

@section('template_title')
    แก้ไขคำขอสั่งซื้อ — {{ $purchaseRequest->request_no }}
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

        .status-timeline { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
        .status-step { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #e2e8f0; color: #64748b; }
        .status-step.active { color: #fff; }
        .status-step.s0 { background: #ffc107; color: #000; }
        .status-step.s1 { background: #17a2b8; }
        .status-step.s2 { background: #007bff; }
        .status-step.s3 { background: #28a745; }
        .status-step.s4 { background: #6c757d; }
        .status-step.s5 { background: #343a40; }
        .status-step.s6 { background: #dc3545; }

        .product-card { display: flex; gap: 16px; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .product-card img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; }
        .product-card .info { flex: 1; }
        .product-card .info h6 { margin: 0 0 4px; font-weight: 600; font-size: 14px; }
        .product-card .info small { color: #64748b; }

        .site-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .site-mercari { background: #ff4655; color: #fff; }
        .site-yahoo { background: #7b0099; color: #fff; }
        .site-rakuten { background: #bf0000; color: #fff; }
        .site-amazon { background: #ff9900; color: #000; }
        .site-paypay { background: #ff0033; color: #fff; }
        .site-other { background: #6c757d; color: #fff; }

        .btn-open-site { display: inline-flex; align-items: center; gap: 6px; padding: 8px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; text-decoration: none; color: #fff; transition: all 0.2s; }
        .btn-open-site:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); color: #fff; text-decoration: none; }
        .btn-mercari { background: #ff4655; }
        .btn-yahoo { background: #7b0099; }
        .btn-rakuten { background: #bf0000; }
        .btn-amazon { background: #ff9900; color: #000; }
        .btn-default { background: #007bff; }

        .sidebar-logout { padding: 20px; margin-top: auto; border-top: 1px solid rgba(255, 255, 255, 0.1); background: inherit; flex-shrink: 0; display: flex; justify-content: center; }
        .sidebar-logout .logout-link { display: flex; align-items: center; gap: 12px; padding: 12px 20px; background: rgba(255, 255, 255, 0.1); border-radius: 10px; color: rgba(255, 255, 255, 0.9); text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: all 0.3s; width: 100%; justify-content: center; }
        .sidebar-logout .logout-link:hover { box-shadow: 0 4px 15px rgba(230, 57, 70, 0.4); background: rgba(230, 57, 70, 0.8) !important; }
    </style>
@endsection

@section('content')
    @include('layouts.partials.side-bar')
    <div class="main-panel">
        <div class="panel-header panel-header-sm"></div>
        <div class="content">
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0"><i class="fa fa-edit"></i> {{ $purchaseRequest->request_no }}</h4>
                    <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> กลับ
                    </a>
                </div>

                {{-- Status Timeline --}}
                <div class="status-timeline">
                    @foreach($statuses as $key => $label)
                        <span class="status-step {{ $purchaseRequest->status == $key ? 'active s'.$key : '' }}">
                            {{ $label }}
                        </span>
                    @endforeach
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

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                <form method="POST" action="{{ route('purchase-requests.update', $purchaseRequest->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="site" value="{{ $purchaseRequest->site }}">

                    {{-- Section 1: Product Info + Open Site Button --}}
                    <div class="form-card">
                        <h5><i class="fa fa-link"></i> ข้อมูลสินค้า</h5>

                        @if($purchaseRequest->product_image || $purchaseRequest->product_title)
                        <div class="product-card">
                            @if($purchaseRequest->product_image)
                                <img src="{{ $purchaseRequest->product_image }}" onerror="this.style.display='none'" alt="">
                            @endif
                            <div class="info">
                                <h6>{{ $purchaseRequest->product_title ?: 'ไม่มีชื่อสินค้า' }}</h6>
                                <small>
                                    @php
                                        $siteClass = 'site-other';
                                        if ($purchaseRequest->site == 'Mercari') $siteClass = 'site-mercari';
                                        elseif ($purchaseRequest->site == 'Yahoo Auctions') $siteClass = 'site-yahoo';
                                        elseif ($purchaseRequest->site == 'Rakuten') $siteClass = 'site-rakuten';
                                        elseif ($purchaseRequest->site == 'Amazon JP') $siteClass = 'site-amazon';
                                        elseif ($purchaseRequest->site == 'PayPay') $siteClass = 'site-paypay';
                                    @endphp
                                    <span class="site-badge {{ $siteClass }}">{{ $purchaseRequest->site }}</span>
                                </small>
                                @if($purchaseRequest->estimated_price_yen > 0)
                                    <div class="mt-1"><strong>¥{{ number_format($purchaseRequest->estimated_price_yen) }}</strong></div>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Open site button --}}
                        @php
                            $btnClass = 'btn-default';
                            if ($purchaseRequest->site == 'Mercari') $btnClass = 'btn-mercari';
                            elseif ($purchaseRequest->site == 'Yahoo Auctions') $btnClass = 'btn-yahoo';
                            elseif ($purchaseRequest->site == 'Rakuten') $btnClass = 'btn-rakuten';
                            elseif ($purchaseRequest->site == 'Amazon JP') $btnClass = 'btn-amazon';
                        @endphp
                        <a href="{{ $purchaseRequest->product_url }}" target="_blank" class="btn-open-site {{ $btnClass }}">
                            <i class="fa fa-external-link"></i> เปิดหน้าสินค้าเพื่อสั่งซื้อ
                        </a>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>URL สินค้า <span class="text-danger">*</span></label>
                                    <input type="url" name="product_url" class="form-control" value="{{ $purchaseRequest->product_url }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 text-center">
                                <label style="font-weight:600;color:#334155;">รูปสินค้า</label>
                                <div style="width:100px;height:100px;border:2px {{ $purchaseRequest->product_image ? 'solid #22c55e' : 'dashed #cbd5e1' }};border-radius:8px;display:flex;align-items:center;justify-content:center;margin:0 auto;overflow:hidden;background:#f8fafc;">
                                    @if($purchaseRequest->product_image)
                                        <img src="{{ $purchaseRequest->product_image }}" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'">
                                    @else
                                        <i class="fa fa-image" style="font-size:28px;color:#cbd5e1;"></i>
                                    @endif
                                </div>
                                <input type="hidden" name="product_image" value="{{ $purchaseRequest->product_image }}">
                            </div>
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label>ชื่อสินค้า</label>
                                    <input type="text" name="product_title" class="form-control" value="{{ $purchaseRequest->product_title }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Customer & Price --}}
                    <div class="form-card">
                        <h5><i class="fa fa-yen"></i> ลูกค้า & ราคา</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>รหัสลูกค้า <span class="text-danger">*</span></label>
                                    <input type="text" name="customerno" class="form-control" value="{{ $purchaseRequest->customerno }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>จำนวน <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" class="form-control" value="{{ $purchaseRequest->quantity }}" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>ราคาค่าสินค้า (¥)</label>
                                    <input type="number" name="estimated_price_yen" class="form-control" value="{{ $purchaseRequest->estimated_price_yen }}" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>ราคาซื้อจริง (¥)</label>
                                    <input type="number" name="actual_price_yen" class="form-control" value="{{ $purchaseRequest->actual_price_yen }}" step="0.01" placeholder="กรอกหลังซื้อ">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>ค่าส่งในญี่ปุ่น (¥)</label>
                                    <input type="number" name="shipping_jp_yen" class="form-control" value="{{ $purchaseRequest->shipping_jp_yen }}" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>เรท (¥ → ฿)</label>
                                    <input type="number" name="rate" class="form-control" value="{{ $purchaseRequest->rate }}" step="0.0001" placeholder="auto">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Status & Assignment --}}
                    <div class="form-card">
                        <h5><i class="fa fa-cog"></i> สถานะ & การจัดการ</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>สถานะ</label>
                                    <select name="status" class="form-control">
                                        @foreach($statuses as $key => $label)
                                            <option value="{{ $key }}" {{ $purchaseRequest->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Boss (ผู้รับผิดชอบ)</label>
                                    <select name="boss_id" class="form-control">
                                        <option value="">-- เลือก --</option>
                                        @foreach($bosses as $boss)
                                            <option value="{{ $boss->id }}" {{ $purchaseRequest->boss_id == $boss->id ? 'selected' : '' }}>{{ $boss->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>เลขอ้างอิงการสั่งซื้อ</label>
                                    <input type="text" name="purchase_ref" class="form-control" value="{{ $purchaseRequest->purchase_ref }}" placeholder="Order ID จาก Mercari/Yahoo">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>หมายเหตุลูกค้า</label>
                                    <textarea name="customer_note" class="form-control" rows="3">{{ $purchaseRequest->customer_note }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>หมายเหตุ Admin</label>
                                    <textarea name="admin_note" class="form-control" rows="3">{{ $purchaseRequest->admin_note }}</textarea>
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
