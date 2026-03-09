<div class="shipping-form-wrapper">

    <div id="cur_addr">
        <input type="hidden" id="fullname" value="{{$authUser->name}}" />
        <input type="hidden" id="address" value="{{$authUser->addr}}"/>
        <input type="hidden" id="addr_province" value="{{$authUser->province}}"/>
        <input type="hidden" id="addr_district" value="{{$authUser->distrinct}}"/>
        <input type="hidden" id="addr_subdistrict" value="{{$authUser->subdistrinct}}"/>
        <input type="hidden" id="addr_postcode" value="{{$authUser->postcode}}"/>
        <input type="hidden" id="addr_mobile" value="{{$authUser->mobile}}"/>
    </div>

    <!-- Section: ข้อมูลพัสดุ -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fa fa-cube"></i> ข้อมูลพัสดุ
        </div>
        <div class="form-row-grid">
            <div class="form-group">
                {{ Form::label('track_no','เลขพัสดุ') }}
                {{ Form::text('track_no', $customershipping->track_no, ['class' => 'form-control' . ($errors->has('track_no') ? ' is-invalid' : ''), 'placeholder' => '', 'readonly' => 'true']) }}
                {!! $errors->first('track_no', '<div class="invalid-feedback">:message</div>') !!}
            </div>
            <div class="form-group">
                {{ Form::label('delivery_type_id','การจัดส่ง') }}
                @php
                    $deliveryTypes = \App\Models\DeliveryType::orderBy('sortno')->pluck('name','id')->toArray();
                    $options = ['' => 'เลือกวิธีการจัดส่ง'] + $deliveryTypes;
                @endphp
                <select name="delivery_type_id" class="form-control{{ $errors->has('delivery_type_id') ? ' is-invalid' : '' }}" required>
                    @foreach($options as $value => $label)
                        <option value="{{ $value }}" 
                                {{ $value === '' ? 'disabled' : '' }}
                                {{ (string)$value === (string)($customershipping->delivery_type_id ?? 1) ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                {!! $errors->first('delivery_type_id', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('note','หมายเหตุ') }}
            {{ Form::text('note', $customershipping->note, ['class' => 'form-control' . ($errors->has('note') ? ' is-invalid' : ''), 'placeholder' => 'หมายเหตุเพิ่มเติม (ถ้ามี)']) }}
            {!! $errors->first('note', '<div class="invalid-feedback">:message</div>') !!}
        </div>
    </div>

    <!-- Section: ข้อมูลผู้รับ -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fa fa-user"></i> ข้อมูลผู้รับ
        </div>
        <div class="form-row-grid">
            <div class="form-group position-relative">
                {{ Form::label('delivery_fullname', 'ชื่อ-นามสกุล') }}
                {{ Form::text('delivery_fullname', $customershipping->delivery_fullname, ['class' => 'form-control' . ($errors->has('delivery_fullname') ? ' is-invalid' : ''), 'placeholder' => 'ชื่อ-นามสกุล']) }}
                <small class="text-danger search-hint d-none">*ค้นหาด้วยชื่อ (ประวัติการส่งที่ผ่านมา)*</small>
                <div id="delivery_fullname-results" class="search-results"></div>
                {!! $errors->first('delivery_fullname', '<div class="invalid-feedback">:message</div>') !!}
            </div>
            <div class="form-group position-relative">
                {{ Form::label('delivery_mobile', 'เบอร์โทร') }}
                {{ Form::text('delivery_mobile', $customershipping->delivery_mobile, ['class' => 'form-control' . ($errors->has('delivery_mobile') ? ' is-invalid' : ''), 'placeholder' => 'เบอร์โทร']) }}
                <small class="text-danger search-hint d-none">*ค้นหาด้วยเบอร์โทร (ประวัติการส่งที่ผ่านมา)*</small>
                <div id="delivery_mobile-results" class="search-results"></div>
                {!! $errors->first('delivery_mobile', '<div class="invalid-feedback">:message</div>') !!}
            </div>
        </div>
    </div>

    <!-- Section: ที่อยู่จัดส่ง -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fa fa-map-marker"></i> ที่อยู่จัดส่ง
        </div>
        <div class="form-group">
            {{ Form::label('delivery_address','ที่อยู่') }}
            {{ Form::text('delivery_address', $customershipping->delivery_address, ['class' => 'form-control' . ($errors->has('delivery_address') ? ' is-invalid' : ''), 'placeholder' => 'บ้านเลขที่ ซอย ถนน']) }}
            {!! $errors->first('delivery_address', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-row-grid">
            <div class="form-group position-relative">
                <label for="delivery_subdistrict" class="form-label">แขวง/ตำบล</label>
                <input type="text" 
                       class="form-control @error('delivery_subdistrict') is-invalid @enderror" 
                       id="delivery_subdistrict" 
                       name="delivery_subdistrict"
                       value="{{ $customershipping->delivery_subdistrict ?? '' }}"
                       placeholder="พิมพ์เพื่อค้นหาตำบล"
                       required>
                <div id="delivery_subdistrict-results" class="search-results"></div>
                @error('delivery_subdistrict')
                    <label class="error invalid-feedback">กรุณากรอก แขวง/ตำบล</label>
                @enderror
            </div>
            <div class="form-group position-relative">
                <label for="delivery_district" class="form-label">เขต/อำเภอ</label>
                <input type="text" 
                       class="form-control @error('delivery_district') is-invalid @enderror" 
                       id="delivery_district" 
                       name="delivery_district"
                       value="{{ $customershipping->delivery_district ?? '' }}"
                       placeholder="พิมพ์เพื่อค้นหาอำเภอ"
                       required>
                <div id="delivery_district-results" class="search-results"></div>
                @error('delivery_district')
                    <label class="error invalid-feedback">กรุณากรอก เขต/อำเภอ</label>
                @enderror
            </div>
        </div>
        <div class="form-row-grid">
            <div class="form-group position-relative">
                <label for="delivery_province" class="form-label">จังหวัด</label>
                <input type="text" 
                       class="form-control @error('delivery_province') is-invalid @enderror" 
                       id="delivery_province" 
                       name="delivery_province"
                       value="{{ $customershipping->delivery_province ?? '' }}"
                       placeholder="พิมพ์เพื่อค้นหาจังหวัด"
                       required>
                <div id="delivery_province-results" class="search-results"></div>
                @error('delivery_province')
                    <label class="error invalid-feedback">กรุณากรอก จังหวัด</label>
                @enderror
            </div>
            <div class="form-group position-relative">
                <label for="delivery_postcode" class="form-label">รหัสไปรษณีย์</label>
                <input type="text" 
                       class="form-control @error('delivery_postcode') is-invalid @enderror" 
                       id="delivery_postcode" 
                       name="delivery_postcode"
                       value="{{ $customershipping->delivery_postcode ?? '' }}"
                       placeholder="รหัสไปรษณีย์"
                       required>
                <div id="delivery_postcode-results" class="search-results"></div>
                @error('delivery_postcode')
                    <label class="error invalid-feedback">กรุณากรอก รหัสไปรษณีย์</label>
                @enderror
            </div>
        </div>
    </div>

    <!-- Section: รูปภาพ -->
    <div class="form-section">
        <div class="form-section-title">
            <i class="fa fa-image"></i> รูปภาพ
        </div>
        <div class="image-grid">
            <div class="image-preview-card">
                <label>รูปหน้ากล่อง</label>
                <div class="image-preview-box">
                    @if(isset($customershipping) && $customershipping->box_image)
                        <img id="box_image_preview" src="{{ asset($customershipping->box_image) }}" alt="รูปหน้ากล่อง">
                    @else
                        <div class="image-placeholder" id="box_image_preview">
                            <i class="fa fa-image"></i>
                            <span>ไม่มีรูปภาพ</span>
                        </div>
                    @endif
                </div>
                {!! $errors->first('box_image', '<div class="invalid-feedback d-block">:message</div>') !!}
            </div>
            <div class="image-preview-card">
                <label>รูปสินค้า</label>
                <div class="image-preview-box">
                    @if(isset($customershipping) && $customershipping->product_image)
                        <img id="product_image_preview" src="{{ asset($customershipping->product_image) }}" alt="รูปสินค้า">
                    @else
                        <div class="image-placeholder" id="product_image_preview">
                            <i class="fa fa-image"></i>
                            <span>ไม่มีรูปภาพ</span>
                        </div>
                    @endif
                </div>
                {!! $errors->first('product_image', '<div class="invalid-feedback d-block">:message</div>') !!}
            </div>
        </div>
    </div>

    <!-- Buttons -->
    <div class="form-actions">
        <button type="submit" class="btn-shipping-submit">
            <i class="fa fa-check"></i> บันทึกข้อมูล
        </button>
        <button type="button" class="btn-shipping-cancel" onclick="window.history.back();">
            <i class="fa fa-arrow-left"></i> ย้อนกลับ
        </button>
    </div>

</div>
@section('extra-script')
<script src="{{ asset('js/thai-address-search.js') }}"></script>
<script>
    //EVENTS
    // document.querySelector('#delivery_province').addEventListener('change', (event) => {
    //     showAmphoes("#delivery_province","#delivery_district");
    // });
    // document.querySelector('#delivery_district').addEventListener('change', (event) => {
    //     showTambons("#delivery_province","#delivery_district","#delivery_subdistrict");
    // });
    // document.querySelector('#delivery_subdistrict').addEventListener('change', (event) => {
    //     showZipcode("#delivery_province","#delivery_district","#delivery_subdistrict","#delivery_postcode");
    // });

    $(function () {
        //รับเอง hide all address
        //ที่อยูู่ปัจจุบัน
        //เพิ่ม default


        deliveryOnLoad($('select[name="delivery_type_id"]').val());
        $('select[name="delivery_type_id"]').on('change',function (e) {

            $('input[name="delivery_fullname"]').val('');
            $('input[name="delivery_address"]').val('');
            $('input[name="delivery_province"]').val('');
            $('input[name="delivery_district"]').val('');
            $('input[name="delivery_subdistrict"]').val('');
            $('input[name="delivery_mobile"]').val('');
            $('input[name="delivery_postcode"]').val('');
            deliveryLoadChange($(this).val());
        });

    });

    function deliveryOnLoad(_delivery_type_id) {
        if(_delivery_type_id==1){
            $('[name^="delivery"]').not('[name="delivery_type_id"]').addClass('d-none');
            $('[name^="delivery"]').not('[name="delivery_type_id"]').removeAttr('required');
            $('[for^="delivery"]').not('[for="delivery_type_id"]').addClass('d-none');
            $('.search-hint').addClass('d-none');
            // Show pickup person name
            $('input[name="delivery_fullname"]').removeClass('d-none');
            $('label[for="delivery_fullname"]').removeClass('d-none');
        } else if(_delivery_type_id==2) {
            // ล็อคฟิลด์ทั้งหมดเมื่อโหลดหน้าและเป็นที่อยู่ปัจจุบัน
            $('input[name="delivery_fullname"]').attr('readonly', true);
            $('input[name="delivery_mobile"]').attr('readonly', true);
            $('input[name="delivery_address"]').attr('readonly', true);
            $('input[name="delivery_province"]').attr('readonly', true);
            $('input[name="delivery_district"]').attr('readonly', true);
            $('input[name="delivery_subdistrict"]').attr('readonly', true);
            $('input[name="delivery_postcode"]').attr('readonly', true);
        } else {
            $('.search-hint').removeClass('d-none');
            // ปลดล็อคฟิลด์ทั้งหมดสำหรับตัวเลือกอื่นๆ
            $('input[name^="delivery"]').not('[name="delivery_type_id"]').removeAttr('readonly');
        }
    }
    function deliveryLoadChange(_delivery_type_id) {
        if(_delivery_type_id>1){
            $('[name^="delivery"]').not('[name="delivery_type_id"]').removeClass('d-none');
            $('[name^="delivery"]').not('[name="delivery_type_id"]').attr('required',true);
            $('[for^="delivery"]').not('[for="delivery_type_id"]').removeClass('d-none');
            $('.search-hint').removeClass('d-none');
            // ปลดล็อคฟิลด์ทั้งหมด
            $('input[name^="delivery"]').not('[name="delivery_type_id"]').removeAttr('readonly');
        }
        switch (_delivery_type_id)
        {
            case '1':
                $('[name^="delivery"]').not('[name="delivery_type_id"]').addClass('d-none');
                $('[name^="delivery"]').not('[name="delivery_type_id"]').removeAttr('required');
                $('[for^="delivery"]').not('[for="delivery_type_id"]').addClass('d-none');
                $('.search-hint').addClass('d-none');
                // Show pickup person name
                $('input[name="delivery_fullname"]').removeClass('d-none').removeAttr('readonly');
                $('label[for="delivery_fullname"]').removeClass('d-none');
                // Clear other fields
                $('input[name="delivery_mobile"]').val('');
                $('input[name="delivery_address"]').val('');
                $('input[name="delivery_province"]').val('');
                $('input[name="delivery_district"]').val('');
                $('input[name="delivery_subdistrict"]').val('');
                $('input[name="delivery_postcode"]').val('');
                break;
            case '2':
                $('input[name="delivery_fullname"]').val($('#cur_addr>input#fullname').val());
                $('input[name="delivery_address"]').val($('#cur_addr>input#address').val());
                $('input[name="delivery_province"]').val($('#cur_addr>input#addr_province').val());
                $('input[name="delivery_district"]').val($('#cur_addr>input#addr_district').val());
                $('input[name="delivery_subdistrict"]').val($('#cur_addr>input#addr_subdistrict').val());
                $('input[name="delivery_mobile"]').val($('#cur_addr>input#addr_mobile').val());
                $('input[name="delivery_postcode"]').val($('#cur_addr>input#addr_postcode').val());
                // ล็อคฟิลด์ทั้งหมดเมื่อเลือกที่อยู่ปัจจุบัน
                $('input[name="delivery_fullname"]').attr('readonly', true);
                $('input[name="delivery_mobile"]').attr('readonly', true);
                $('input[name="delivery_address"]').attr('readonly', true);
                $('input[name="delivery_province"]').attr('readonly', true);
                $('input[name="delivery_district"]').attr('readonly', true);
                $('input[name="delivery_subdistrict"]').attr('readonly', true);
                $('input[name="delivery_postcode"]').attr('readonly', true);

                $('.search-hint').addClass('d-none');
                break;
        }
    }

    // เพิ่มส่วนของ Thai Address Search
    $(document).ready(function() {
        // เริ่มต้นใช้งาน Thai Address Search
        initThaiAddressSearch({
            formId: '#shipping-form',
            provinceField: '#delivery_province',
            amphoeField: '#delivery_district',
            tambonField: '#delivery_subdistrict',
            zipcodeField: '#delivery_postcode',
            onAddressSelect: function(address) {
                console.log('เลือกที่อยู่:', address);
                // เก็บค่าที่อยู่เดิมไว้
                var currentAddress = $('#delivery_address').val();
                
                // อัพเดทค่าในช่องที่อยู่อื่นๆ ตามการเลือก delivery type
                // if($('select[name="delivery_type_id"]').val() == '2') {
                //     $('#cur_addr>input#addr_province').val(address.province);
                //     $('#cur_addr>input#addr_district').val(address.amphoe);
                //     $('#cur_addr>input#addr_subdistrict').val(address.tambon);
                //     $('#cur_addr>input#addr_postcode').val(address.zipcode);
                // }
                // คืนค่าที่อยู่เดิมหลังจาก update ข้อมูลอื่น
                if(currentAddress) {
                    setTimeout(function() {
                        // console.log('test123');
                        $('#delivery_address').val(currentAddress);
                    }, 100);
                }
            }
        });
      

         // ระบบค้นหาลูกค้า
    initCustomerSearch({
        fullnameField: '#delivery_fullname',
        mobileField: '#delivery_mobile',
        apiPrefix: '/skjtrack',
        onSelect: function(customer) {
            // อัพเดทข้อมูลลูกค้าที่เลือก
            $('#delivery_address').val(customer.address);
            $('#delivery_province').val(customer.province);
            $('#delivery_district').val(customer.amphoe);
            $('#delivery_subdistrict').val(customer.tambon);
            $('#delivery_postcode').val(customer.zipcode);
           
        }
    });
    });
</script>
@endsection

@section('extra-css')
<style>
    .position-relative { position: relative; }

    /* ==========================================
       SHIPPING EDIT PAGE - MODERN STYLES
       ========================================== */

    /* Card */
    .shipping-edit-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        border: 1px solid #f1f5f9;
        overflow: hidden;
        margin-top: 20px;
        margin-bottom: 30px;
    }

    /* Header */
    .shipping-edit-header {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 28px 32px;
        background: linear-gradient(135deg, #1D8AC9 0%, #0f4c75 100%);
        color: white;
    }

    .shipping-edit-header-icon {
        width: 52px;
        height: 52px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .shipping-edit-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0 0 4px;
    }

    .shipping-edit-header p {
        font-size: 0.88rem;
        margin: 0;
        opacity: 0.8;
    }

    .shipping-edit-header p strong {
        opacity: 1;
        font-weight: 600;
    }

    /* Body */
    .shipping-edit-body {
        padding: 32px;
    }

    /* Form Wrapper */
    .shipping-form-wrapper {}

    /* Form Sections */
    .form-section {
        margin-bottom: 28px;
        padding-bottom: 24px;
        border-bottom: 1px solid #f1f5f9;
    }

    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .form-section-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #1D8AC9;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e8f4fd;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section-title i {
        font-size: 1rem;
        width: 20px;
        text-align: center;
    }

    /* 2-Column Grid */
    .form-row-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 20px;
    }

    @media (max-width: 768px) {
        .form-row-grid {
            grid-template-columns: 1fr;
        }
        .shipping-edit-body {
            padding: 24px 20px;
        }
        .shipping-edit-header {
            padding: 22px 20px;
        }
    }

    /* Form Group */
    .shipping-form-wrapper .form-group {
        margin-bottom: 18px;
    }

    .shipping-form-wrapper .form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .shipping-form-wrapper .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.92rem;
        color: #1e293b;
        background: #ffffff;
        transition: all 0.2s ease;
        outline: none;
    }

    .shipping-form-wrapper .form-control:focus {
        border-color: #1D8AC9;
        box-shadow: 0 0 0 3px rgba(29, 138, 201, 0.1);
    }

    .shipping-form-wrapper .form-control[readonly] {
        background: #f8fafc;
        color: #64748b;
        cursor: not-allowed;
    }

    .shipping-form-wrapper .form-control::placeholder {
        color: #94a3b8;
    }

    .main-panel .shipping-form-wrapper select.form-control,
    .shipping-form-wrapper select.form-control {
        -webkit-appearance: auto !important;
        -moz-appearance: auto !important;
        appearance: auto !important;
        background-color: #ffffff !important;
        background-image: none !important;
        padding: 12px 16px !important;
        height: auto !important;
        line-height: 1.5 !important;
        cursor: pointer !important;
    }

    /* Image Grid */
    .image-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 576px) {
        .image-grid {
            grid-template-columns: 1fr;
        }
    }

    .image-preview-card label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .image-preview-box {
        border: 2px dashed #d1d9e6;
        border-radius: 14px;
        overflow: hidden;
        background: #f8fafc;
        height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color 0.2s;
    }

    .image-preview-box:hover {
        border-color: #94a3b8;
    }

    .image-preview-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        border-radius: 10px;
        padding: 8px;
    }

    .image-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #94a3b8;
        width: 100%;
        height: 100%;
    }

    .image-placeholder i {
        font-size: 2rem;
        opacity: 0.5;
    }

    .image-placeholder span {
        font-size: 0.82rem;
    }

    /* Buttons */
    .form-actions {
        display: flex;
        gap: 12px;
        padding-top: 24px;
        margin-top: 8px;
    }

    .btn-shipping-submit {
        padding: 14px 32px;
        background: linear-gradient(135deg, #1D8AC9, #0ea5e9);
        color: white;
        border: none;
        border-radius: 14px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(29, 138, 201, 0.3);
    }

    .btn-shipping-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(29, 138, 201, 0.4);
    }

    .btn-shipping-cancel {
        padding: 14px 28px;
        background: #f1f5f9;
        color: #64748b;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-shipping-cancel:hover {
        background: #e2e8f0;
        color: #475569;
    }

    /* Search Hint */
    .search-hint {
        font-size: 0.78rem;
        margin-top: 4px;
    }
</style>
@endsection
