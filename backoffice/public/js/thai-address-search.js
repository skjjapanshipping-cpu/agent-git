/**
 * Thai Address Search    outside of skjtrack
 * ระบบค้นหาที่อยู่ของไทย
 */
function initThaiAddressSearch(options = {}) {
    const config = {
        formId: options.formId || '#address-form',
        provinceField: options.provinceField || '#province',
        amphoeField: options.amphoeField || '#amphoe', 
        tambonField: options.tambonField || '#tambon',
        zipcodeField: options.zipcodeField || '#zipcode',
        onAddressSelect: options.onAddressSelect || function() {}
    };

    let typingTimer;
    const doneTypingInterval = 300;

    /**
     * ค้นหาที่อยู่
     * @param {string} searchText - ข้อความที่ใช้ค้นหา
     * @param {string} type - ประเภทการค้นหา (province, amphoe, tambon, zipcode)
     */
    function searchAddress(searchText, type) {
        // const prefix ="/skjtrack";
        const prefix ="";

        $.get(prefix + '/api/address/search', { term: searchText, type: type }, function(data) {
            const resultsDiv = $(`#${type}-results`);
            if(data.length > 0) {
                let html = '';
                data.forEach(item => {
                    const [vTumbon, vAmphoe, vProvince, vZipcode] = item.id.split('|');
                    html += `<div class="search-item" 
                        data-value="${item.id}"
                        data-province="${vProvince || ''}"
                        data-amphoe="${vAmphoe || ''}"
                        data-tambon="${vTumbon || ''}"
                        data-zipcode="${vZipcode || ''}"
                    >${item.text}</div>`;
                });
                resultsDiv.html(html).show();
            } else {
                resultsDiv.html('<div class="search-item">ไม่พบข้อมูล</div>').show();
            }
        });
    }

    // ตั้งค่า Event Listeners สำหรับช่องค้นหา
    [
        config.provinceField.replace('#', ''), 
        config.amphoeField.replace('#', ''), 
        config.tambonField.replace('#', ''), 
        config.zipcodeField.replace('#', '')
    ].forEach(field => {
        $(`#${field}`).on('keyup', function() {
            clearTimeout(typingTimer);
            const $input = $(this);
            
            if($input.val().length >= 2) {
                typingTimer = setTimeout(() => {
                    searchAddress($input.val(), field);
                }, doneTypingInterval);
            } else {
                $(`#${field}-results`).hide();
            }
        });
    });

    // จัดการการคลิกเลือกผลการค้นหา
    $(document).on('click', '.search-item', function() {
        const $this = $(this);
        const parent = $this.parent();
        const inputId = parent.attr('id').replace('-results', '');
        
        $(`#${inputId}`).val($this.text());
        parent.hide();

        // อัพเดทค่าในช่องอื่นๆ
        if($this.data('province')) $(config.provinceField).val($this.data('province'));
        if($this.data('amphoe')) $(config.amphoeField).val($this.data('amphoe'));
        if($this.data('tambon')) $(config.tambonField).val($this.data('tambon'));
        if($this.data('zipcode')) $(config.zipcodeField).val($this.data('zipcode'));

        // เรียก callback function
        config.onAddressSelect({
            province: $this.data('province'),
            amphoe: $this.data('amphoe'),
            tambon: $this.data('tambon'),
            zipcode: $this.data('zipcode')
        });
    });

    // ซ่อนผลการค้นหาเมื่อคลิกที่อื่น
    $(document).on('click', function(e) {
        if(!$(e.target).closest('.form-group').length) {
            $('.search-results').hide();
        }
    });
} 

/**
 * ระบบค้นหาข้อมูลจากชื่อและเบอร์โทร
 * @param {Object} options - ตัวเลือกการตั้งค่า
 * @param {string} options.fullnameField - ID ของช่องกรอกชื่อ (default: '#delivery_fullname')
 * @param {string} options.mobileField - ID ของช่องกรอกเบอร์โทร (default: '#delivery_mobile')
 * @param {number} options.minLength - จำนวนตัวอักษรขั้นต่ำก่อนเริ่มค้นหา (default: 2)
 * @param {number} options.delay - ระยะเวลารอก่อนค้นหา (ms) (default: 300)
 * @param {string} options.apiPrefix - prefix ของ API URL (default: '')
 * @param {Function} options.onSelect - ฟังก์ชันที่จะทำงานเมื่อเลือกผลการค้นหา
 */
