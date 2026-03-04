<?php $__env->startSection('content'); ?>
    <div class="auth-page">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <img src="<?php echo e(asset('img/skj-logo-white.png')); ?>" alt="SKJ Japan Shipping">
                <p>ระบบติดตามสินค้า</p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="<?php echo e(route('login')); ?>" class="auth-form">
                <?php echo csrf_field(); ?>

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email"><?php echo e(__('อีเมล')); ?></label>
                    <div class="input-icon-wrapper">
                        <input id="email" type="text" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="email"
                            value="<?php echo e(old('email')); ?>" required autocomplete="email" autofocus
                            placeholder="กรอกอีเมลของคุณ">
                        <i class="fa fa-envelope input-icon"></i>
                    </div>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="invalid-feedback" role="alert">
                            <strong><?php echo e($message); ?></strong>
                        </span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password"><?php echo e(__('รหัสผ่าน')); ?></label>
                    <div class="input-icon-wrapper">
                        <input id="password" type="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            name="password" required autocomplete="current-password" placeholder="กรอกรหัสผ่าน">
                        <i class="fa fa-lock input-icon"></i>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword()">
                            <i class="fa fa-eye" id="password-toggle-icon"></i>
                        </button>
                    </div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="invalid-feedback" role="alert">
                            <strong><?php echo e($message); ?></strong>
                        </span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Remember Me -->
                <div class="remember-check">
                    <input type="checkbox" name="remember" id="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                    <label for="remember"><?php echo e(__('จดจำฉัน')); ?></label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-auth-primary">
                    <i class="fa fa-sign-in"></i> <?php echo e(__('เข้าสู่ระบบ')); ?>

                </button>

                <!-- Links -->
                <div class="auth-links">
                    <a href="<?php echo e(route('register')); ?>">สมัครสมาชิก</a>
                    <?php if(Route::has('password.request')): ?>
                        <span class="divider">|</span>
                        <a href="<?php echo e(route('password.request')); ?>"><?php echo e(__('ลืมรหัสผ่าน?')); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('extra-script'); ?>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\skjtrack\backoffice\resources\views/auth/login.blade.php ENDPATH**/ ?>