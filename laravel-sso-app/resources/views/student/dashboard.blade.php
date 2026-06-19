<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PUP SSO - Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body class="bg-gray-100 min-h-screen font-sans relative" onload="updateClock()">
    <nav class="bg-red-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-black tracking-wider">PUP SSO</h1>
                <span class="text-yellow-400 text-xs font-bold uppercase tracking-widest">Student Portal</span>
            </div>
            <div class="flex gap-4 items-center">
                <button onclick="togglePasswordModal()" class="text-white hover:text-yellow-400 transition text-sm font-bold"><i class="fa-solid fa-gear mr-1"></i> Settings</button>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-red-800 hover:bg-red-700 px-5 py-2 rounded font-bold transition shadow">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 py-8">
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="mb-8 flex justify-between items-end border-b border-gray-300 pb-4">
            <div>
                <h2 class="text-3xl font-black text-gray-800"><span id="greeting">Hello</span>, {{ auth()->user()->name }}!</h2>
                <p class="text-gray-500 font-medium">{{ auth()->user()->student_number }} | <span id="dateDisplay">Loading date...</span></p>
            </div>
            <div class="text-right hidden md:block">
                <p class="text-4xl font-black text-red-900" id="timeDisplay">00:00:00</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-yellow-500 mb-8">
            <h2 class="text-xl font-black text-gray-800 mb-4">Request a Document</h2>
            <form action="{{ route('student.requests.store') }}" method="POST" class="flex flex-col md:flex-row gap-4">
                @csrf
                <select name="type" required class="border px-4 py-2 rounded focus:outline-none focus:border-red-900 flex-1 bg-gray-50">
                    <option value="" disabled selected>Select Document Type...</option>
                    <option value="Transcript of Records (TOR)">Transcript of Records (TOR)</option>
                    <option value="Certificate of Registration (COR)">Certificate of Registration (COR)</option>
                    <option value="Good Moral Certificate">Good Moral Certificate</option>
                    <option value="ID Replacement">ID Replacement</option>
                    <option value="Excuse Slip">Excuse Slip</option>
                    <option value="Off-Campus Request">Off-Campus Request</option>
                    <option value="Student Activity Approval">Student Activity Approval</option>
                </select>
                <input type="text" name="reason" placeholder="Purpose of Request..." required class="border px-4 py-2 rounded focus:outline-none focus:border-red-900 flex-1 bg-gray-50">
                <button class="bg-red-900 text-white font-bold px-6 py-2 rounded shadow hover:bg-red-800 transition">Submit Request</button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-red-900 mb-8">
            <h2 class="text-xl font-black text-gray-800 mb-4">Schedule Appointment</h2>
            <form action="{{ route('student.appointments.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <input type="date" name="date" required class="border px-4 py-2 rounded focus:outline-none focus:border-red-900 bg-gray-50">
                <input type="time" name="time" required class="border px-4 py-2 rounded focus:outline-none focus:border-red-900 bg-gray-50">
                <button class="bg-yellow-500 text-red-900 font-bold px-6 py-2 rounded shadow hover:bg-yellow-400 transition">Book Appointment</button>
            </form>
        </div>

        @php
            $activeReqs = $requests->where('status', '!=', 'Rejected');
            $historyReqs = $requests->where('status', 'Rejected');
        @endphp

        @if ($notifications->count())
            <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded text-yellow-900 font-bold">
                <i class="fa-solid fa-bell mr-2"></i>You have {{ $notifications->count() }} document(s) ready to claim.
            </div>
        @endif

        <h2 class="text-2xl font-black text-gray-800 mb-4 border-l-4 border-red-900 pl-3">Active Requests</h2>
        <div class="flex flex-col gap-6 mb-12">
            @forelse ($activeReqs as $request)
                @php
                    $step1 = 'bg-gray-300 text-gray-500';
                    $step2 = 'bg-gray-300 text-gray-500';
                    $step3 = 'bg-gray-300 text-gray-500';
                    if ($request->status === 'Pending') { $step1 = 'bg-yellow-500 text-white shadow-[0_0_10px_rgba(234,179,8,0.5)]'; }
                    if ($request->status === 'Approved') { $step1 = 'bg-green-500 text-white'; $step2 = 'bg-green-500 text-white shadow-[0_0_10px_rgba(34,197,94,0.5)]'; }
                    if ($request->status === 'Ready to Claim') { $step1 = 'bg-green-500 text-white'; $step2 = 'bg-green-500 text-white'; $step3 = 'bg-purple-600 text-white shadow-[0_0_10px_rgba(147,51,234,0.5)]'; }
                @endphp
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
                    <div class="flex justify-between items-start mb-6 gap-4">
                        <div>
                            <h3 class="font-black text-lg text-gray-800">{{ $request->type }}</h3>
                            <p class="text-sm text-gray-500">Purpose: {{ $request->reason }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-black text-gray-400 block">REQ-#{{ $request->id }}</span>
                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded mt-1 inline-block border border-blue-100">
                                <i class="fa-regular fa-calendar mr-1"></i>{{ optional($request->date_requested)->format('M d, Y h:i A') ?? 'Just now' }}
                            </span>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-between w-full mt-4">
                        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 -z-10"></div>
                        <div class="flex flex-col items-center gap-2 bg-white px-2"><div class="w-8 h-8 rounded-full {{ $step1 }} flex items-center justify-center font-bold text-sm z-10"><i class="fa-solid fa-file-arrow-up"></i></div><span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Submitted</span></div>
                        <div class="flex flex-col items-center gap-2 bg-white px-2"><div class="w-8 h-8 rounded-full {{ $step2 }} flex items-center justify-center font-bold text-sm z-10"><i class="fa-solid fa-magnifying-glass"></i></div><span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Processing</span></div>
                        <div class="flex flex-col items-center gap-2 bg-white px-2"><div class="w-8 h-8 rounded-full {{ $step3 }} flex items-center justify-center font-bold text-sm z-10"><i class="fa-solid fa-box-open"></i></div><span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Ready</span></div>
                    </div>

                    @if ($request->remarks)
                        <div class="mt-6 p-3 bg-blue-50 border-l-4 border-blue-500 text-sm text-blue-800 rounded">
                            <i class="fa-solid fa-circle-info mr-2"></i><strong>Admin Note:</strong> {{ $request->remarks }}
                        </div>
                    @endif

                    @if ($request->qr_image)
                        <div class="mt-6 flex flex-col items-center p-4 bg-gray-50 border rounded-lg">
                            <p class="text-sm font-bold text-red-900 mb-2 uppercase tracking-widest">Show this QR to the Admin</p>
                            <img src="{{ $request->qr_image }}" alt="QR Code" onclick="openQRModal('{{ $request->qr_image }}', '{{ $request->qr_text }}')" class="w-32 h-32 border-4 border-white shadow-md rounded cursor-pointer hover:scale-105 transition transform">
                            <p class="text-xs text-gray-500 mt-2 text-center break-all">{{ $request->qr_text }}</p>
                            <p class="text-xs text-gray-400 mt-1 italic"><i class="fa-solid fa-magnifying-glass-plus mr-1"></i>Click to expand</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center p-8 bg-white rounded-lg shadow border border-gray-100"><i class="fa-regular fa-folder-open text-4xl text-gray-300 mb-3"></i><p class="text-gray-500 font-medium">No active requests.</p></div>
            @endforelse
        </div>

        <h2 class="text-2xl font-black text-gray-500 mb-4 border-l-4 border-gray-400 pl-3">Archive / History</h2>
        <div class="flex flex-col gap-4 opacity-75">
            @forelse ($historyReqs as $request)
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-gray-700"><i class="fa-solid fa-clock-rotate-left mr-2"></i>{{ $request->type }}</h3>
                        <p class="text-xs text-gray-500">{{ $request->status }} - {{ $request->remarks }}<br><span class="text-blue-500 font-bold">{{ optional($request->date_requested)->format('M d, Y h:i A') ?? 'Old Record' }}</span></p>
                    </div>
                    <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-1 rounded">Archived</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 italic">No archived records.</p>
            @endforelse
        </div>
    </div>

    <div id="qrModal" class="fixed inset-0 bg-black bg-opacity-80 z-[100] hidden flex-col items-center justify-center backdrop-blur-sm transition-opacity" onclick="closeQRModal()">
        <span class="absolute top-6 right-8 text-white text-4xl cursor-pointer hover:text-gray-300 transition">&times;</span>
        <div class="bg-white p-6 rounded-xl shadow-2xl transform transition-transform scale-100" onclick="event.stopPropagation()">
            <img id="modalQRImage" src="" alt="Expanded QR Code" class="w-64 h-64 md:w-80 md:h-80 object-contain">
            <input id="modalQRText" readonly onclick="this.select()" class="mt-4 w-full text-center text-xs border rounded px-2 py-2 text-gray-600">
            <p class="text-center text-red-900 font-black mt-4 uppercase tracking-widest text-sm">PUP San Pedro SSO</p>
        </div>
        <p class="text-white mt-6 font-medium animate-pulse text-sm tracking-wide">Present this barcode to the scanner</p>
    </div>

    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden flex-col items-center justify-center backdrop-blur-sm">
        <div class="bg-white p-6 rounded-lg shadow-2xl w-96 max-w-[calc(100%-32px)]">
            <h3 class="text-xl font-black text-gray-800 mb-4"><i class="fa-solid fa-lock mr-2"></i>Account Settings</h3>
            <div class="flex flex-col gap-3">
                <input type="password" id="currentPass" placeholder="Current Password" class="border px-3 py-2 rounded focus:outline-none focus:border-red-900 w-full bg-gray-50 text-sm">
                <input type="password" id="newPass" placeholder="New Password" class="border px-3 py-2 rounded focus:outline-none focus:border-red-900 w-full bg-gray-50 text-sm">
                <div class="flex justify-end gap-2 mt-2">
                    <button onclick="togglePasswordModal()" class="text-gray-500 hover:text-gray-700 text-sm font-bold px-4">Cancel</button>
                    <button onclick="updatePassword()" class="bg-red-900 text-white px-4 py-2 rounded text-sm font-bold shadow hover:bg-red-800">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div id="chatbot-container" class="fixed bottom-6 right-6 z-50">
        <div id="chat-window" class="hidden bg-white w-80 rounded-lg shadow-2xl border border-gray-200 flex flex-col overflow-hidden mb-4">
            <div class="bg-red-900 text-white p-4 flex justify-between items-center">
                <span class="font-bold"><i class="fa-solid fa-robot mr-2"></i> PUP SP Assistant</span>
                <button onclick="toggleChat()" class="text-white hover:text-gray-200"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div id="chat-messages" class="p-4 h-64 overflow-y-auto bg-gray-50 text-sm flex flex-col gap-3">
                <div class="bg-white p-3 rounded-lg border text-gray-700 shadow-sm self-start w-5/6">Hello! Ako ang virtual assistant ng PUP SSO. Ano ang gusto mong malaman?</div>
            </div>
            <div class="p-3 bg-white border-t flex flex-wrap gap-2 text-xs">
                <button onclick="askBot('Paano mag-request ng TOR?')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-full border transition">Mag-request ng TOR</button>
                <button onclick="askBot('Saan i-claim ang papel?')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-full border transition">Saan i-claim?</button>
                <button onclick="askBot('Paano kung Rejected?')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-full border transition">Bakit Rejected?</button>
            </div>
        </div>
        <button onclick="toggleChat()" class="bg-yellow-500 hover:bg-yellow-400 text-red-900 w-14 h-14 rounded-full shadow-2xl flex items-center justify-center text-2xl transition transform hover:scale-110 ml-auto block border-2 border-white">
            <i class="fa-regular fa-comment-dots"></i>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        function updateClock() {
            const now = new Date();
            document.getElementById('dateDisplay').textContent = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('timeDisplay').textContent = now.toLocaleTimeString('en-US');
            const hr = now.getHours();
            document.getElementById('greeting').textContent = hr < 12 ? 'Good Morning' : (hr < 18 ? 'Good Afternoon' : 'Good Evening');
        }
        setInterval(updateClock, 1000);

        function togglePasswordModal() {
            document.getElementById('passwordModal').classList.toggle('hidden');
            document.getElementById('passwordModal').classList.toggle('flex');
        }

        function updatePassword() {
            const current = document.getElementById('currentPass').value;
            const newp = document.getElementById('newPass').value;
            if (!current || !newp) return alert('Please fill all fields.');

            fetch('{{ route('student.password.update') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ current_password: current, new_password: newp })
            }).then(res => res.json()).then(data => {
                Toastify({ text: data.message, duration: 3000, style: { background: data.success ? '#059669' : '#DC2626', borderRadius: '8px' } }).showToast();
                if (data.success) {
                    togglePasswordModal();
                    document.getElementById('currentPass').value = '';
                    document.getElementById('newPass').value = '';
                }
            });
        }

        function openQRModal(imgSrc, qrText) {
            document.getElementById('modalQRImage').src = imgSrc;
            document.getElementById('modalQRText').value = qrText || '';
            const modal = document.getElementById('qrModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeQRModal() {
            const modal = document.getElementById('qrModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('modalQRImage').src = '';
        }

        function toggleChat() {
            document.getElementById('chat-window').classList.toggle('hidden');
        }

        function askBot(question) {
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.innerHTML += `<div class="bg-red-900 text-white p-3 rounded-lg shadow-sm self-end w-5/6 text-right">${question}</div>`;
            let answer = '';
            if (question.includes('TOR')) answer = "Para mag-request ng TOR, piliin ang Transcript of Records sa dropdown, ilagay ang purpose, at i-click ang Submit.";
            else if (question.includes('claim')) answer = "Kapag Ready to Claim na ang status, pumunta sa SSO/Registrar area at ipakita ang QR code.";
            else if (question.includes('Rejected')) answer = "Kapag rejected, basahin ang Admin Note para malaman ang kulang, then mag-submit ulit kung kailangan.";
            chatMessages.scrollTo(0, chatMessages.scrollHeight);
            setTimeout(() => {
                chatMessages.innerHTML += `<div class="bg-white p-3 rounded-lg border text-gray-700 shadow-sm self-start w-5/6"><i class="fa-solid fa-robot mr-1 text-red-900"></i> ${answer}</div>`;
                chatMessages.scrollTo(0, chatMessages.scrollHeight);
            }, 500);
        }
    </script>
</body>
</html>
