<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>PUP SSO - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen font-sans transition-colors duration-300" onload="updateClock()">
    <nav class="bg-red-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col lg:flex-row gap-4 lg:gap-0 justify-between lg:items-center">
            <div>
                <h1 class="text-2xl font-black tracking-wider">PUP SSO</h1>
                <span class="text-yellow-400 text-xs font-bold uppercase tracking-widest">Enterprise Admin Dashboard</span>
            </div>
            <div class="flex flex-wrap gap-3 items-center">
                <span class="text-sm font-bold text-gray-300 mr-4 hidden md:block" id="navTime">Loading time...</span>
                <button onclick="toggleDarkMode()" class="bg-gray-700 hover:bg-gray-600 p-2 rounded-full transition"><span id="themeIcon">Moon</span></button>
                <a href="<?php echo e(route('admin.export')); ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-2 font-bold shadow transition"><i class="fa-solid fa-download"></i> Export</a>
                <a href="<?php echo e(route('admin.scanner')); ?>" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 flex items-center gap-2 shadow transition"><i class="fa-solid fa-qrcode"></i> Scanner</a>
                <form method="post" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="bg-red-800 hover:bg-red-700 px-5 py-2 rounded font-bold shadow transition">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <?php if(session('success')): ?>
            <div class="mb-4 bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="mb-4 bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <?php
                $cards = [
                    ['title' => 'Total Requests', 'value' => $stats['total'], 'color' => 'border-blue-500'],
                    ['title' => 'Pending', 'value' => $stats['pending'], 'color' => 'border-yellow-500'],
                    ['title' => 'Approved / Ready', 'value' => $stats['approved'] + $stats['ready'], 'color' => 'border-green-500'],
                    ['title' => 'Rejected', 'value' => $stats['rejected'], 'color' => 'border-red-500'],
                ];
            ?>
            <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white dark:bg-gray-800 p-4 rounded shadow-md border-l-4 <?php echo e($card['color']); ?>">
                    <p class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider"><?php echo e($card['title']); ?></p>
                    <p class="text-3xl font-black text-gray-800 dark:text-white"><?php echo e($card['value']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md border-t-4 border-blue-500 mb-8 flex flex-col md:flex-row gap-8 items-center">
            <div class="w-full md:w-1/3 flex justify-center">
                <div class="w-48 h-48"><canvas id="statusChart"></canvas></div>
            </div>
            <div class="w-full md:w-2/3">
                <h2 class="text-xl font-black text-gray-800 dark:text-white mb-2">Request Distribution Analytics</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">A visual representation of the current document request workload.</p>
                <div class="flex gap-4">
                    <div class="bg-green-50 dark:bg-gray-700 px-4 py-2 rounded border border-green-200 dark:border-gray-600">
                        <span class="text-green-700 dark:text-green-400 font-bold text-sm">Clearance Rate</span>
                        <p class="text-lg font-black dark:text-white"><?php echo e($stats['total'] > 0 ? round((($stats['approved'] + $stats['ready']) / $stats['total']) * 100) : 0); ?>%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-md border-t-4 border-yellow-500 mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-xl font-black text-gray-800 dark:text-white">Batch SIS Import (CSV Upload)</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Upload CSV columns: <strong>student_number</strong>, <strong>name</strong>, <strong>email</strong></p>
            </div>
            <form action="<?php echo e(route('admin.import')); ?>" method="POST" enctype="multipart/form-data" class="flex items-center gap-4 bg-gray-50 dark:bg-gray-700 p-3 border dark:border-gray-600 rounded w-full md:w-auto">
                <?php echo csrf_field(); ?>
                <input type="file" name="csv_file" accept=".csv" required class="block w-full text-sm text-gray-500 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-red-900 file:text-white hover:file:bg-red-800 cursor-pointer">
                <button class="bg-yellow-500 text-yellow-900 font-bold px-6 py-2 rounded shadow hover:bg-yellow-400 transition whitespace-nowrap">Upload</button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded shadow-md overflow-hidden flex flex-col">
                <div class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600 px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-4">
                    <h2 class="text-lg font-black text-gray-800 dark:text-white">Processing Queue</h2>
                    <div class="flex gap-2 items-center w-full md:w-auto">
                        <select id="statusFilter" onchange="filterTable()" class="px-3 py-2 border dark:border-gray-600 rounded text-sm dark:bg-gray-600 dark:text-white focus:outline-none focus:border-red-900">
                            <option value="">All Statuses</option>
                            <option value="Pending">Pending Only</option>
                            <option value="Approved">Approved</option>
                            <option value="Ready to Claim">Ready to Claim</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search ID/Name..." class="px-4 py-2 border dark:border-gray-600 rounded text-sm w-full md:w-64 focus:outline-none focus:border-red-900 dark:bg-gray-600 dark:text-white">
                    </div>
                </div>

                <div class="p-0 overflow-x-auto flex-1">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="border-b dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                <th class="py-3 px-6 font-bold">Student</th>
                                <th class="py-3 px-6 font-bold">Document</th>
                                <th class="py-3 px-6 font-bold">Status</th>
                                <th class="py-3 px-6 font-bold text-right">Action / Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="queueTable" class="text-sm divide-y divide-gray-100 dark:divide-gray-600">
                            <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors data-row">
                                    <td class="py-4 px-6">
                                        <span class="font-black text-gray-800 dark:text-white">REQ-#<?php echo e($request->id); ?> | <?php echo e($request->user->student_number); ?></span><br>
                                        <span class="text-xs font-bold text-red-900 dark:text-red-400 uppercase search-name"><?php echo e($request->user->name); ?></span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="font-medium text-gray-800 dark:text-gray-200 block"><?php echo e($request->type); ?></span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 italic block"><?php echo e($request->reason); ?></span>
                                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400 mt-1 block"><i class="fa-regular fa-clock mr-1"></i><?php echo e(optional($request->date_requested)->format('M d, Y h:i A') ?? 'Legacy Data'); ?></span>
                                    </td>
                                    <td class="py-4 px-6 status-cell">
                                        <?php
                                            $badge = match ($request->status) {
                                                'Ready to Claim' => 'bg-purple-100 text-purple-700',
                                                'Approved' => 'bg-green-100 text-green-700',
                                                'Rejected' => 'bg-red-100 text-red-700',
                                                default => 'bg-yellow-100 text-yellow-700',
                                            };
                                        ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-bold uppercase tracking-wide <?php echo e($badge); ?>"><?php echo e($request->status); ?></span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <?php if($request->status === 'Pending'): ?>
                                            <div class="flex flex-col gap-2 items-end">
                                                <input type="text" id="rem-<?php echo e($request->id); ?>" placeholder="Remarks..." class="text-xs px-2 py-1.5 border rounded w-48 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <div class="flex gap-2">
                                                    <button onclick="processAction('<?php echo e(route('admin.requests.approve', $request)); ?>', '<?php echo e($request->id); ?>')" class="bg-green-600 text-white px-3 py-1.5 rounded text-xs hover:bg-green-700 font-bold shadow-sm transition">Approve</button>
                                                    <button onclick="processAction('<?php echo e(route('admin.requests.reject', $request)); ?>', '<?php echo e($request->id); ?>')" class="bg-red-600 text-white px-3 py-1.5 rounded text-xs hover:bg-red-700 font-bold shadow-sm transition">Reject</button>
                                                </div>
                                            </div>
                                        <?php elseif($request->status === 'Approved'): ?>
                                            <div class="flex flex-col items-end gap-1">
                                                <?php if($request->remarks): ?><span class="text-xs text-gray-500 dark:text-gray-400 max-w-[200px] truncate" title="<?php echo e($request->remarks); ?>">Admin: <?php echo e($request->remarks); ?></span><?php endif; ?>
                                                <button onclick="processAction('<?php echo e(route('admin.requests.ready', $request)); ?>')" class="bg-purple-600 text-white font-bold px-3 py-1.5 rounded text-xs hover:bg-purple-700 shadow-sm transition mt-1">Mark Ready</button>
                                            </div>
                                        <?php else: ?>
                                            <div class="flex flex-col items-end">
                                                <span class="text-gray-400 font-bold text-xs uppercase tracking-wider">Completed</span>
                                                <?php if($request->remarks): ?><span class="text-xs text-gray-500 dark:text-gray-400 italic mt-1 max-w-[200px] truncate" title="<?php echo e($request->remarks); ?>">Note: <?php echo e($request->remarks); ?></span><?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="4" class="py-8 text-center text-gray-500">No requests yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 border-t dark:border-gray-600 px-6 py-3 flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400" id="pageInfo">Showing entries</span>
                    <div class="flex gap-2">
                        <button onclick="changePage(-1)" class="bg-white dark:bg-gray-600 border dark:border-gray-500 text-gray-600 dark:text-white px-3 py-1 rounded text-sm hover:bg-gray-100 transition shadow-sm">Previous</button>
                        <button onclick="changePage(1)" class="bg-white dark:bg-gray-600 border dark:border-gray-500 text-gray-600 dark:text-white px-3 py-1 rounded text-sm hover:bg-gray-100 transition shadow-sm">Next</button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-8">
                <div class="bg-white dark:bg-gray-800 rounded shadow-md border-t-4 border-yellow-500 flex flex-col flex-1 max-h-[500px]">
                    <div class="px-6 py-4 border-b dark:border-gray-600">
                        <h2 class="text-lg font-black text-gray-800 dark:text-white">Today's Appointments</h2>
                    </div>
                    <div class="p-6 flex-1 overflow-y-auto">
                        <ul class="space-y-3">
                            <?php $__empty_1 = true; $__currentLoopData = $queue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <li class="p-4 rounded border dark:border-gray-700 <?php echo e($appointment->status === 'Scheduled' ? 'border-yellow-200 bg-yellow-50 dark:bg-gray-700' : 'border-gray-100 bg-gray-50 opacity-60'); ?> flex justify-between items-center">
                                    <div>
                                        <span class="font-black text-xl text-red-900 dark:text-red-400 block leading-tight"><?php echo e($appointment->queue_number); ?></span>
                                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase"><?php echo e($appointment->user->student_number); ?> | <?php echo e($appointment->time); ?></span>
                                    </div>
                                    <?php if($appointment->status === 'Scheduled'): ?>
                                        <button onclick="processAction('<?php echo e(route('admin.appointments.serve', $appointment)); ?>')" class="bg-red-900 text-white font-bold px-3 py-1.5 rounded text-xs hover:bg-red-800 shadow-sm transition">Serve</button>
                                    <?php else: ?>
                                        <span class="text-green-600 dark:text-green-400 font-black text-xs uppercase tracking-wider">Served</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <li class="text-sm text-gray-500">No appointments yet.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded shadow-md border-t-4 border-gray-800">
            <div class="px-6 py-4 border-b dark:border-gray-600 bg-gray-50 dark:bg-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-black text-gray-800 dark:text-white">System Audit Logs</h2>
            </div>
            <div class="p-0 overflow-x-auto max-h-64 overflow-y-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-600">
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="py-3 px-6 text-gray-500 dark:text-gray-400 whitespace-nowrap w-48 text-xs font-mono"><?php echo e($log->created_at->format('M d, Y h:i A')); ?></td>
                                <td class="py-3 px-6"><span class="bg-gray-200 dark:bg-gray-600 dark:text-gray-200 px-2 py-1 rounded text-xs font-bold"><?php echo e($log->admin_username); ?></span></td>
                                <td class="py-3 px-6 font-medium text-gray-700 dark:text-gray-200"><?php echo e($log->action); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td class="py-4 px-6 text-gray-500">No audit logs yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        function updateClock() {
            const now = new Date();
            document.getElementById('navTime').textContent = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric'}) + ' | ' + now.toLocaleTimeString('en-US');
        }
        setInterval(updateClock, 1000);

        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            const icon = document.getElementById('themeIcon');
            if (document.documentElement.classList.contains('dark')) {
                localStorage.theme = 'dark'; icon.textContent = 'Sun';
            } else {
                localStorage.theme = 'light'; icon.textContent = 'Moon';
            }
        }
        if (localStorage.theme === 'dark') {
            document.documentElement.classList.add('dark');
            document.getElementById('themeIcon').textContent = 'Sun';
        }

        function processAction(url, id = null) {
            const remarks = id ? (document.getElementById('rem-' + id)?.value || '') : '';
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ remarks })
            }).then(res => res.json()).then(data => {
                Toastify({ text: data.message, duration: 1500, gravity: 'top', position: 'right', style: { background: data.success ? '#059669' : '#DC2626', borderRadius: '8px', fontWeight: 'bold' } }).showToast();
                if (data.success) setTimeout(() => location.reload(), 1500);
            }).catch(() => {
                Toastify({ text: 'Action failed. Please refresh and try again.', duration: 2500, style: { background: '#DC2626', borderRadius: '8px' } }).showToast();
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('statusChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Approved/Ready', 'Rejected'],
                    datasets: [{
                        data: [<?php echo e($stats['pending']); ?>, <?php echo e($stats['approved'] + $stats['ready']); ?>, <?php echo e($stats['rejected']); ?>],
                        backgroundColor: ['#eab308', '#22c55e', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: '#6b7280', font: {size: 10} } } }, cutout: '70%' }
            });
            filterTable();
        });

        let currentPage = 1;
        const rowsPerPage = 5;
        let filteredRows = [];

        function filterTable() {
            const searchInput = document.getElementById('searchInput').value.toUpperCase();
            const statusFilter = document.getElementById('statusFilter').value.toUpperCase();
            const allRows = Array.from(document.querySelectorAll('.data-row'));

            filteredRows = allRows.filter(row => {
                const textData = row.textContent.toUpperCase();
                const statusData = row.querySelector('.status-cell').textContent.toUpperCase();
                const matchesSearch = textData.indexOf(searchInput) > -1;
                const matchesStatus = statusFilter === '' || statusData.indexOf(statusFilter) > -1;
                row.style.display = 'none';
                return matchesSearch && matchesStatus;
            });
            currentPage = 1;
            renderPagination();
        }

        function changePage(dir) {
            const maxPage = Math.ceil(filteredRows.length / rowsPerPage);
            if (currentPage + dir >= 1 && currentPage + dir <= maxPage) {
                currentPage += dir;
                renderPagination();
            }
        }

        function renderPagination() {
            filteredRows.forEach(row => row.style.display = 'none');
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            filteredRows.slice(start, end).forEach(row => { row.style.display = ''; });

            const total = filteredRows.length;
            const startText = total === 0 ? 0 : start + 1;
            const endText = Math.min(end, total);
            document.getElementById('pageInfo').textContent = `Showing ${startText} to ${endText} of ${total} entries`;
        }
    </script>
</body>
</html>
<?php /**PATH C:\Users\elmer\Documents\Codex\2026-06-19\files-mentioned-by-the-user-mid\work\laravel-sso-app\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>