function initCustomerSearch(options = {}) {
    const config = {
        fullnameField: options.fullnameField || '#delivery_fullname',
        mobileField: options.mobileField || '#delivery_mobile',
        minLength: options.minLength || 2,
        delay: options.delay || 300,
        apiPrefix: options.apiPrefix || '',
        onSelect: options.onSelect || function() {}
    };

    let typingTimer;

    function searchCustomer(value, field) {
        $.ajax({
            url: config.apiPrefix + '/api/address/searchCustomerAddress',
            method: 'GET',
            data: {
                term: value,
                field: field
            },
            success: function(results) {
                const $results = $(`#${field}-results`);
                $results.empty();

                if (results.length > 0) {
                    results.forEach(result => {
                        const $item = $('<div>')
                            .addClass('search-item')
                            .text(result.text)
                            .data({
                                'province': result.province,
                                'amphoe': result.amphoe,
                                'tambon': result.tambon,
                                'zipcode': result.zipcode,
                                'fullname': result.fullname,
                                'mobile': result.mobile,
                                'address': result.address
                            });
                        $results.append($item);
                    });
                    $results.show();
                } else {
                    $results.hide();
                }
            }
        });
    }

    // ตั้งค่า Event Listeners
    [
        config.fullnameField.replace('#', ''),
        config.mobileField.replace('#', '')
    ].forEach(field => {
        $(`#${field}`).on('keyup', function() {
            clearTimeout(typingTimer);
            const $input = $(this);
            
            if ($input.val().length >= config.minLength) {
                typingTimer = setTimeout(() => {
                    searchCustomer($input.val(), field);
                }, config.delay);
            } else {
                $(`#${field}-results`).hide();
            }
        });
    });

    // จัดการการคลิกเลือกผลการค้นหา
    $(document).on('click', '.search-item', function() {
        const $this = $(this);
        const parent = $this.parent();
        const inputId = parent.attr('id').replace('-results', '');
        
        // เมื่อเลือกชื่อ ให้เติมข้อมูลทั้งหมด
        if (inputId === 'delivery_fullname') {
            $('#delivery_fullname').val($this.data('fullname'));
            $('#delivery_mobile').val($this.data('mobile'));
            $('#delivery_address').val($this.data('address'));
            $('#delivery_province').val($this.data('province'));
            $('#delivery_district').val($this.data('amphoe'));
            $('#delivery_subdistrict').val($this.data('tambon'));
            $('#delivery_postcode').val($this.data('zipcode'));
        } 
        // เมื่อเลือกเบอร์โทร ให้เติมข้อมูลทั้งหมดเช่นกัน
        else if (inputId === 'delivery_mobile') {
            $('#delivery_fullname').val($this.data('fullname'));
            $('#delivery_mobile').val($this.data('mobile'));
            $('#delivery_address').val($this.data('address'));
            $('#delivery_province').val($this.data('province'));
            $('#delivery_district').val($this.data('amphoe'));
            $('#delivery_subdistrict').val($this.data('tambon'));
            $('#delivery_postcode').val($this.data('zipcode'));
        }

        parent.hide();

        // เรียก callback function
        config.onSelect({
            fullname: $this.data('fullname'),
            mobile: $this.data('mobile'),
            address: $this.data('address'),
            province: $this.data('province'),
            amphoe: $this.data('amphoe'),
            tambon: $this.data('tambon'),
            zipcode: $this.data('zipcode')
        });
    });

    // ซ่อนผลการค้นหาเมื่อคลิกที่อื่น
    $(document).on('click', function(e) {
        if(!$(e.target).closest('.form-group').length) {
            $('.search-results').hide();
        }
    });
}
