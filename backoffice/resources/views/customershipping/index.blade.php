@extends('layouts.app')

@section('template_title')
    Customershipping
@endsection
@section('extra-css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Flatpickr ship icon on Mondays */
        .flatpickr-day.is-monday { position: relative; }
        .flatpickr-day.is-monday::after {
            content: '\1F6A2';
            position: absolute;
            bottom: -2px;
            right: -2px;
            font-size: 9px;
            line-height: 1;
            pointer-events: none;
        }
        .flatpickr-calendar { font-family: inherit; }
        .table td, .table th {
            white-space: nowrap; /* ปรับให้ข้อมูลในตารางไม่ขึ้นบรรทัดใหม่ */
        }
        input[type="checkbox"] {
            accent-color: #dc3545;
        }
        th,td {
            text-align: center;
        }
        .dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        
        /* ปรับ container ของ dot ให้อยู่กึ่งกลาง */
        .status-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        
        .bg-danger {
            background-color: #dc3545;
        }
        
        .bg-warning {
            background-color: #ffc107;
        }
        
        .bg-success {
            background-color: #28a745;
        }
        
        .bg-secondary {
            background-color: #6c757d;
        }
        
        .d-flex {
            display: flex !important;
        }
        
        .align-items-center {
            align-items: center !important;
        }
        
        .mr-2 {
            margin-right: 0.5rem !important;
        }
        
        /* ปรับ border และพื้นหลังวันที่เริ่มต้นให้เป็นสีแดง */
        .fp-start-date {
            border: 2px solid #dc3545 !important;
            background-color: #dc3545 !important;
            color: #ffffff !important;
            cursor: pointer !important;
        }
        .fp-start-date:focus {
            border-color: #dc3545 !important;
            background-color: #dc3545 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        .fp-start-date::placeholder { color: rgba(255,255,255,0.7) !important; }
        input.flatpickr-input[readonly] { cursor: pointer !important; }
        input[type='submit'].disabled { opacity: 0.5; pointer-events: none; }

        /* === Floating Box Search Popup === */
        .box-search-toggle { position:fixed; right:20px; bottom:100px; z-index:1050; background:#dc3545; color:#fff; border:none; border-radius:50%; width:56px; height:56px; font-size:22px; cursor:pointer; box-shadow:0 4px 16px rgba(220,53,69,0.4); transition:all 0.3s; display:flex; align-items:center; justify-content:center; }
        .box-search-toggle:hover { background:#c82333; transform:scale(1.1); }
        .box-search-panel { position:fixed; right:20px; bottom:170px; z-index:1051; background:#fff; border-radius:12px; box-shadow:0 8px 32px rgba(0,0,0,0.25); width:300px; display:none; overflow:hidden; }
        .box-search-panel.open { display:block; }
        .box-search-panel .panel-header { background:#dc3545; color:#fff; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; font-weight:600; font-size:14px; }
        .box-search-panel .panel-header .close-btn { background:none; border:none; color:#fff; font-size:22px; cursor:pointer; padding:0 4px; line-height:1; }
        .box-search-panel .panel-body { padding:16px; }
        .box-search-panel .panel-body input { width:100%; padding:10px 12px; border:2px solid #e2e8f0; border-radius:8px; font-size:16px; font-family:inherit; }
        .box-search-panel .panel-body input:focus { outline:none; border-color:#dc3545; }
        .box-search-panel .hint { font-size:12px; color:#718096; margin-top:8px; }
        .box-search-panel .result-info { font-size:13px; color:#2d3748; margin-top:8px; font-weight:600; }
        .box-search-panel .btn-clear-box { margin-top:10px; width:100%; padding:8px; background:#f7fafc; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer; font-family:inherit; font-size:13px; color:#718096; }
        .box-search-panel .btn-clear-box:hover { background:#edf2f7; color:#2d3748; }

        /* === Box Image Gallery === */
        .gallery-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.92); z-index:10000; align-items:center; justify-content:center; flex-direction:column; }
        .gallery-overlay.open { display:flex; }
        .gallery-overlay .gallery-close { position:absolute; top:16px; right:20px; background:none; border:none; color:#fff; font-size:40px; cursor:pointer; z-index:10001; }
        .gallery-overlay .gallery-close:hover { color:#fc8181; }
        .gallery-overlay .gallery-img { max-width:90%; max-height:75vh; object-fit:contain; border-radius:8px; user-select:none; }
        .gallery-overlay .gallery-nav { position:absolute; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); border:none; color:#fff; font-size:36px; cursor:pointer; padding:12px 18px; border-radius:8px; }
        .gallery-overlay .gallery-nav:hover { background:rgba(255,255,255,0.3); }
        .gallery-overlay .gallery-prev { left:16px; }
        .gallery-overlay .gallery-next { right:16px; }
        .gallery-overlay .gallery-counter { color:#fff; font-size:16px; margin-top:16px; font-weight:600; }
        .gallery-overlay .gallery-label { color:rgba(255,255,255,0.8); font-size:14px; margin-top:6px; }

        /* === Red Scrollbar === */
        ::-webkit-scrollbar { width:14px; height:14px; }
        ::-webkit-scrollbar-track { background:#f8f8f8; }
        ::-webkit-scrollbar-thumb { background:linear-gradient(180deg,#dc3545,#c82333); border-radius:7px; border:2px solid #f8f8f8; }
        ::-webkit-scrollbar-thumb:hover { background:linear-gradient(180deg,#c82333,#a71d2a); }
        * { scrollbar-width:auto; scrollbar-color:#dc3545 #f8f8f8; }

    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('      ')}}  
                            </span>
                            <div class="float-right">
                                <form method="POST" action="{{ route('update-status-shipping2') }}" style="display: inline-block;">
                                    @csrf
                                    <input type="hidden" name="track_ids" id="trackIdsInput" value="">
                                    <input type="hidden" name="h_customerno" id="h_customerno" value="">
                                    <input type="submit" class="btn btn-sm btn-outline-danger mr-2 disabled" id="updateSelected" value="สินค้าถึงไทยแล้ว">
                                </form>
                                <form method="POST" action="{{ route('update-status-received2') }}" style="display: inline-block;">
                                    @csrf
                                    <input type="hidden" name="track_ids3" id="trackIdsInput3" value="">
                                    <input type="submit" class="btn btn-sm btn-outline-warning mr-2 disabled" id="updateSelected3" value="อัพเดทสถานะ สำเร็จ">
                                </form>
                                <form method="POST" action="{{ route('update-status-pay2') }}" style="display: inline-block;">
                                    @csrf
                                    <input type="hidden" name="track_ids2" id="trackIdsInput2" value="">
                                    <input type="submit" class="btn btn-sm btn-outline-primary mr-2 disabled" id="updateSelected2" value="ชำระเงินแล้ว">
                                </form>
                            </div>
                             <div class="float-right">
                                <a href="{{ route('customershippings.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
                                  {{ __('Create New') }}
                                </a>
                                <!-- $shipping->id -->
                                <button id="invoiceBtn" class="btn btn-danger btn-sm float-right mr-2 disabled" data-placement="left">
                                    {{ __('Invoice') }}
                                </button>
                                 <a href="{{ route('welcome') }}" class="btn btn-default btn-sm mr-2" data-placement="left">
                                     {{ __('Dashboard') }}
                                 </a>

                                 <a href="{{url('customershippingsimport')}}" class="btn btn-warning btn-sm float-right mr-2"  data-placement="left">
                                     {{ __('Import EXCEL') }}
                                 </a>
                                <button id="btn-line-notify" class="btn btn-sm float-right mr-2" style="background: #06C755; color: white; border: none;" data-placement="left" title="แจ้งเตือนลูกค้าผ่าน LINE">
                                    <i class="fa fa-commenting"></i> LINE แจ้งเตือน
                                </button>
                                <button id="btn-send-invoice-chat" class="btn btn-sm float-right mr-2" style="background: #0084FF; color: white; border: none;" data-placement="left" title="ส่งบิลผ่าน SKJ Chat">
                                    <i class="fa fa-paper-plane"></i> ส่งบิลผ่านแชท
                                </button>
                                <button id="btn-remind-payment" class="btn btn-sm float-right mr-2" style="background: #FF9800; color: white; border: none;" data-placement="left" title="เตือนชำระเงินผ่านแชท">
                                    <i class="fa fa-bell"></i> เตือนชำระเงิน
                                </button>
                                <button id="shipping-export" class="btn btn-success btn-sm float-right mr-2 disabled" data-placement="left">
                                    {{ __('Shipping Export EXCEL') }}
                                </button>
                                 <a href="{{url('customershippingsexport2')}}" id="data-export" class="btn btn-primary btn-sm float-right mr-2" data-placement="left">
                                     {{ __('Data Export EXCEL') }}
                                 </a>
{{--                                 <a href="{{url('customershippingsexport2')}}" class="btn btn-primary btn-sm float-right mr-2"  data-placement="left">--}}
{{--                                     {{ __('Data Export EXCEL') }}--}}
{{--                                 </a>--}}
                              </div>
                        </div>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body">
                        <div class="table-responsive">
                            <div id="dateFilters" class="mb-3 col-12 right">
                                <label for="start_date" class="control-label"> วันที่เริ่มต้น (วันที่ปิดตู้)</label>
                                @if ($date = Session::get('startdate'))
                                    <input type="text" id="start_date" class="form-control col-6 col-md-2 col-lg-2" value="{{$date}}" placeholder="วันที่เริ่มต้น" readonly>
                                    @php  session()->forget('startdate'); @endphp
                                @else
                                    <input type="text" id="start_date" class="form-control col-6 col-md-2 col-lg-2" placeholder="วันที่เริ่มต้น" readonly>
                                @endif
                                <label for="end_date" class="control-label"> วันที่สิ้นสุด (วันที่ปิดตู้)</label>
                                <input type="text" id="end_date" class="form-control col-3" placeholder="วว/ดด/ปปปป" readonly>
                            </div>

                            <div class="text-center mt-3 ">
                                <strong class="text-decoration-none text-dark d-block">สรุปยอดรอบจัดส่ง <span id="etd_show"></span> </strong>

                            </div>
                            <div class="row justify-content-center mt-3">
                                <div class="col-2 col-md-2 col-lg-2 text-center d-inline">
                                    <strong class="text-decoration-none text-dark d-block">รวมทั้งหมด</strong>
                                    <span id="total_records">-</span> ชิ้น
                                </div>
                                <div class="col-2 col-md-2 col-lg-2 text-center {{Session::get('hide')?'':'d-none'}}" id="weight_total_section">
                                    <strong class="text-decoration-none text-dark d-block">น้ำหนักรวม</strong>
                                    <span id="weight_total">-</span> kg
                                </div>
                                <div class="col-2 col-md-2 col-lg-2 text-center ">
                                    <strong class="text-decoration-none text-dark d-block">ค่า COD</strong>
                                    <span id="cod_total">-</span> บาท
                                </div>
                            </div>
                            <div class="row justify-content-center  d-none">
                                <div class="col-2 col-md-2 col-lg-2 text-center ">
                                    <strong class="text-decoration-none text-dark d-block">ค่านำเข้า</strong>
                                    <span id="import_cost_total">-</span> บาท
                                </div>

                            </div>

                            <div class="text-center mt-3 {{Session::get('hide')?'':'d-none'}}" id="price_total_section">
                                <strong style="text-decoration: none; color: black; border-bottom: 3px solid red; font-weight: bolder;">ยอดสุทธิ <span id="price_total"></span> บาท</strong>

                            </div>
                            <input type="hidden" id="sessionSearch" />
                            <table class="table table-striped table-hover" id="dt-mant-table-1">
                                <thead class="thead">
                                    <tr>
                                        <th><input type="checkbox" id="checkAll"></th>
                                        <th>No</th>
                                        <th>วันที่</th>
                                        <th>รูปหน้ากล่อง</th>
                                        <th>รหัสลูกค้า</th>
                                        <th>เลขพัสดุ</th>
                                        <th>COD</th>
                                        <th>น้ำหนัก</th>
                                        <th>หน่วยละ</th>
                                        <th>ค่านำเข้า</th>
                                        <th>รูปสินค้า</th>
                                        <th>เลขกล่อง</th>
                                        <th>โกดัง</th>
                                        <th>วันที่ปิดตู้/เที่ยวบิน</th>
                                        <th>ประเภท</th>
                                        <th>สถานะ</th>
                                        <th>การจัดส่ง</th>
                                        <th>สถานะชำระเงิน</th>
                                        <th>หมายเหตุ</th>
                                        <th>Note Admin</th>
                                        <th></th>
                                        <th>Items</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
{{--                {!! $customershippings->links() !!}--}}
            </div>
        </div>
    </div>

    <!-- Floating Box Search Toggle -->
    <button class="box-search-toggle" id="boxSearchToggle" title="ค้นหาเลขกล่อง">
        <i class="fa fa-cube"></i>
    </button>

    <!-- Floating Box Search Panel -->
    <div class="box-search-panel" id="boxSearchPanel">
        <div class="panel-header">
            <span><i class="fa fa-cube"></i> ค้นหาเลขกล่อง</span>
            <button class="close-btn" id="boxSearchClose">&times;</button>
        </div>
        <div class="panel-body">
            <input type="text" id="boxNoSearch" placeholder="พิมพ์เลขกล่อง..." autocomplete="off">
            <div class="hint">ค้นหาเฉพาะเลขกล่องของรอบปิดตู้ที่เลือก</div>
            <div class="result-info" id="boxSearchResult"></div>
            <button class="btn-clear-box" id="boxSearchClear"><i class="fa fa-times"></i> ล้างการค้นหาเลขกล่อง</button>
        </div>
    </div>

    <!-- Box Image Gallery Overlay -->
    <div class="gallery-overlay" id="boxGallery">
        <button class="gallery-close" id="galleryClose">&times;</button>
        <button class="gallery-nav gallery-prev" id="galleryPrev">&#10094;</button>
        <img class="gallery-img" id="galleryImg" src="" alt="">
        <button class="gallery-nav gallery-next" id="galleryNext">&#10095;</button>
        <div class="gallery-counter" id="galleryCounter"></div>
        <div class="gallery-label" id="galleryLabel"></div>
    </div>
@endsection
@section('extra-script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <script>
        $(function () {
            // ฟังก์ชันหาวันจันทร์ในอาทิตย์นั้นๆ
            function getMondayInWeek(dateString) {
                const date = new Date(dateString);
                const day = date.getDay();
                const daysSinceMonday = (day + 6) % 7;
                
                date.setDate(date.getDate() - daysSinceMonday);
                return date.toISOString().split('T')[0];
            }

            // Flatpickr common config: add ship icon on Mondays + footer buttons
            var flatpickrConfig = {
                locale: 'th',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                disableMobile: true,
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    if (dayElem.dateObj.getDay() === 1) {
                        dayElem.classList.add('is-monday');
                    }
                },
                onReady: function(selectedDates, dateStr, fp) {
                    var footer = document.createElement('div');
                    footer.style.cssText = 'display:flex;justify-content:space-between;padding:8px 12px;border-top:1px solid #e2e8f0;';
                    var btnClear = document.createElement('button');
                    btnClear.type = 'button';
                    btnClear.textContent = 'ล้าง';
                    btnClear.style.cssText = 'background:none;border:none;color:#3b82f6;cursor:pointer;font-size:14px;font-family:inherit;padding:4px 8px;';
                    btnClear.addEventListener('click', function() {
                        fp.clear();
                        fp.close();
                        $('#shipping-export').addClass('disabled');
                        setTimeout(function() { dataTable.ajax.reload(null, false); }, 100);
                    });
                    var btnToday = document.createElement('button');
                    btnToday.type = 'button';
                    btnToday.textContent = 'วันนี้';
                    btnToday.style.cssText = 'background:none;border:none;color:#3b82f6;cursor:pointer;font-size:14px;font-family:inherit;padding:4px 8px;';
                    btnToday.addEventListener('click', function() { fp.setDate(new Date(), true); });
                    footer.appendChild(btnClear);
                    footer.appendChild(btnToday);
                    fp.calendarContainer.appendChild(footer);
                }
            };

            // Initialize Flatpickr for start_date (with red styling class)
            var startPicker = flatpickr('#start_date', Object.assign({}, flatpickrConfig, {
                altInputClass: 'form-control col-6 col-md-2 col-lg-2 fp-start-date flatpickr-input',
                onChange: function(selectedDates, dateStr) {
                    if (dateStr) {
                        localStorage.setItem('shippingStartDate', $('#start_date').val());
                        $('#shipping-export').addClass('disabled');
                        setTimeout(function() { dataTable.ajax.reload(null, false); }, 100);
                    }
                }
            }));

            // Initialize Flatpickr for end_date
            var endPicker = flatpickr('#end_date', Object.assign({}, flatpickrConfig, {
                onChange: function(selectedDates, dateStr) {
                    if (dateStr) {
                        $('#shipping-export').addClass('disabled');
                        setTimeout(function() { dataTable.ajax.reload(null, false); }, 100);
                    }
                }
            }));

            // ตั้งค่า default ให้แสดงวันจันทร์ล่าสุดในอาทิตย์นั้นๆ หรือวันที่ที่เลือกไว้เดิม
            function setDefaultMonday() {
                const savedDate = localStorage.getItem('shippingStartDate');
                if (savedDate) {
                    startPicker.setDate(savedDate, false);
                } else {
                    const today = new Date();
                    const mondayInWeek = getMondayInWeek(today.toISOString().split('T')[0]);
                    startPicker.setDate(mondayInWeek, false);
                }
            }
            
            setDefaultMonday();
            
            // เพิ่ม debounce สำหรับ search input
            var searchTimeout;
            $(document).on('input', 'input[type="search"]', function() {
                // ปิดปุ่ม SHIPPING EXPORT EXCEL เมื่อเริ่มพิมพ์
                $('#shipping-export').addClass('disabled');
                
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    dataTable.search($('input[type="search"]').val()).draw();
                }, 1500); // รอ 1.5 วินาทีหลังหยุดพิมพ์
            });
            
            var dataTable = $('#dt-mant-table-1').DataTable({
                "processing": true,
                "serverSide": true,
                "search": false, // ปิดการค้นหาอัตโนมัติของ DataTable
                "language": {
                    "processing": "กำลังโหลด..."
                },
                "ajax": {
                    "url": "{{ route('fetch.customershippings') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function (d){
                        // trim ค่า search เพื่อป้องกัน whitespace และรับค่าครบถ้วน
                        var searchValue = $("input[type='search']").val();
                        d.search = searchValue ? $.trim(searchValue) : '';
                        
                        d.status = $("select.status").val();
                        d.delivery_type_id = $("select.delivery_type_id").val();
                        d.pay_status = $("select.pay_status").val();
                        d.shipping_method = $("select.shipping_method").val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d._token = "{{ csrf_token() }}";
                        d.customerno='';
                        d.box_no = $('#boxNoSearch').val() ? $.trim($('#boxNoSearch').val()) : '';
                        
                        // Log สำหรับ debug (ป้องกัน race condition)
                        // console.log('Request sent - search:', d.search);

                        // จัดการการซ่อน/แสดงคอลัมน์
                        if (d.search && d.search.toLowerCase() === 'แสดง') {
                            d.hide = 'true';
                        } else if (d.search && d.search.toLowerCase() === 'ซ่อน') {
                            d.hide = 'false';
                        }

                        // console.log('start:'+$('#start_date').val()+' end:'+$('#end_date').val())
                        // console.log('search value:', d.search);
                    }
                },
                "initComplete":function(){
                    this.api().columns([14]).every(function () {
                        var column = this;
                        var select = $('<select class="shipping_method"><option value="">ประเภท(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                $('#shipping-export').addClass('disabled');
                                dataTable.ajax.reload(null, false);
                            });
                        select.append('<option value="1">🚢 ทางเรือ</option>')
                        select.append('<option value="2">✈️ ทางเครื่องบิน</option>')
                    });

                    this.api().columns([15]).every(function () {
                        var column = this;
                        var select = $('<select class="status"><option value="">สถานะ(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                // ปิดปุ่ม SHIPPING EXPORT EXCEL เมื่อมีการเปลี่ยน filter
                                $('#shipping-export').addClass('disabled');
                                dataTable.ajax.reload(null, false);

                            });

                            select.append('<option value="2">อยู่ระหว่างขนส่ง</option>')
                        select.append('<option value="3">สินค้าถึงไทยแล้ว</option>')
                        select.append('<option value="4">สำเร็จ</option>')
                        // });
                    });

                    this.api().columns([16]).every(function () {
                        var column = this;
                        var select = $('<select class="delivery_type_id"><option value="">การจัดส่ง(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                // ปิดปุ่ม SHIPPING EXPORT EXCEL เมื่อมีการเปลี่ยน filter
                                $('#shipping-export').addClass('disabled');
                                dataTable.ajax.reload(null, false);

                            });

                        select.append('<option value="1">รับเอง</option>')
                        select.append('<option value="2">ที่อยู่ปัจจุบัน	</option>')
                        select.append('<option value="3">เพิ่มที่อยู่เอง</option>')
                        // });
                    });


                    this.api().columns([17]).every(function () {
                        var column = this;
                        var select = $('<select class="pay_status"><option value="">สถานะชำระเงิน(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                // ปิดปุ่ม SHIPPING EXPORT EXCEL เมื่อมีการเปลี่ยน filter
                                $('#shipping-export').addClass('disabled');
                                dataTable.ajax.reload(null, false);

                            });

                        select.append('<option value="1">ยังไม่ชำระเงิน</option>')
                        select.append('<option value="5">รอโอน</option>')
                        select.append('<option value="2">ชำระเงินแล้ว</option>')
                        // });
                    });

                    // โหลดค่าการค้นหาจาก session และตั้งค่าให้กับฟิลด์การค้นหา
                    console.log('test:{{Session::get('search')??''}}')


                },
                "columnDefs": [
                    { "targets": 0, "data": null,"orderable": false, "render": function (data, type, full, meta) {

                                return `<input type="checkbox" value="${full.id}">`;

                        }
                        },
                    { "targets": 8, "visible": {{Session::get('hide') ? 'true' : 'false'}} }, // หน่วยละ
                    { "targets": 9, "visible": {{Session::get('hide') ? 'true' : 'false'}} }, // ค่านำเข้า
                    { "targets": 12, "visible": {{Session::get('hide') ? 'true' : 'false'}} }, // โกดัง
                    { "targets": 1, "data": null,title:"No","orderable": false, "render": function (data, type, full, meta) {
                            return meta.row + 1;
                        } },
                    { "targets": 2, "data": "ship_date",
                        "render": function (data, type, full, meta) {

                            return `
                            <div>${data}</div>
            <form action="${full.action_del}" method="POST">
                <a class="btn btn-sm btn-success" href="${full.edit_url}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
                @csrf

                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('คุณแน่ใจว่าต้องการจะลบข้อมูลรายการนี้?') }}')" ><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>
            </form>
        `;
                        }}, // คอลัมน์ที่ 1
                    { "targets": 3, "data": "box_image", "render": function (data, type, full, meta) {
                            if (!data || data.trim() === '-') {
                                return '-';
                            } else {
                                return '<img src="' + data + '" class="img-thumbnail box-img" width="50" height="50" data-boxno="' + (full.box_no || '') + '" data-customer="' + (full.customerno || '') + '" onclick="openBoxGallery(this)" alt="" style="cursor: pointer;" onerror="this.onerror=null;this.src=\'/img/error-icon.png\';this.alt=\'-\';">';
                            }
                        } }, // คอลัมน์ที่ 2
                    // เพิ่มคอลัมน์ที่ต้องการให้แสดงในตารางตามลำดับที่เป็นไปตามลิสต์ของคุณ
                    { "targets": 4, "data": "customerno" },
                    { "targets": 5, "data": "track_no" },
                    { "targets": 6, "data": "cod" },
                    { "targets": 7, "data": "weight" },
                    // { "targets": 8, "data": "unit_price" },
                    { "targets": 8, "data": "unit_price", "render": function (data, type, full, meta) {
                            // if (!data || (data > 180.00||data===0||data===0.00) ) {
                            if (full.iswholeprice===1) {
                                return 'ราคาเหมา';
                            } else {
                                return data;
                            }
                        } },
                    { "targets": 9, "data": "import_cost" },
                    { "targets": 10, "data": "product_image", "render": function (data, type, full, meta) {
                            if (!data || data.trim() === '-') {
                                return '-';
                            } else {
                                return '<img src="' + data + '" class="img-thumbnail" width="50" height="50" onclick="showImage(\'' + data + '\')" alt="" style="cursor: pointer;" onerror="this.onerror=null;this.src=\'/img/error-icon.png\';this.alt=\'-\';">';
                            }
                        } },
                    { "targets": 11, "data": "box_no" },
                    { "targets": 12, "data": "warehouse" },
                    { "targets": 13, "data": "etd" },
                    { "targets": 14, "data": "shipping_method_label", "orderable": false, "render": function(data, type, row) {
                            var method = row.shipping_method || 1;
                            if (method == 2) {
                                return '<span style="display:inline-block;padding:4px 10px;border-radius:20px;background:#eff6ff;color:#2563eb;font-size:11px;font-weight:700;white-space:nowrap;">✈️ เครื่องบิน</span>';
                            }
                            return '<span style="display:inline-block;padding:4px 10px;border-radius:20px;background:#f0fdf4;color:#16a34a;font-size:11px;font-weight:700;white-space:nowrap;">🚢 เรือ</span>';
                        }
                    },
                    { "targets": 15, "data": "status","orderable": false, "render": function(data, type, row) {
                            if (!data) return '-';
                            var colors = {
                                'รอดำเนินการ': {bg:'#f1f5f9',color:'#64748b'},
                                'อยู่ระหว่างขนส่ง': {bg:'#fef2f2',color:'#dc2626'},
                                'สินค้าถึงไทยแล้ว': {bg:'#dcfce7',color:'#16a34a'},
                                'สำเร็จ': {bg:'#fdf2f8',color:'#ec4899'}
                            };
                            var c = colors[data] || {bg:'#f1f5f9',color:'#64748b'};
                            return '<span style="display:inline-block;padding:4px 12px;border-radius:20px;background:'+c.bg+';color:'+c.color+';font-size:11px;font-weight:700;white-space:nowrap;">'+data+'</span>';
                        }
                    },
                    { "targets": 16, "data": "delivery_type_name" ,orderable:false},
                    {
                        "targets": 17,
                        "data": "pay_status",
                        "orderable": false,
                        "render": function(data, type, row) {
                            let statusClass = '';
                            console.log(data);
                            switch(data) {
                                case 'ยังไม่ชำระเงิน':
                                    statusClass = 'danger';
                                    break;
                                case 'รอโอน':
                                    statusClass = 'warning';
                                    break;
                                case 'ชำระเงินแล้ว':
                                    statusClass = 'success';
                                    break;
                            }
                            
                            return `<div class="status-container">
                                        <span title="${data}" class="dot bg-${statusClass}"></span>
                                    </div>`;
                        }
                    },
                    { "targets": 18, "data": "note" },
                    { "targets": 19, "data": "note_admin" },
                    {
                        "targets": 20,
                        "data": null,
                        "orderable": false,visible:false,
                        "render": function (data, type, full, meta) {

                            return `
            <form action="${full.action_del}" method="POST">
                <a class="btn btn-sm btn-success" href="${full.edit_url}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
                @csrf

                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('คุณแน่ใจว่าต้องการจะลบข้อมูลรายการนี้?') }}')" ><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>
            </form>
        `;
                        }
                    },

                    { "targets": 21, "data": "itemno" },

                ],
                "lengthMenu": [100, 200, 300, 500, 1000],
                "pageLength": 100,
                "order": [
                    // [13, 'desc'], // เรียงลำดับตาม etd ล่าสุด (desc)
                    // [4, 'asc']    // เรียงลำดับตาม customerno (asc)
                ]
            });

            @if ($search = Session::get('search'))
            $("input[type='search']").val('{{ $search }}');
            $("#sessionSearch").val('{{ $search }}');
            console.log('get search');
            dataTable.search('{{ $search }}').draw();
            @else
            // ตั้งค่า default value "ANW-" ในช่องค้นหา
            var defaultSearch = 'ANW-';
            $("input[type='search']").val(defaultSearch);
            $("#sessionSearch").val(defaultSearch);
            dataTable.search(defaultSearch).draw();
            @endif

            // (date change handled by Flatpickr onChange above)
            // สร้างตัวแปรเพื่อเก็บสถานะการโหลดครั้งแรกและค่าการค้นหาก่อนหน้า
            var initialLoad = true;
            var previousSearchValue = ''; // เก็บค่าการค้นหาก่อนหน้า
            
            // เพิ่มตัวแปรเพื่อป้องกัน race condition
            var pendingRequestId = null; // เก็บ request ID ที่กำลังรอ response (สำรองไว้)


            dataTable.on('xhr.dt', function(e, settings, json, xhr) {
                // ดึงข้อมูลที่ส่งกลับมาจากการเรียกใช้ AJAX
                {{--settings.oPreviousSearch.sSearch !== '{{ $search }}'||--}}
                
                // ตรวจสอบว่า response นี้เป็น response ล่าสุดหรือไม่ (ป้องกัน race condition)
                // โดยเปรียบเทียบ search value จาก response กับ search value ปัจจุบัน
                var currentSearch = $.trim($("input[type='search']").val());
                var responseSearch = settings.oPreviousSearch ? settings.oPreviousSearch.sSearch : '';
                
                // ตรวจสอบว่า search value ตรงกับค่าปัจจุบันหรือไม่
                // ถ้าไม่ตรง และมีค่าค้นหา แสดงว่าเป็น response เก่า ไม่ต้องอัพเดท URL และยอดรวม
                // แต่ถ้าไม่มีค่าค้นหาเลย (ทั้งสองฝั่ง) ให้อัพเดทได้ (กรณีโหลดครั้งแรก)
                var isLatestResponse = (responseSearch === currentSearch) || (!currentSearch && !responseSearch);
                
                if (!isLatestResponse && (currentSearch || responseSearch)) {
                    console.log('Ignoring outdated response - response search:', responseSearch, 'current search:', currentSearch);
                    // ไม่ต้องอัพเดท URL และยอดรวม เพราะเป็น response เก่า
                    return; // ข้ามการอัพเดททั้งหมด
                }
                
                // อัพเดท URL และยอดรวมเฉพาะเมื่อเป็น response ล่าสุด
                console.log('Updating summary and URLs - response search:', responseSearch, 'current search:', currentSearch);
                $('#data-export').attr('href',json.data_export_link);
                $('#shipping-export').attr('href',json.shipping_export_link);
                pendingRequestId = null; // clear pending request
                
                // อัพเดทยอดรวมทุกครั้งที่ AJAX response กลับมา (เฉพาะ response ล่าสุด)
                console.log('Response Data:', json);
                $('#etd_show').text(json.start_date);
                $('#cod_total').text(json.cod_total);
                $('#weight_total').text(json.weight_total);
                $('#total_records').text(json.total_records);
                $('#import_cost_total').text(json.import_cost_total);
                $('#price_total').text(json.price_total);
                
                if (initialLoad || settings.oPreviousSearch.sSearch !== previousSearchValue || $('#start_date').val() !== previousStartDate) {
                    initialLoad = false;
                    previousSearchValue = settings.oPreviousSearch.sSearch; // อัปเดตค่าการค้นหาก่อนหน้า
                    previousStartDate = $('#start_date').val(); // อัปเดตค่า start_date ก่อนหน้า

                    // $('#sessionSearch').val('');
                    initialLoad = false; // ตั้งค่า initialLoad เป็น false หลังจากการโหลดครั้งแรก
                    console.log('initialSearch:', initialLoad);
                }
                
                // เปิดใช้งานปุ่ม Export เมื่อข้อมูลโหลดเสร็จ (ทุกครั้ง)
                updateExportButtonState();
                
                // ไม่ต้องอัพเดท total_records จาก DataTable เพราะใช้ server-side processing
                // ค่าจาก server (json.total_records) ถูกต้องอยู่แล้ว
                // อัพเดท total_records ให้ตรงกับจำนวนแถวที่แสดงจริงในตาราง
                // setTimeout(function() {
                //     var visibleRows = dataTable.rows({page: 'current'}).count();
                //     var totalFilteredRows = dataTable.rows({search: 'applied'}).count();
                //     $('#total_records').text(totalFilteredRows);
                // }, 100);
                
                // คืนค่าการค้นหาหลังจากอัพเดทสถานะ
                var preservedSearch = sessionStorage.getItem('preservedSearch');
                if (preservedSearch) {
                    $("input[type='search']").val(preservedSearch);
                    dataTable.search(preservedSearch).draw();
                    sessionStorage.removeItem('preservedSearch'); // ลบค่าที่เก็บไว้หลังจากใช้แล้ว
                }

            });

            // อัพเดท total_records หลังจาก DataTable ทำการค้นหาเสร็จ
            dataTable.on('search.dt', function() {
                // ไม่ต้องอัพเดท total_records เพราะใช้ server-side processing
                // ค่าจาก server จะถูกอัพเดทใน xhr.dt event
                // var filteredData = dataTable.rows({search: 'applied'}).data();
                // $('#total_records').text(filteredData.length);
                
                // ปิดปุ่ม SHIPPING EXPORT EXCEL เมื่อมีการค้นหาใหม่
                $('#shipping-export').addClass('disabled');
                
                // ตรวจสอบการพิมพ์ "แสดง" หรือ "ซ่อน"
                var searchValue = $("input[type='search']").val().toLowerCase();
                if (searchValue === 'แสดง' || searchValue === 'ซ่อน') {
                    // รีเฟรชหน้าเพื่อให้ session ถูกอัพเดท
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                }
            });

            // อัพเดท total_records เมื่อมีการเปลี่ยนหน้า
            dataTable.on('page.dt', function() {
                // ไม่ต้องอัพเดท total_records เพราะใช้ server-side processing
                // ค่าจาก server จะถูกอัพเดทใน xhr.dt event
                // var filteredData = dataTable.rows({search: 'applied'}).data();
                // $('#total_records').text(filteredData.length);
                
                // ปิดปุ่ม SHIPPING EXPORT EXCEL เมื่อมีการเปลี่ยนหน้า
                $('#shipping-export').addClass('disabled');
            });


            // // เพิ่มฟีเจอร์ Check All
            // $('#checkAllButton').on('click', function() {
            //     $(':checkbox', dataTable.rows().nodes()).prop('checked', true);
            // });
            //
            // $('#uncheckAllButton').on('click', function() {
            //     $(':checkbox', dataTable.rows().nodes()).prop('checked', false);
            // });
            //
            $('#checkAll').on('change', function() {
                $(':checkbox', dataTable.rows().nodes()).prop('checked', $(this).prop('checked'));
            });

            // หากมีการเลือก checkbox ใดๆ, ตรวจสอบว่าควรเปิดหรือปิดปุ่ม Check All
            $('#dt-mant-table-1 tbody').on('change', ':checkbox', function() {
                var allChecked = $(':checkbox', dataTable.rows().nodes()).length === $(':checkbox:checked', dataTable.rows().nodes()).length;
                $('#checkAll').prop('checked', allChecked);
            });


            $('#updateSelected,#updateSelected2,#updateSelected3').on('click', function(e) {

                var selectedRows = $('tbody').find(':checkbox:checked');
                console.log(selectedRows.length);
                if (selectedRows.length > 0) {
                    var selectedIds = [];
                    selectedRows.each(function() {
                        selectedIds.push($(this).val());
                    });
                    $('#trackIdsInput').val(selectedIds.join(','));
                    $('#trackIdsInput2').val(selectedIds.join(','));
                    $('#trackIdsInput3').val(selectedIds.join(','));
                    
                    // เก็บค่าการค้นหาปัจจุบันไว้
                    var currentSearch = $("input[type='search']").val();
                    if (currentSearch) {
                        // เก็บค่าการค้นหาไว้ใน session storage
                        sessionStorage.setItem('preservedSearch', currentSearch);
                    }

                } else {
                    e.preventDefault();
                    alert("กรุณาเลือกรายการที่ต้องการอัพเดท");
                }
            });

            // เพิ่มฟังก์ชันสำหรับตรวจสอบและอัพเดทสถานะปุ่ม Invoice
            function updateInvoiceButtonState() {
                var $invoiceBtn = $('#invoiceBtn');
                var $btn1 = $('#updateSelected');
                var $btn2 = $('#updateSelected2');
                var $btn3 = $('#updateSelected3');
                var selectedRows = $('tbody').find(':checkbox:checked');
                
                if (selectedRows.length > 0) {
                    $invoiceBtn.removeClass('disabled');
                    $btn1.removeClass('disabled');
                    $btn2.removeClass('disabled');
                    $btn3.removeClass('disabled');
                } else {
                    $invoiceBtn.addClass('disabled');
                    $btn1.addClass('disabled');
                    $btn2.addClass('disabled');
                    $btn3.addClass('disabled');
                }
            }

            // เพิ่ม event listeners
            $(document).on('change', ':checkbox', updateInvoiceButtonState);
            $("select.status").on('change', updateInvoiceButtonState);
            $("select.delivery_type_id").on('change', updateInvoiceButtonState);
            $("select.pay_status").on('change', updateInvoiceButtonState);
            
            // เรียกใช้ฟังก์ชันครั้งแรกเมื่อโหลดหน้า
            updateInvoiceButtonState();
            
            // ฟังก์ชันอัพเดทสถานะปุ่ม SHIPPING EXPORT EXCEL
            function updateExportButtonState() {
                var $shippingExportBtn = $('#shipping-export');
                
                // เปิดใช้งานปุ่มเมื่อข้อมูลโหลดเสร็จ
                $shippingExportBtn.removeClass('disabled');
            }

            // เพิ่ม event handler สำหรับปุ่ม Invoice
            $('#invoiceBtn').on('click', function(e) {
                e.preventDefault();
                
                var selectedRows = $('tbody').find(':checkbox:checked');
                
                if (selectedRows.length === 0) {
                    alert('กรุณาเลือกรายการที่ต้องการพิมพ์ Invoice ก่อน');
                    return false;
                }
                
                var selectedIds = [];
                selectedRows.each(function() {
                    selectedIds.push($(this).val());
                });
                
                var startDate = $('#start_date').val();
                var customerno = $("input[type='search']").val();
                
                // สร้าง URL สำหรับ Invoice พร้อมส่งไอดีรายการที่เลือก
                var url = "{{ route('invoice.generate', ['etd' => ':etd', 'customerno' => ':customerno', 'shipping_ids' => ':shipping_ids']) }}";
                url = url.replace(':etd', startDate)
                         .replace(':customerno', customerno)
                         .replace(':shipping_ids', selectedIds.join(','));
                         
                console.log('Invoice URL:', url);
                window.open(url, '_blank');
            });
            
            // เพิ่ม event handler สำหรับปุ่ม SHIPPING EXPORT EXCEL
            $('#shipping-export').on('click', function(e) {
                e.preventDefault();
                
                if ($(this).hasClass('disabled')) {
                    alert('กรุณารอให้ข้อมูลโหลดเสร็จก่อน');
                    return false;
                }
               
                // สร้าง URL ใหม่จากค่าปัจจุบันในช่องค้นหาเสมอ เพื่อความถูกต้อง
                var searchVal = $.trim($("input[type='search']").val()) || '';
                var startDate = $('#start_date').val() || '';
                
                // สร้าง URL ใหม่โดยไม่พึ่งพา href ที่มีอยู่ เพื่อให้แน่ใจว่าใช้ค่าปัจจุบัน
                var baseUrl = "{{ url('customershippingsexport') }}";
                var exportUrl = baseUrl + '/' + startDate + (searchVal ? '?customerno=' + encodeURIComponent(searchVal) : '');
                
                console.log('Export URL:', exportUrl, 'Search Value:', searchVal);
                
                if (exportUrl) {
                    window.open(exportUrl, '_blank');
                    // $('input[type="search"]').val('');
                }
            });

            // LINE Notification
            $('#btn-line-notify').on('click', function() {
                var etdDate = $('#start_date').val();
                if (!etdDate) {
                    alert('กรุณาเลือกวันที่ปิดตู้ก่อน');
                    return;
                }

                // เก็บ customerno จากแถวที่ติ๊กถูก
                var selectedRows = $('tbody').find(':checkbox:checked');
                if (selectedRows.length === 0) {
                    alert('กรุณาเลือกรายการที่ต้องการแจ้งเตือนก่อน (ติ๊กถูกด้านซ้าย)');
                    return;
                }

                // ดึง customerno ที่ไม่ซ้ำจากแถวที่เลือก
                var customerNos = [];
                selectedRows.each(function() {
                    var row = dataTable.row($(this).closest('tr'));
                    var data = row.data();
                    if (data && data.customerno && customerNos.indexOf(data.customerno) === -1) {
                        customerNos.push(data.customerno);
                    }
                });

                // แปลงวันที่เป็น dd/mm/yyyy สำหรับแสดง
                var d = new Date(etdDate);
                var displayDate = ('0'+d.getDate()).slice(-2) + '/' + ('0'+(d.getMonth()+1)).slice(-2) + '/' + d.getFullYear();

                if (!confirm('ต้องการส่ง LINE แจ้งเตือนลูกค้า ' + customerNos.length + ' ราย\n(' + customerNos.join(', ') + ')\nรอบปิดตู้วันที่ ' + displayDate + ' ใช่หรือไม่?')) {
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> กำลังส่ง...');

                $.ajax({
                    url: "{{ route('send.line.notification') }}",
                    type: 'POST',
                    data: {
                        etd: etdDate,
                        customer_nos: customerNos,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html('<i class="fa fa-commenting"></i> LINE แจ้งเตือน');

                        if (response.results && response.results.details) {
                            var msg = response.message + '\n\n--- รายละเอียด ---\n';
                            response.results.details.forEach(function(d) {
                                var icon = d.status === 'success' ? '✅' : (d.status === 'already_sent' ? '⏭️' : '❌');
                                msg += icon + ' ' + d.customerno + ': ' + d.message + '\n';
                            });
                            alert(msg);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fa fa-commenting"></i> LINE แจ้งเตือน');
                        var errMsg = 'เกิดข้อผิดพลาด';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        alert(errMsg);
                    }
                });
            });

            // === ส่งบิลผ่านแชท (SKJ Chat) ===
            $('#btn-send-invoice-chat').on('click', function() {
                var etdDate = $('#start_date').val();
                if (!etdDate) {
                    alert('กรุณาเลือกวันที่ปิดตู้ก่อน');
                    return;
                }

                var selectedRows = $('tbody').find(':checkbox:checked');
                if (selectedRows.length === 0) {
                    alert('กรุณาเลือกรายการที่ต้องการส่งบิลก่อน (ติ๊กถูกด้านซ้าย)');
                    return;
                }

                // ดึง customerno + shipping_ids ที่เลือก
                var customerMap = {}; // { customerno: [id1, id2, ...] }
                selectedRows.each(function() {
                    var row = dataTable.row($(this).closest('tr'));
                    var data = row.data();
                    if (data && data.customerno) {
                        var cn = data.customerno;
                        if (!customerMap[cn]) customerMap[cn] = [];
                        if (data.id) customerMap[cn].push(data.id);
                    }
                });

                var customerNos = Object.keys(customerMap);
                if (customerNos.length === 0) {
                    alert('ไม่พบรหัสลูกค้าจากรายการที่เลือก');
                    return;
                }

                // เก็บ customerMap ไว้ใน modal data
                $('#invoiceChatModal').data('customerMap', customerMap);

                // แปลงวันที่
                var d = new Date(etdDate);
                var displayDate = ('0'+d.getDate()).slice(-2) + '/' + ('0'+(d.getMonth()+1)).slice(-2) + '/' + d.getFullYear();

                // แสดงรายชื่อในรายการ modal พร้อมจำนวนชิ้น (loading state ก่อน)
                var listHtml = '';
                customerNos.forEach(function(cn) {
                    var count = customerMap[cn].length;
                    listHtml += '<label id="chat-row-' + cn.replace(/[^a-zA-Z0-9-]/g, '_') + '" style="display:flex;align-items:center;padding:5px 4px;cursor:pointer;border-bottom:1px solid #f0f0f0;margin:0;">'
                        + '<input type="checkbox" class="chat-invoice-check" value="' + cn + '" checked style="width:18px;height:18px;margin-right:10px;flex-shrink:0;cursor:pointer;">'
                        + '<span style="font-weight:500;">' + cn.toUpperCase() + '</span>'
                        + '&nbsp;<span class="badge badge-info" style="font-size:11px;">' + count + ' ชิ้น</span>'
                        + '&nbsp;<span class="chat-status-badge" data-cn="' + cn + '" style="font-size:10px;"><i class="fa fa-spinner fa-spin"></i></span>'
                        + '</label>';
                });

                $('#invoiceChatEtdDisplay').text(displayDate);
                $('#invoiceChatCustomerList').html(listHtml);
                $('#invoiceChatCustomerCount').text(customerNos.length);

                // แปลง etd เป็น dd/mm/yyyy ให้ตรงกับที่เก็บใน DB
                var etdParts = etdDate.split('-');
                var etdFormatted = etdParts.length === 3 ? etdParts[2] + '/' + etdParts[1] + '/' + etdParts[0] : etdDate;

                // เช็คสถานะเชื่อมต่อแชท + สถานะส่งบิล จาก SKJ Chat API
                function refreshInvoiceStatus() {
                    var allCns = [];
                    $('.chat-invoice-check').each(function() { allCns.push($(this).val()); });
                    if (allCns.length === 0) return;
                    $.ajax({
                        url: 'https://chat.skjjapanshipping.com/api/invoice-check',
                        type: 'POST',
                        contentType: 'application/json',
                        headers: { 'X-API-Key': 'skjchat-invoice-2026' },
                        data: JSON.stringify({ customer_nos: allCns, etd: etdFormatted }),
                        success: function(res) {
                            if (res.success && res.results) {
                                var connectedCount = 0;
                                var notConnectedCount = 0;
                                var invoiceSentCount = 0;
                                allCns.forEach(function(cn) {
                                    var info = res.results[cn];
                                    var badge = $('.chat-status-badge[data-cn="' + cn + '"]');
                                    if (info && info.connected) {
                                        connectedCount++;
                                        var badgeHtml = '<span class="badge" style="background:#28a745;color:#fff;font-size:10px;">✓ เชื่อมต่อแล้ว</span>';
                                        if (info.invoiceSent) {
                                            invoiceSentCount++;
                                            badgeHtml += ' <span class="badge" style="background:#fd7e14;color:#fff;font-size:10px;">📩 ส่งบิลแล้ว</span>';
                                        }
                                        badge.html(badgeHtml);
                                    } else {
                                        notConnectedCount++;
                                        badge.html('<span class="badge" style="background:#dc3545;color:#fff;font-size:10px;">✗ ยังไม่เชื่อมต่อ</span>');
                                        badge.closest('label').find('.chat-invoice-check').prop('checked', false);
                                        badge.closest('label').css('opacity', '0.6');
                                    }
                                });
                                var summaryHtml = '<span style="font-size:12px;color:#666;">'
                                    + '🟢 เชื่อมต่อ: <b>' + connectedCount + '</b> ราย &nbsp;|&nbsp; 🔴 ยังไม่เชื่อมต่อ: <b>' + notConnectedCount + '</b> ราย';
                                if (invoiceSentCount > 0) {
                                    summaryHtml += ' &nbsp;|&nbsp; 📩 ส่งบิลแล้ว: <b>' + invoiceSentCount + '</b> ราย';
                                }
                                summaryHtml += '</span>';
                                $('#invoiceChatConnectionSummary').html(summaryHtml).show();
                            }
                        },
                        error: function() {
                            $('.chat-status-badge').html('<span class="badge badge-secondary" style="font-size:10px;">? ไม่ทราบ</span>');
                        }
                    });
                }
                refreshInvoiceStatus();

                // Auto-generate message template with Thai date
                var thaiDays = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
                var thaiMonths = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                var now = new Date();
                var thaiDate = thaiDays[now.getDay()] + 'ที่ ' + ('0' + now.getDate()).slice(-2) + ' ' + thaiMonths[now.getMonth()];

                var messageTemplate = '✨ขออนุญาตแจ้งยอดนะครับ\n'
                    + '🚢ค่านำเข้า (รอบปิดตู้ ' + displayDate + ')\n'
                    + '📌จำนวน: ' + String.fromCharCode(123,123) + 'จำนวน' + String.fromCharCode(125,125) + ' ชิ้น\n'
                    + 'รวม: ' + String.fromCharCode(123,123) + 'รวม' + String.fromCharCode(125,125) + ' บาท\n'
                    + '\n'
                    + '📍พร้อมให้เข้ารับเอง/เรียกแมส ได้วัน' + thaiDate + ' ตั้งแต่ เวลา 09.30-18.00น.\n'
                    + '\n'
                    + '📍จัดส่งในไทยแจ้งที่อยู่จัดส่งผ่านระบบได้เลยครับ\n'
                    + '\n'
                    + '*‼️ลูกค้าที่ต้องการส่งในไทย รบกวนชำระค่านำเข้าแยกกับค่าส่งในไทยนะครับ🙏\n'
                    + '\n'
                    + '🙏🏻 ขอบคุณครับผม 🙏';
                $('#invoiceChatMessageTemplate').val(messageTemplate);
                $('#invoiceChatQrUrl').val('');
                $('#invoiceChatResult').html('').hide();
                $('#invoiceChatSendBtn').prop('disabled', false).text('📩 ส่งบิล');

                $('#invoiceChatModal').modal('show');
            });

            // ปุ่มส่งใน modal
            $(document).on('click', '#invoiceChatSendBtn', function() {
                var etdDate = $('#start_date').val();
                var messageTemplate = $('#invoiceChatMessageTemplate').val();
                var qrImageUrl = $('#invoiceChatQrUrl').val();

                // เก็บ customerno ที่ติ๊ก
                var selectedCustomers = [];
                $('.chat-invoice-check:checked').each(function() {
                    selectedCustomers.push($(this).val());
                });

                if (selectedCustomers.length === 0) {
                    alert('กรุณาเลือกลูกค้าอย่างน้อย 1 ราย');
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).text('⏳ กำลังส่ง...');
                $('#invoiceChatResult').html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> กำลังส่งบิล ' + selectedCustomers.length + ' ราย กรุณารอ...</div>').show();

                // รวม shipping_ids ของลูกค้าที่เลือก
                var customerMap = $('#invoiceChatModal').data('customerMap') || {};
                var shippingIdsMap = {};
                selectedCustomers.forEach(function(cn) {
                    if (customerMap[cn]) shippingIdsMap[cn] = customerMap[cn];
                });

                $.ajax({
                    url: "{{ route('send.invoice.chat') }}",
                    type: 'POST',
                    data: {
                        etd: etdDate,
                        customer_nos: selectedCustomers,
                        shipping_ids_map: shippingIdsMap,
                        message_template: messageTemplate,
                        qr_image_url: qrImageUrl,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        btn.prop('disabled', false).text('📩 ส่งบิล');
                        var resultHtml = '<div class="alert alert-success mb-2"><b>' + response.message + '</b></div>';

                        if (response.results && response.results.details) {
                            resultHtml += '<div style="max-height:200px;overflow-y:auto;">';
                            response.results.details.forEach(function(d) {
                                var icon = d.status === 'success' ? '✅' : (d.status === 'not_found' ? '⚠️' : '❌');
                                var color = d.status === 'success' ? '#28a745' : (d.status === 'not_found' ? '#ffc107' : '#dc3545');
                                resultHtml += '<div style="padding:4px 0;color:' + color + '">' + icon + ' <b>' + d.customerno.toUpperCase() + '</b>: ' + d.message + '</div>';
                            });
                            resultHtml += '</div>';
                        }
                        $('#invoiceChatResult').html(resultHtml).show();
                        // รีเฟรชสถานะส่งบิลหลังส่งสำเร็จ
                        setTimeout(function() { refreshInvoiceStatus(); }, 1000);
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('📩 ส่งบิล');
                        var errMsg = 'เกิดข้อผิดพลาด';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        $('#invoiceChatResult').html('<div class="alert alert-danger">' + errMsg + '</div>').show();
                    }
                });
            });

            // === เตือนชำระเงินผ่านแชท ===
            $('#btn-remind-payment').on('click', function() {
                var etdDate = $('#start_date').val();
                if (!etdDate) {
                    alert('กรุณาเลือกวันที่ปิดตู้ก่อน');
                    return;
                }

                var selectedRows = $('tbody').find(':checkbox:checked');
                if (selectedRows.length === 0) {
                    alert('กรุณาเลือกรายการที่ต้องการเตือนก่อน (ติ๊กถูกด้านซ้าย)');
                    return;
                }

                // ดึง customerno ที่เลือก (unique)
                var customerSet = {};
                selectedRows.each(function() {
                    var row = dataTable.row($(this).closest('tr'));
                    var data = row.data();
                    if (data && data.customerno) {
                        customerSet[data.customerno] = true;
                    }
                });
                var customerNos = Object.keys(customerSet);

                if (!confirm('ต้องการส่งเตือนชำระเงินให้ ' + customerNos.length + ' ราย?\n\n' + customerNos.map(function(c){ return c.toUpperCase(); }).join(', '))) {
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> กำลังส่ง...');

                $.ajax({
                    url: "{{ route('remind.payment') }}",
                    type: 'POST',
                    data: {
                        customer_nos: customerNos,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html('<i class="fa fa-bell"></i> เตือนชำระเงิน');
                        var msg = response.message + '\n\n';
                        if (response.results) {
                            response.results.forEach(function(d) {
                                var icon = d.status === 'success' ? '✅' : '❌';
                                msg += icon + ' ' + d.customerno.toUpperCase() + ': ' + d.message + '\n';
                            });
                        }
                        alert(msg);
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fa fa-bell"></i> เตือนชำระเงิน');
                        var errMsg = 'เกิดข้อผิดพลาด';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        alert(errMsg);
                    }
                });
            });

            // เลือกทั้งหมด / ยกเลิกทั้งหมด
            $(document).on('click', '#invoiceChatSelectAll', function() {
                var allChecked = $('.chat-invoice-check').length === $('.chat-invoice-check:checked').length;
                $('.chat-invoice-check').prop('checked', !allChecked);
            });

            // === jQuery delegation for box image gallery (backup) ===
            $('#dt-mant-table-1 tbody').on('click', 'img.box-img', function(e) {
                e.stopPropagation();
                if (typeof openBoxGallery === 'function') {
                    openBoxGallery(this);
                }
            });

            // === Box Search Popup Handlers ===
            $('#boxSearchToggle').on('click', function() {
                $('#boxSearchPanel').toggleClass('open');
                if ($('#boxSearchPanel').hasClass('open')) {
                    setTimeout(function(){ $('#boxNoSearch').focus(); }, 100);
                }
            });
            $('#boxSearchClose').on('click', function() {
                $('#boxSearchPanel').removeClass('open');
            });

            // Box search — ค้นผ่าน server (รองรับ pagination)
            var _boxSearchTimer = null;

            $('#boxNoSearch').on('input', function() {
                var val = $(this).val().trim();
                var startDate = $('#start_date').val();
                clearTimeout(_boxSearchTimer);

                // ต้องเลือกรอบปิดตู้ก่อนถึงจะค้นได้
                if (!startDate) {
                    $('#boxSearchResult').html('<span style="color:#e67e22">กรุณาเลือกรอบปิดตู้ก่อน</span>');
                    return;
                }

                if (val.length >= 1) {
                    $('#boxSearchResult').html('<i class="fa fa-spinner fa-spin"></i> กำลังค้นหา...');
                    _boxSearchTimer = setTimeout(function() {
                        dataTable.ajax.reload(function() {
                            var info = dataTable.page.info();
                            if (info.recordsDisplay > 0) {
                                $('#boxSearchResult').html('พบ <b>' + info.recordsDisplay + '</b> รายการ ในรอบปิดตู้ ' + startDate);
                                // ไฮไลท์แถวที่ตรง
                                $('#dt-mant-table-1 tbody tr').each(function() {
                                    var boxCell = $(this).find('td').eq(11);
                                    var text = boxCell.text().trim().toLowerCase();
                                    if (text && text.indexOf(val.toLowerCase()) !== -1) {
                                        $(this).css('background', 'rgba(220,53,69,0.15)');
                                    }
                                });
                            } else {
                                $('#boxSearchResult').html('<span style="color:#dc3545">ไม่พบ "' + val + '" ในรอบปิดตู้ ' + startDate + '</span>');
                            }
                        }, false);
                    }, 400);
                } else {
                    $('#boxSearchResult').html('');
                    dataTable.ajax.reload(null, false);
                }
            });

            $('#boxSearchClear').on('click', function() {
                $('#boxNoSearch').val('');
                $('#dt-mant-table-1 tbody tr').css('background', '');
                $('#boxSearchResult').html('');
                dataTable.ajax.reload(null, false);
            });


        });

    </script>

    <!-- Standalone script: gallery + showImage (isolated from jQuery errors) -->
    <script>
        // === Product Image Simple Viewer (for product_image column) ===
        function showImage(imageUrl) {
            var overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.85);z-index:9999;display:flex;align-items:center;justify-content:center;cursor:pointer;';
            var img = document.createElement('img');
            img.src = imageUrl;
            img.style.cssText = 'max-width:85%;max-height:85%;border-radius:8px;';
            overlay.appendChild(img);
            document.body.appendChild(overlay);
            overlay.onclick = function() { document.body.removeChild(overlay); };
        }
    </script>

    <!-- Box Image Gallery: fully JS-created, appended to body -->
    <script>
        var _gImgs = [], _gLabels = [], _gIdx = 0;
        var _gOverlay = null;

        // Create gallery overlay dynamically (appended to body, no CSS dependency)
        function _gCreate() {
            _gOverlay = document.createElement('div');
            _gOverlay.id = 'jsBoxGallery';
            _gOverlay.style.cssText = 'display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.92);z-index:99999;align-items:center;justify-content:center;flex-direction:column;';

            _gOverlay.innerHTML =
                '<button id="gClose" style="position:absolute;top:16px;right:20px;background:none;border:none;color:#fff;font-size:40px;cursor:pointer;z-index:100000;">&times;</button>' +
                '<button id="gPrev" style="position:absolute;top:50%;left:16px;transform:translateY(-50%);background:rgba(255,255,255,0.15);border:none;color:#fff;font-size:36px;cursor:pointer;padding:12px 18px;border-radius:8px;">&#10094;</button>' +
                '<img id="gImg" src="" style="max-width:90%;max-height:75vh;object-fit:contain;border-radius:8px;user-select:none;" />' +
                '<button id="gNext" style="position:absolute;top:50%;right:16px;transform:translateY(-50%);background:rgba(255,255,255,0.15);border:none;color:#fff;font-size:36px;cursor:pointer;padding:12px 18px;border-radius:8px;">&#10095;</button>' +
                '<div id="gCounter" style="color:#fff;font-size:16px;margin-top:16px;font-weight:600;"></div>' +
                '<div id="gLabel" style="color:rgba(255,255,255,0.8);font-size:14px;margin-top:6px;"></div>';

            document.body.appendChild(_gOverlay);

            var gImg = document.getElementById('gImg');
            var _gZoom = 1;

            document.getElementById('gClose').onclick = _gClose;
            document.getElementById('gPrev').onclick = function() { if (_gIdx > 0) { _gIdx--; _gZoom=1; _gPanX=0; _gPanY=0; gImg.style.transform='scale(1)'; _gUpdate(); } };
            document.getElementById('gNext').onclick = function() { if (_gIdx < _gImgs.length - 1) { _gIdx++; _gZoom=1; _gPanX=0; _gPanY=0; gImg.style.transform='scale(1)'; _gUpdate(); } };
            _gOverlay.onclick = function(e) { if (e.target === _gOverlay) { if (_gZoom > 1) { _gZoom=1; _gPanX=0; _gPanY=0; gImg.style.transform='scale(1)'; gImg.style.cursor='zoom-in'; } else { _gClose(); } } };

            // Zoom + Pan state
            var _gPanX = 0, _gPanY = 0, _isDragging = false, _dragStartX = 0, _dragStartY = 0, _panStartX = 0, _panStartY = 0;
            gImg.style.cursor = 'zoom-in';
            gImg.style.transition = 'transform 0.2s ease';

            function _gApplyTransform() {
                gImg.style.transform = 'scale(' + _gZoom + ') translate(' + (_gPanX/_gZoom) + 'px,' + (_gPanY/_gZoom) + 'px)';
                gImg.style.cursor = _gZoom > 1 ? 'grab' : 'zoom-in';
            }
            function _gResetZoom() { _gZoom = 1; _gPanX = 0; _gPanY = 0; _gApplyTransform(); }

            // Click to zoom in, click again to zoom out
            gImg.addEventListener('click', function(e) {
                e.stopPropagation();
                if (_isDragging) return;
                if (_gZoom > 1) { _gResetZoom(); } else { _gZoom = 2.5; _gPanX = 0; _gPanY = 0; _gApplyTransform(); }
            });

            // Mouse wheel zoom at cursor position
            gImg.addEventListener('wheel', function(e) {
                e.preventDefault(); e.stopPropagation();
                gImg.style.transition = 'none';
                var oldZoom = _gZoom;
                if (e.deltaY < 0) { _gZoom = Math.min(_gZoom + 0.5, 6); }
                else { _gZoom = Math.max(_gZoom - 0.5, 1); }
                if (_gZoom === 1) { _gPanX = 0; _gPanY = 0; }
                _gApplyTransform();
                setTimeout(function() { gImg.style.transition = 'transform 0.2s ease'; }, 50);
            });

            // Mouse drag to pan when zoomed
            gImg.addEventListener('mousedown', function(e) {
                if (_gZoom <= 1) return;
                e.preventDefault();
                _isDragging = false;
                _dragStartX = e.clientX; _dragStartY = e.clientY;
                _panStartX = _gPanX; _panStartY = _gPanY;
                gImg.style.cursor = 'grabbing';
                gImg.style.transition = 'none';

                function onMove(ev) {
                    var dx = ev.clientX - _dragStartX, dy = ev.clientY - _dragStartY;
                    if (Math.abs(dx) > 3 || Math.abs(dy) > 3) _isDragging = true;
                    _gPanX = _panStartX + dx; _gPanY = _panStartY + dy;
                    gImg.style.transform = 'scale(' + _gZoom + ') translate(' + (_gPanX/_gZoom) + 'px,' + (_gPanY/_gZoom) + 'px)';
                }
                function onUp() {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                    gImg.style.cursor = _gZoom > 1 ? 'grab' : 'zoom-in';
                    gImg.style.transition = 'transform 0.2s ease';
                    setTimeout(function() { _isDragging = false; }, 50);
                }
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            });

            // Touch pinch-to-zoom + drag to pan
            var _touchStartDist = 0, _touchStartZoom = 1;
            var _touchStartX = 0, _touchStartY = 0, _touchPanStartX = 0, _touchPanStartY = 0;
            gImg.addEventListener('touchstart', function(e) {
                if (e.touches.length === 2) {
                    e.preventDefault();
                    var dx = e.touches[0].clientX - e.touches[1].clientX;
                    var dy = e.touches[0].clientY - e.touches[1].clientY;
                    _touchStartDist = Math.sqrt(dx*dx + dy*dy);
                    _touchStartZoom = _gZoom;
                } else if (e.touches.length === 1 && _gZoom > 1) {
                    _touchStartX = e.touches[0].clientX; _touchStartY = e.touches[0].clientY;
                    _touchPanStartX = _gPanX; _touchPanStartY = _gPanY;
                    gImg.style.transition = 'none';
                }
            }, {passive:false});
            gImg.addEventListener('touchmove', function(e) {
                if (e.touches.length === 2) {
                    e.preventDefault();
                    var dx = e.touches[0].clientX - e.touches[1].clientX;
                    var dy = e.touches[0].clientY - e.touches[1].clientY;
                    var dist = Math.sqrt(dx*dx + dy*dy);
                    _gZoom = Math.min(Math.max(_touchStartZoom * (dist / _touchStartDist), 1), 6);
                    if (_gZoom === 1) { _gPanX = 0; _gPanY = 0; }
                    _gApplyTransform();
                } else if (e.touches.length === 1 && _gZoom > 1) {
                    e.preventDefault();
                    _gPanX = _touchPanStartX + (e.touches[0].clientX - _touchStartX);
                    _gPanY = _touchPanStartY + (e.touches[0].clientY - _touchStartY);
                    gImg.style.transform = 'scale(' + _gZoom + ') translate(' + (_gPanX/_gZoom) + 'px,' + (_gPanY/_gZoom) + 'px)';
                }
            }, {passive:false});
            gImg.addEventListener('touchend', function(e) {
                gImg.style.transition = 'transform 0.2s ease';
            });

            document.addEventListener('keydown', function(e) {
                if (!_gOverlay || _gOverlay.style.display === 'none') return;
                if (e.key === 'ArrowLeft' && _gIdx > 0) { _gIdx--; _gUpdate(); }
                if (e.key === 'ArrowRight' && _gIdx < _gImgs.length - 1) { _gIdx++; _gUpdate(); }
                if (e.key === 'Escape') _gClose();
            });

            // Touch swipe
            var startX = 0;
            _gOverlay.addEventListener('touchstart', function(e) { startX = e.touches[0].clientX; }, {passive:true});
            _gOverlay.addEventListener('touchend', function(e) {
                var diff = e.changedTouches[0].clientX - startX;
                if (Math.abs(diff) > 50) {
                    if (diff < 0 && _gIdx < _gImgs.length - 1) { _gIdx++; _gUpdate(); }
                    if (diff > 0 && _gIdx > 0) { _gIdx--; _gUpdate(); }
                }
            }, {passive:true});

            console.log('Gallery created and appended to body');
        }

        function openBoxGallery(imgEl) {
            if (!_gOverlay) _gCreate();
            _gImgs = []; _gLabels = []; _gIdx = 0;
            var rows = document.querySelectorAll('#dt-mant-table-1 tbody tr');
            for (var i = 0; i < rows.length; i++) {
                var imgs = rows[i].querySelectorAll('img.box-img');
                for (var j = 0; j < imgs.length; j++) {
                    var src = imgs[j].getAttribute('src');
                    if (src && src.indexOf('error-icon') === -1) {
                        _gImgs.push(src);
                        _gLabels.push('เลขกล่อง: ' + (imgs[j].getAttribute('data-boxno') || '-') + '  |  ลูกค้า: ' + (imgs[j].getAttribute('data-customer') || '-'));
                        if (imgs[j] === imgEl) _gIdx = _gImgs.length - 1;
                    }
                }
            }
            if (!_gImgs.length) return;
            _gUpdate();
            _gOverlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function _gUpdate() {
            var img = document.getElementById('gImg');
            var counter = document.getElementById('gCounter');
            var label = document.getElementById('gLabel');
            var prev = document.getElementById('gPrev');
            var next = document.getElementById('gNext');
            if (img) img.src = _gImgs[_gIdx];
            if (counter) counter.textContent = (_gIdx + 1) + ' / ' + _gImgs.length;
            if (label) label.textContent = _gLabels[_gIdx] || '';
            if (prev) prev.style.display = _gIdx > 0 ? 'block' : 'none';
            if (next) next.style.display = _gIdx < _gImgs.length - 1 ? 'block' : 'none';
        }

        function _gClose() {
            if (_gOverlay) _gOverlay.style.display = 'none';
            document.body.style.overflow = '';
        }

    </script>

<!-- Invoice Chat Modal -->
<div class="modal fade" id="invoiceChatModal" tabindex="-1" role="dialog" aria-labelledby="invoiceChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-height: 90vh;">
        <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header py-2" style="background: #0084FF; color: white; flex-shrink: 0;">
                <h5 class="modal-title" id="invoiceChatModalLabel">📩 ส่งบิลผ่าน SKJ Chat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="overflow-y: auto; flex: 1 1 auto;">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-2"><b>รอบปิดตู้:</b> <span id="invoiceChatEtdDisplay" class="text-primary"></span></h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><b>ลูกค้า (<span id="invoiceChatCustomerCount">0</span> ราย)</b></span>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="invoiceChatSelectAll">เลือก/ยกเลิกทั้งหมด</button>
                        </div>
                        <div id="invoiceChatCustomerList" style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 8px; resize: vertical;">
                        </div>
                        <div id="invoiceChatConnectionSummary" class="mt-1" style="display:none;"></div>
                        <small class="text-muted mt-1 d-block">⚠️ ลูกค้าที่ยังไม่เชื่อมต่อ จะถูกยกเลิกการเลือกอัตโนมัติ</small>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <label class="mb-1"><b>ข้อความแจ้งลูกค้า</b></label>
                            <small class="text-muted d-block mb-1"><code>@{{จำนวน}}</code> และ <code>@{{รวม}}</code> จะถูกแทนที่อัตโนมัติตามข้อมูลแต่ละลูกค้า</small>
                            <textarea class="form-control form-control-sm" id="invoiceChatMessageTemplate" rows="14" style="font-size: 13px; line-height: 1.6; resize: vertical; min-height: 280px;"></textarea>
                        </div>
                        <div class="form-group mb-2">
                            <label class="mb-1"><b>URL รูป QR รับเงิน</b> <small class="text-muted">(ไม่บังคับ)</small></label>
                            <input type="text" class="form-control form-control-sm" id="invoiceChatQrUrl" placeholder="https://chat.skjjapanshipping.com/uploads/qr-payment.jpg">
                        </div>
                        <div class="text-muted" style="font-size: 11px; line-height: 1.6;">
                            <b>ระบบจะส่ง:</b> 1) ข้อความด้านบน 2) PDF ใบแจ้งหนี้ 3) รูป QR (ถ้ามี)
                        </div>
                    </div>
                </div>
                <div id="invoiceChatResult" style="display: none;" class="mt-2"></div>
            </div>
            <div class="modal-footer py-2" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" id="invoiceChatSendBtn">📩 ส่งบิล</button>
            </div>
        </div>
    </div>
</div>

@endsection
