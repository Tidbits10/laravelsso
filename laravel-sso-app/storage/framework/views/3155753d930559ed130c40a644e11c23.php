<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(config('app.name')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; }
        .topbar { background: #800000; color: #fff; }
        .brand-dot { width: 12px; height: 12px; background: #ffc107; border-radius: 50%; display: inline-block; }
        .stat { border-left: 4px solid #800000; }
    </style>
</head>
<body>
    <nav class="navbar topbar mb-4">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h1 text-white"><span class="brand-dot me-2"></span>PUP San Pedro SSO</span>
            <?php if(auth()->guard()->check()): ?>
                <form method="post" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-sm btn-light">Logout</button>
                </form>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container-fluid px-4 pb-5">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        <?php echo $__env->yieldContent('content'); ?>
    </main>
</body>
</html>
<?php /**PATH C:\Users\elmer\Documents\Codex\2026-06-19\files-mentioned-by-the-user-mid\work\laravel-sso-app\resources\views/layouts/app.blade.php ENDPATH**/ ?>