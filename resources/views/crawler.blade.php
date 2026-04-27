<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Web Crawler — cmlabs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'spin-slow': 'spin 1s linear infinite',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen text-gray-900 antialiased">

    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
            </div>
            <div>
                <h1 class="text-base font-semibold leading-tight">Web Crawler</h1>
                <p class="text-xs text-gray-500">Fetch & save HTML from any URL</p>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 space-y-6">

        <!-- Crawl Form Card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="font-semibold text-sm text-gray-900">Crawl Website</h2>
                <p class="text-xs text-gray-500 mt-0.5">Enter a URL to fetch its HTML content</p>
            </div>
            <div class="px-6 py-5 space-y-4">

                <!-- URL Input -->
                <div>
                    <label for="url" class="block text-xs font-medium text-gray-700 mb-1.5">Website URL</label>
                    <input id="url" type="url" placeholder="https://example.com"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition placeholder-gray-400" />
                    <p id="url-error" class="text-xs text-red-500 mt-1 hidden"></p>
                </div>

                <!-- Mode Selection -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-2">Mode</label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="mode" value="crawl" checked
                                class="text-blue-600 focus:ring-blue-500 border-gray-300"
                                onchange="toggleFilename(this.value)">
                            <span class="text-sm text-gray-700">Crawl only</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="mode" value="save"
                                class="text-blue-600 focus:ring-blue-500 border-gray-300"
                                onchange="toggleFilename(this.value)">
                            <span class="text-sm text-gray-700">Crawl &amp; Save</span>
                        </label>
                    </div>
                </div>

                <!-- Filename (only visible for save mode) -->
                <div id="filename-wrapper" class="hidden">
                    <label for="filename" class="block text-xs font-medium text-gray-700 mb-1.5">
                        Filename <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input id="filename" type="text" placeholder="my-page.html"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition placeholder-gray-400" />
                    <p class="text-xs text-gray-400 mt-1">Leave blank for auto-generated name</p>
                </div>

                <!-- Submit Button -->
                <button id="crawl-btn" onclick="startCrawl()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg id="btn-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <svg id="btn-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span id="btn-text">Crawl</span>
                </button>
            </div>
        </div>

        <!-- Result Panel -->
        <div id="result-panel" class="hidden">
            <!-- Success -->
            <div id="result-success" class="hidden bg-white rounded-xl border border-green-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-green-50 border-b border-green-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium text-green-800">Crawled successfully</span>
                </div>
                <div class="px-6 py-4 grid grid-cols-2 sm:grid-cols-3 gap-4" id="result-meta"></div>
                <div id="result-actions" class="hidden px-6 pb-5 flex gap-2 border-t border-green-100 pt-4"></div>
            </div>

            <!-- Error -->
            <div id="result-error" class="hidden bg-white rounded-xl border border-red-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-red-50 border-b border-red-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium text-red-800">Crawl failed</span>
                </div>
                <div class="px-6 py-4">
                    <p id="result-error-msg" class="text-sm text-red-600 font-mono break-all"></p>
                </div>
            </div>
        </div>

        <!-- Files List Card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-sm text-gray-900">Saved Files</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Previously crawled HTML files</p>
                </div>
                <button onclick="loadFiles()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <svg id="refresh-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
            <div id="files-container">
                <!-- Loading state -->
                <div id="files-loading" class="px-6 py-8 flex flex-col items-center text-gray-400">
                    <svg class="w-5 h-5 animate-spin mb-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span class="text-sm">Loading files…</span>
                </div>
                <!-- Empty state -->
                <div id="files-empty" class="hidden px-6 py-10 flex flex-col items-center text-gray-400">
                    <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm">No saved files yet</span>
                    <span class="text-xs mt-0.5">Use "Crawl &amp; Save" to store HTML files</span>
                </div>
                <!-- File list -->
                <div id="files-list" class="hidden divide-y divide-gray-100"></div>
            </div>
        </div>

    </main>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        function toggleFilename(mode) {
            const wrapper = document.getElementById('filename-wrapper');
            if (mode === 'save') {
                wrapper.classList.remove('hidden');
            } else {
                wrapper.classList.add('hidden');
            }
        }

        function setLoading(on) {
            const btn = document.getElementById('crawl-btn');
            const icon = document.getElementById('btn-icon');
            const spinner = document.getElementById('btn-spinner');
            const text = document.getElementById('btn-text');
            btn.disabled = on;
            icon.classList.toggle('hidden', on);
            spinner.classList.toggle('hidden', !on);
            text.textContent = on ? 'Crawling…' : 'Crawl';
        }

        function showResult(type, data, mode) {
            const panel = document.getElementById('result-panel');
            const success = document.getElementById('result-success');
            const error = document.getElementById('result-error');
            panel.classList.remove('hidden');

            if (type === 'success') {
                success.classList.remove('hidden');
                error.classList.add('hidden');
                const meta = document.getElementById('result-meta');

                const items = [];
                if (data.page_title) items.push({ label: 'Page Title', value: data.page_title, full: true });
                items.push({ label: 'URL', value: data.url, full: true });
                items.push({ label: 'HTML Size', value: data.html_length ? formatBytes(data.html_length) : '—' });
                if (data.links_count !== undefined) items.push({ label: 'Links Found', value: data.links_count });
                if (data.file_name) items.push({ label: 'Saved As', value: data.file_name });

                meta.innerHTML = items.map(i => `
                    <div class="${i.full ? 'col-span-2 sm:col-span-3' : ''}">
                        <p class="text-xs text-gray-500 mb-0.5">${i.label}</p>
                        <p class="text-sm font-medium text-gray-900 truncate" title="${escHtml(String(i.value))}">${escHtml(String(i.value))}</p>
                    </div>
                `).join('');

                if (data.file_name) {
                    const actions = document.getElementById('result-actions');
                    if (actions) {
                        actions.innerHTML = `
                            <a href="/api/crawler/view/${encodeURIComponent(data.file_name)}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                               View saved file
                            </a>
                            <a href="/api/crawler/download/${encodeURIComponent(data.file_name)}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                               Download
                            </a>
                        `;
                        actions.classList.remove('hidden');
                    }
                }
            } else {
                error.classList.remove('hidden');
                success.classList.add('hidden');
                document.getElementById('result-error-msg').textContent = data.error || 'Unknown error occurred.';
            }
        }

        function escHtml(str) {
            return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        async function startCrawl() {
            const urlInput = document.getElementById('url');
            const url = urlInput.value.trim();
            const errEl = document.getElementById('url-error');

            errEl.classList.add('hidden');

            if (!url) {
                errEl.textContent = 'Please enter a URL.';
                errEl.classList.remove('hidden');
                urlInput.focus();
                return;
            }

            try { new URL(url); } catch {
                errEl.textContent = 'Please enter a valid URL (e.g. https://example.com).';
                errEl.classList.remove('hidden');
                urlInput.focus();
                return;
            }

            const mode = document.querySelector('input[name="mode"]:checked').value;
            const filename = document.getElementById('filename').value.trim();

            // reset result panel
            document.getElementById('result-panel').classList.add('hidden');
            document.getElementById('result-success').classList.add('hidden');
            document.getElementById('result-error').classList.add('hidden');
            const actEl = document.getElementById('result-actions');
            if (actEl) { actEl.classList.add('hidden'); actEl.innerHTML = ''; }

            setLoading(true);

            try {
                const endpoint = mode === 'save' ? '/api/crawler/crawl-and-save' : '/api/crawler/crawl';
                const body = mode === 'save'
                    ? { url, filename: filename || undefined }
                    : { url };

                const resp = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify(body),
                });

                let data;
                const contentType = resp.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    data = await resp.json();
                } else {
                    const text = await resp.text();
                    data = { success: false, error: `HTTP ${resp.status}: Server returned non-JSON response. ${text.slice(0, 300)}` };
                }

                if (data.success) {
                    showResult('success', data, mode);
                    if (mode === 'save') loadFiles();
                } else {
                    showResult('error', { error: data.error || data.message || `HTTP ${resp.status} error` });
                }
            } catch (e) {
                showResult('error', { error: e.message || 'Network error — is the server running?' });
            } finally {
                setLoading(false);
            }
        }

        async function loadFiles() {
            const loading = document.getElementById('files-loading');
            const empty = document.getElementById('files-empty');
            const list = document.getElementById('files-list');
            const refreshIcon = document.getElementById('refresh-icon');

            loading.classList.remove('hidden');
            empty.classList.add('hidden');
            list.classList.add('hidden');
            refreshIcon.classList.add('animate-spin');

            try {
                const resp = await fetch('/api/crawler/files', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await resp.json();

                if (!data.success || data.files.length === 0) {
                    empty.classList.remove('hidden');
                } else {
                    list.innerHTML = data.files.map(f => `
                        <div id="file-row-${CSS.escape(f.name)}" class="px-6 py-3.5 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">${escHtml(f.name)}</p>
                                    <p class="text-xs text-gray-400">${f.size_mb} MB &middot; ${f.created_at}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="/api/crawler/view/${encodeURIComponent(f.name)}" target="_blank"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    View
                                </a>
                                <a href="/api/crawler/download/${encodeURIComponent(f.name)}"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download
                                </a>
                                <button onclick="deleteFile(this, '${escHtml(f.name).replace(/'/g, "\\'")}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    `).join('');
                    list.classList.remove('hidden');
                }
            } catch {
                empty.classList.remove('hidden');
            } finally {
                loading.classList.add('hidden');
                refreshIcon.classList.remove('animate-spin');
            }
        }

        async function deleteFile(btn, filename) {
            if (!confirm(`Hapus file "${filename}"?`)) return;

            btn.disabled = true;
            btn.textContent = 'Menghapus…';

            try {
                const resp = await fetch(`/api/crawler/files/${encodeURIComponent(filename)}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                });
                const data = await resp.json();
                if (data.success) {
                    const row = document.getElementById(`file-row-${CSS.escape(filename)}`);
                    if (row) {
                        row.style.transition = 'opacity 0.2s';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            const list = document.getElementById('files-list');
                            if (list && !list.children.length) {
                                list.classList.add('hidden');
                                document.getElementById('files-empty').classList.remove('hidden');
                            }
                        }, 200);
                    }
                } else {
                    alert(data.error || 'Gagal menghapus file.');
                    btn.disabled = false;
                    btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg> Hapus`;
                }
            } catch (e) {
                alert('Gagal menghapus: ' + e.message);
                btn.disabled = false;
                btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg> Hapus`;
            }
        }

        function formatBytes(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1024 / 1024).toFixed(2) + ' MB';
        }

        // Allow Enter key on URL input
        document.getElementById('url').addEventListener('keydown', e => {
            if (e.key === 'Enter') startCrawl();
        });

        // Load files on page load
        loadFiles();
    </script>
</body>
</html>
