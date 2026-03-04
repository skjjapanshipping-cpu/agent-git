/**
 * Thai Address Search
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
        $.get('/api/address/search', { term: searchText, type: type }, function(data) {
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