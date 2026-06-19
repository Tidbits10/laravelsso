<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">QR scanner</h1>
        <div class="text-muted">Verify approved documents against the database.</div>
    </div>
    <a class="btn btn-outline-danger" href="<?php echo e(route('admin.dashboard')); ?>">Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <div id="reader" style="min-height: 320px;"></div>
                <form id="manual-form" class="mt-3">
                    <?php echo csrf_field(); ?>
                    <label class="form-label">Manual QR text</label>
                    <div class="input-group">
                        <input id="qr-text" class="form-control" name="qr_text" placeholder="Paste QR text here">
                        <button id="verify-button" class="btn btn-danger" type="submit">Verify</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Verification result</h2>
                <div id="result" class="border rounded p-4 bg-light">
                    <div class="text-muted">Waiting for scan or manual verification...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const result = document.getElementById('result');
const token = document.querySelector('input[name="_token"]').value;
const verifyButton = document.getElementById('verify-button');

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function (char) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
    });
}

function renderResult(data) {
    if (data.success) {
        const request = data.request;
        result.className = 'border border-success rounded p-4 bg-success-subtle';
        result.innerHTML = `
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge text-bg-success">VERIFIED</span>
                <strong>Document record found</strong>
            </div>
            <dl class="row mb-0">
                <dt class="col-sm-4">Request ID</dt><dd class="col-sm-8">REQ-${escapeHtml(request.id)}</dd>
                <dt class="col-sm-4">Student No.</dt><dd class="col-sm-8">${escapeHtml(request.student_number)}</dd>
                <dt class="col-sm-4">Name</dt><dd class="col-sm-8">${escapeHtml(request.name)}</dd>
                <dt class="col-sm-4">Document</dt><dd class="col-sm-8">${escapeHtml(request.type)}</dd>
                <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><strong>${escapeHtml(request.status)}</strong></dd>
                <dt class="col-sm-4">Remarks</dt><dd class="col-sm-8">${escapeHtml(request.remarks || '-')}</dd>
            </dl>
        `;
        return;
    }

    result.className = 'border border-danger rounded p-4 bg-danger-subtle';
    result.innerHTML = `
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge text-bg-danger">INVALID</span>
            <strong>Document not verified</strong>
        </div>
        <div>${escapeHtml(data.message || 'QR code was not found in the database.')}</div>
    `;
}

async function verify(qrText) {
    qrText = qrText.trim();

    if (!qrText) {
        renderResult({ success: false, message: 'Please scan or enter QR text first.' });
        return;
    }

    verifyButton.disabled = true;
    verifyButton.textContent = 'Verifying...';

    try {
        const response = await fetch('<?php echo e(route('api.verify')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ qr_text: qrText })
        });

        const data = await response.json();
        renderResult(data);
    } catch (error) {
        renderResult({ success: false, message: 'Verification request failed. Please refresh and try again.' });
    } finally {
        verifyButton.disabled = false;
        verifyButton.textContent = 'Verify';
    }
}

document.getElementById('manual-form').addEventListener('submit', function (event) {
    event.preventDefault();
    verify(document.getElementById('qr-text').value);
});

if (window.Html5QrcodeScanner) {
    const scanner = new Html5QrcodeScanner('reader', { fps: 10, qrbox: 250 });
    scanner.render(function (decodedText) {
        document.getElementById('qr-text').value = decodedText;
        verify(decodedText);
    });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\elmer\Documents\Codex\2026-06-19\files-mentioned-by-the-user-mid\work\laravel-sso-app\resources\views/admin/scanner.blade.php ENDPATH**/ ?>