<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fa fa-exclamation-triangle"></i> 
                        <?php echo e(__('ไม่พบข้อมูล')); ?>

                    </h4>
                </div>

                <div class="card-body text-center">
                    <h5 class="text-danger mb-4">
                        <?php echo e($message ?? 'ไม่พบหน้าที่คุณต้องการ'); ?>

                    </h5>

                    <div class="mb-4">
                    <div class="error-code" style="font-size: 120px; font-weight: bold; 
                         background: linear-gradient(45deg, #ff6b6b, #ff8787);
                         -webkit-background-clip: text;
                         -webkit-text-fill-color: transparent;
                         text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
                         margin-bottom: 20px;">
                        404
                    </div>

                    <p class="error-message" style="font-size: 1.5rem; 
                       color: #495057;
                       margin-bottom: 1.5rem;
                       font-weight: 300;
                       line-height: 1.6;">
                        ขออภัย ไม่พบหน้าที่คุณกำลังค้นหา
                    </p>

                    <div class="error-details" style="color: #868e96; 
                         font-size: 0.9rem;
                         padding: 10px;
                         border-radius: 5px;
                         background: rgba(0,0,0,0.03);">
                        <p class="mb-1">รหัสข้อผิดพลาด: 404 Not Found</p>
                        
                    </div>
                    </div>

                    <div>
                        <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary mr-2">
                            <i class="fa fa-arrow-left"></i> 
                            <?php echo e(__('กลับไปหน้าก่อนหน้า')); ?>

                        </a>
                        
                        <a href="<?php echo e(route('home')); ?>" class="btn btn-primary">
                            <i class="fa fa-home"></i> 
                            <?php echo e(__('กลับหน้าหลัก')); ?>

                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('extra-css'); ?>
<style>
    .card-header {
        border-bottom: none;
    }
    .card {
        border: none;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    .btn {
        padding: 10px 20px;
        border-radius: 5px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\skjtrack\backoffice\resources\views/errors/404.blade.php ENDPATH**/ ?>