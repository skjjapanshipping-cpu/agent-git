@extends('layouts.app')

@section('content')
    <div class="auth-page">
        <div class="auth-card auth-card-wide">
            <!-- Logo -->
            <div class="auth-logo">
                <img src="{{ asset('img/skj-logo-white.png') }}" alt="SKJ Japan Shipping">
                <p>สมัครสมาชิก</p>
            </div>

            <!-- Register Form -->
            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf

                <!-- Account Info Section -->
                <div class="section-title">
                    <i class="fa fa-user"></i> ข้อมูลบัญชี
                </div>

                <div class="form-row">
                    <!-- Name -->
                    <div class="form-group">
                        <label for="name">ชื่อ - นามสกุล</label>
                        <div class="input-icon-wrapper">
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ old('name') }}" placeholder="ชื่อ-นามสกุล" required autofocus>
                            <i class="fa fa-user input-icon"></i>
                        </div>
                        @error('name')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">อีเมล</label>
                        <div class="input-icon-wrapper">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" placeholder="อีเมล" required>
                            <i class="fa fa-envelope input-icon"></i>
                        </div>
                        @error('email')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <!-- Mobile -->
                    <div class="form-group">
                        <label for="mobile">เบอร์โทรศัพท์</label>
                        <div class="input-icon-wrapper">
                            <input id="mobile" type="tel" class="form-control @error('mobile') is-invalid @enderror"
                                name="mobile" value="{{ old('mobile') }}" placeholder="0812345678" minlength="10"
                                maxlength="10" pattern="\d*" required>
                            <i class="fa fa-phone input-icon"></i>
                        </div>
                        @error('mobile')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">รหัสผ่าน</label>
                        <div class="input-icon-wrapper">
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password"
                                placeholder="รหัสผ่าน" required>
                            <i class="fa fa-lock input-icon"></i>
                            <button type="button" class="password-toggle-btn" onclick="togglePassword('password')">
                                <i class="fa fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password-confirm">ยืนยันรหัสผ่าน</label>
                        <div class="input-icon-wrapper">
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation"
                                placeholder="ยืนยันรหัสผ่าน" required>
                            <i class="fa fa-lock input-icon"></i>
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div class="section-title">
                    <i class="fa fa-map-marker"></i> ที่อยู่
                </div>

                <div class="form-row full">
                    <!-- Address -->
                    <div class="form-group">
                        <label for="addr">ที่อยู่</label>
                        <div class="input-icon-wrapper">
                            <input id="addr" type="text" class="form-control @error('addr') is-invalid @enderror"
                                name="addr" value="{{ old('addr') }}" placeholder="บ้านเลขที่ ซอย ถนน" required>
                            <i class="fa fa-home input-icon"></i>
                        </div>
                        @error('addr')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <!-- Province -->
                    <div class="form-group">
                        <label for="province">จังหวัด</label>
                        <div class="input-icon-wrapper position-relative">
                            <input id="province" type="text" class="form-control @error('province') is-invalid @enderror"
                                name="province" value="{{ old('province') }}" placeholder="พิมพ์เพื่อค้นหา" required>
                            <i class="fa fa-map input-icon"></i>
                            <div id="province-results" class="search-results"></div>
                        </div>
                        @error('province')
                            <span class="invalid-feedback"><strong>กรุณากรอก จังหวัด</strong></span>
                        @enderror
                    </div>

                    <!-- District -->
                    <div class="form-group">
                        <label for="distrinct">เขต/อำเภอ</label>
                        <div class="input-icon-wrapper position-relative">
                            <input id="distrinct" type="text" class="form-control @error('distrinct') is-invalid @enderror"
                                name="distrinct" value="{{ old('distrinct') }}" placeholder="พิมพ์เพื่อค้นหา" required>
                            <i class="fa fa-map-o input-icon"></i>
                            <div id="distrinct-results" class="search-results"></div>
                        </div>
                        @error('distrinct')
                            <span class="invalid-feedback"><strong>กรุณากรอก เขต/อำเภอ</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <!-- Subdistrict -->
                    <div class="form-group">
                        <label for="subdistrinct">แขวง/ตำบล</label>
                        <div class="input-icon-wrapper position-relative">
                            <input id="subdistrinct" type="text"
                                class="form-control @error('subdistrinct') is-invalid @enderror" name="subdistrinct"
                                value="{{ old('subdistrinct') }}" placeholder="พิมพ์เพื่อค้นหา" required>
                            <i class="fa fa-map-pin input-icon"></i>
                            <div id="subdistrinct-results" class="search-results"></div>
                        </div>
                        @error('subdistrinct')
                            <span class="invalid-feedback"><strong>กรุณากรอก แขวง/ตำบล</strong></span>
                        @enderror
                    </div>

                    <!-- Postcode -->
                    <div class="form-group">
                        <label for="postcode">รหัสไปรษณีย์</label>
                        <div class="input-icon-wrapper position-relative">
                            <input id="postcode" type="text" class="form-control @error('postcode') is-invalid @enderror"
                                name="postcode" value="{{ old('postcode') }}" placeholder="รหัสไปรษณีย์" required>
                            <i class="fa fa-hashtag input-icon"></i>
                            <div id="postcode-results" class="search-results"></div>
                        </div>
                        @error('postcode')
                            <span class="invalid-feedback"><strong>กรุณากรอก รหัสไปรษณีย์</strong></span>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-auth-primary">
                    <i class="fa fa-user-plus"></i> สมัครสมาชิก
                </button>

                <!-- Links -->
                <div class="auth-links">
                    มีบัญชีอยู่แล้ว? <a href="{{ route('login') }}">เข้าสู่ระบบ</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('extra-script')
    <script src="{{ asset('js/thai-address-search.js') }}"></script>
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '-icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        $(document).ready(function () {
            // Thai Address Search
            initThaiAddressSearch({
                formId: 'form',
                provinceField: '#province',
                amphoeField: '#distrinct',
                tambonField: '#subdistrinct',
                zipcodeField: '#postcode',
                onAddressSelect: function (address) {
                    console.log('เลือกที่อยู่:', address);
                }
            });
        });
    </script>
@endsection