@extends('layouts.app')

@section('template_title')
    System Overview
@endsection

@section('template_fastload_css')
    .system-dashboard {
        --ink: #182620;
        --muted: #65736d;
        --line: #dce4dc;
        --paper: #f4f7f1;
        --accent: #b5e46e;
        --teal: #278f84;
        --coral: #e47b60;
        color: var(--ink);
        padding: 12px 0 42px;
    }
    .system-dashboard * { box-sizing: border-box; }
    .system-heading { align-items: flex-end; display: flex; gap: 24px; justify-content: space-between; margin: 0 auto 22px; max-width: 1320px; }
    .system-eyebrow { color: var(--teal); font-size: 11px; font-weight: 800; margin: 0 0 8px; text-transform: uppercase; }
    .system-heading h1 { font-size: 32px; font-weight: 750; line-height: 1.1; margin: 0; }
    .system-heading p { color: var(--muted); font-size: 14px; margin: 8px 0 0; }
    .system-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 14px; }
    .system-live { align-items: center; color: var(--muted); display: inline-flex; font-size: 12px; font-weight: 700; gap: 8px; white-space: nowrap; }
    .system-live-dot { background: var(--teal); border-radius: 50%; box-shadow: 0 0 0 4px rgba(39,143,132,.13); height: 8px; width: 8px; }
    .system-refresh { background: var(--ink); border: 0; border-radius: 4px; color: #fff; font-size: 13px; font-weight: 700; padding: 10px 14px; }
    .system-refresh:hover { background: #294237; color: #fff; }
    .system-identity { align-items: center; background: var(--ink); border-radius: 6px; color: #fff; display: grid; gap: 22px; grid-template-columns: auto minmax(0, 1fr) repeat(3, minmax(100px, .3fr)); margin: 0 auto 16px; max-width: 1320px; padding: 22px 24px; }
    .system-mark { align-items: center; background: var(--accent); border-radius: 4px; color: var(--ink); display: flex; font-size: 12px; font-weight: 900; height: 48px; justify-content: center; width: 64px; }
    .system-name { font-size: 20px; font-weight: 700; line-height: 1.2; }
    .system-subtitle { color: #b6c3bb; font-size: 12px; margin-top: 6px; }
    .system-id-meta { border-left: 1px solid #405149; padding-left: 18px; }
    .system-id-label { color: #9eada4; display: block; font-size: 10px; font-weight: 700; margin-bottom: 5px; text-transform: uppercase; }
    .system-id-value { font-size: 13px; font-weight: 650; overflow-wrap: anywhere; }
    .system-metrics { display: grid; gap: 16px; grid-template-columns: repeat(3, minmax(0, 1fr)); margin: 0 auto 16px; max-width: 1320px; }
    .metric-panel { background: #fff; border: 1px solid var(--line); border-radius: 6px; min-height: 194px; padding: 20px; }
    .metric-top { align-items: center; display: flex; justify-content: space-between; }
    .metric-label { color: var(--muted); font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
    .metric-unit { color: var(--muted); font-size: 12px; }
    .metric-reading { align-items: baseline; display: flex; gap: 5px; margin: 22px 0 4px; }
    .metric-value { font-size: 38px; font-variant-numeric: tabular-nums; font-weight: 750; letter-spacing: 0; line-height: 1; }
    .metric-suffix { color: var(--muted); font-size: 15px; font-weight: 650; }
    .metric-detail { color: var(--muted); font-size: 12px; min-height: 18px; }
    .metric-track { background: #e8eee7; border-radius: 99px; height: 8px; margin-top: 19px; overflow: hidden; }
    .metric-fill { background: var(--teal); border-radius: inherit; height: 100%; transition: width .55s ease; width: 0; }
    .metric-fill.cpu { background: var(--accent); }
    .metric-fill.storage { background: var(--coral); }
    .system-lower { display: grid; gap: 16px; grid-template-columns: minmax(0, 1.25fr) minmax(280px, .75fr); margin: 0 auto; max-width: 1320px; }
    .system-log-panel, .system-detail-panel { border: 1px solid var(--line); border-radius: 6px; overflow: hidden; }
    .system-log-head { align-items: center; background: #fff; display: flex; justify-content: space-between; padding: 16px 18px; }
    .system-log-title { font-size: 15px; font-weight: 750; margin: 0; }
    .system-log-meta { color: var(--muted); font-size: 11px; }
    .system-terminal { background: #17231e; color: #d8e5dc; height: 286px; overflow: auto; padding: 14px 16px; }
    .system-log-line { border-bottom: 1px solid rgba(255,255,255,.055); font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 11px; line-height: 1.55; overflow-wrap: anywhere; padding: 5px 0; white-space: pre-wrap; }
    .system-log-line.error { color: #ff9f84; }
    .system-log-line.warning { color: #f0d17d; }
    .system-log-line.info { color: #90d8c9; }
    .system-log-empty { color: #9aaba1; font-size: 12px; padding: 30px 0; text-align: center; }
    .system-detail-panel { background: #fff; padding: 18px; }
    .system-detail-panel h2 { font-size: 15px; font-weight: 750; margin: 0 0 14px; }
    .system-detail-row { align-items: flex-start; border-top: 1px solid #edf1ec; display: flex; gap: 16px; justify-content: space-between; padding: 12px 0; }
    .system-detail-row span { color: var(--muted); font-size: 12px; }
    .system-detail-row strong { font-size: 12px; font-weight: 700; max-width: 65%; text-align: right; overflow-wrap: anywhere; }
    @media (max-width: 900px) {
        .system-identity { grid-template-columns: auto minmax(0, 1fr) repeat(2, minmax(90px, .4fr)); }
        .system-identity .system-id-meta:last-child { grid-column: 2 / -1; }
        .system-lower { grid-template-columns: 1fr; }
    }
    @media (max-width: 650px) {
        .system-dashboard { padding-top: 0; }
        .system-heading { align-items: flex-start; flex-direction: column; gap: 14px; }
        .system-heading h1 { font-size: 27px; }
        .system-identity { gap: 14px; grid-template-columns: 52px minmax(0, 1fr); padding: 18px; }
        .system-mark { height: 42px; width: 52px; }
        .system-identity .system-id-meta { border-left: 0; border-top: 1px solid #405149; grid-column: 1 / -1; padding: 12px 0 0; }
        .system-identity .system-id-meta:last-child { grid-column: 1 / -1; }
        .system-metrics { grid-template-columns: 1fr; }
        .metric-panel { min-height: 164px; }
        .system-actions { justify-content: space-between; width: 100%; }
    }
@endsection

@section('content')
    <div class="container-fluid system-dashboard" data-metrics-url="{{ route('admin.system.metrics') }}" data-logs-url="{{ route('admin.system.logs') }}">
        <header class="system-heading">
            <div>
                <p class="system-eyebrow">Operations / Admin</p>
                <h1>System overview</h1>
                <p>Live runtime health and application events.</p>
            </div>
            <div class="system-actions">
                <span class="system-live"><span class="system-live-dot"></span><span id="system-refresh-state">Connecting</span></span>
                <button class="system-refresh" id="system-refresh" type="button" title="Refresh system data">
                    <i class="fa fa-refresh" aria-hidden="true"></i> Refresh
                </button>
            </div>
        </header>

        <section class="system-identity" aria-label="Operating system">
            <div class="system-mark">LINUX</div>
            <div>
                <div class="system-name" id="system-name">Reading system...</div>
                <div class="system-subtitle" id="system-runtime">Runtime details loading</div>
            </div>
            <div class="system-id-meta">
                <span class="system-id-label">Kernel</span>
                <span class="system-id-value" id="system-kernel">--</span>
            </div>
            <div class="system-id-meta">
                <span class="system-id-label">Architecture</span>
                <span class="system-id-value" id="system-architecture">--</span>
            </div>
            <div class="system-id-meta">
                <span class="system-id-label">CPU allocation</span>
                <span class="system-id-value" id="system-cores">--</span>
            </div>
        </section>

        <section class="system-metrics" aria-label="Live system metrics">
            <article class="metric-panel">
                <div class="metric-top"><span class="metric-label">CPU usage</span><span class="metric-unit" id="cpu-load">1m load --</span></div>
                <div class="metric-reading"><span class="metric-value" id="cpu-value">--</span><span class="metric-suffix">%</span></div>
                <div class="metric-detail">Container allocation · updates every 5 seconds</div>
                <div class="metric-track" role="meter" aria-label="CPU usage" aria-valuemin="0" aria-valuemax="100"><div class="metric-fill cpu" id="cpu-bar"></div></div>
            </article>
            <article class="metric-panel">
                <div class="metric-top"><span class="metric-label">Memory</span><span class="metric-unit" id="memory-total">-- available</span></div>
                <div class="metric-reading"><span class="metric-value" id="memory-value">--</span><span class="metric-suffix">%</span></div>
                <div class="metric-detail" id="memory-detail">Container memory usage</div>
                <div class="metric-track" role="meter" aria-label="Memory usage" aria-valuemin="0" aria-valuemax="100"><div class="metric-fill" id="memory-bar"></div></div>
            </article>
            <article class="metric-panel">
                <div class="metric-top"><span class="metric-label">App filesystem</span><span class="metric-unit">Container view</span></div>
                <div class="metric-reading"><span class="metric-value" id="storage-value">--</span><span class="metric-suffix">%</span></div>
                <div class="metric-detail" id="storage-detail">Root filesystem usage</div>
                <div class="metric-track" role="meter" aria-label="Filesystem usage" aria-valuemin="0" aria-valuemax="100"><div class="metric-fill storage" id="storage-bar"></div></div>
            </article>
        </section>

        <section class="system-lower">
            <div class="system-log-panel">
                <div class="system-log-head">
                    <div>
                        <h2 class="system-log-title">Application log</h2>
                        <span class="system-log-meta" id="logs-updated">Waiting for log stream</span>
                    </div>
                    <span class="system-live"><span class="system-live-dot"></span>LIVE</span>
                </div>
                <div class="system-terminal" id="system-log-lines" role="log" aria-live="polite" aria-relevant="additions text">
                    <div class="system-log-empty">Connecting to laravel.log...</div>
                </div>
            </div>
            <aside class="system-detail-panel">
                <h2>Runtime details</h2>
                <div class="system-detail-row"><span>Platform</span><strong id="detail-platform">Linux container</strong></div>
                <div class="system-detail-row"><span>Kernel release</span><strong id="detail-kernel">--</strong></div>
                <div class="system-detail-row"><span>CPU load</span><strong id="detail-load">--</strong></div>
                <div class="system-detail-row"><span>Last updated</span><strong id="detail-updated">--</strong></div>
            </aside>
        </section>
    </div>
@endsection

@section('footer_scripts')
    <script>
        (function () {
            var dashboard = document.querySelector('.system-dashboard');
            if (!dashboard) return;

            var metricsUrl = dashboard.dataset.metricsUrl;
            var logsUrl = dashboard.dataset.logsUrl;
            var logContainer = document.getElementById('system-log-lines');
            var lastLogContent = '';
            var requestPending = false;

            function setText(id, value) {
                var element = document.getElementById(id);
                if (element) element.textContent = value;
            }

            function formatBytes(bytes) {
                if (!Number.isFinite(Number(bytes))) return '--';
                var units = ['B', 'KB', 'MB', 'GB', 'TB'];
                var value = Number(bytes);
                var unit = 0;
                while (value >= 1024 && unit < units.length - 1) {
                    value /= 1024;
                    unit++;
                }
                return value.toFixed(unit === 0 ? 0 : 1) + ' ' + units[unit];
            }

            function setMeter(valueId, barId, value, detailId, detail) {
                var number = Number(value);
                var percent = Number.isFinite(number) ? Math.max(0, Math.min(100, number)) : null;
                setText(valueId, percent === null ? '--' : percent.toFixed(1));
                var bar = document.getElementById(barId);
                if (bar) {
                    bar.style.width = (percent === null ? 0 : percent) + '%';
                    bar.parentElement.setAttribute('aria-valuenow', percent === null ? '0' : percent.toFixed(1));
                }
                if (detailId) setText(detailId, detail);
            }

            async function refreshMetrics() {
                if (requestPending) return;
                requestPending = true;
                try {
                    var response = await fetch(metricsUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
                    if (!response.ok) throw new Error('Metrics unavailable');
                    var data = await response.json();
                    setText('system-name', data.system.name || 'Linux');
                    setText('system-runtime', data.system.runtime + ' · ' + data.system.architecture);
                    setText('system-kernel', data.system.kernel || '--');
                    setText('system-architecture', data.system.architecture || '--');
                    setText('system-cores', data.cpu.cores + (Number(data.cpu.cores) === 1 ? ' core' : ' cores'));
                    setText('cpu-load', '1m load ' + (data.cpu.load_1m === null ? '--' : data.cpu.load_1m));
                    setText('detail-load', data.cpu.load_1m === null ? '--' : data.cpu.load_1m + ' (1 minute)');
                    setText('memory-total', formatBytes(data.memory.total_bytes) + ' total');
                    setText('storage-detail', formatBytes(data.storage.used_bytes) + ' used of ' + formatBytes(data.storage.total_bytes));
                    setMeter('cpu-value', 'cpu-bar', data.cpu.usage_percent, null, '');
                    if (data.cpu.usage_percent === null) setText('cpu-value', '...');
                    setMeter('memory-value', 'memory-bar', data.memory.percent, 'memory-detail', formatBytes(data.memory.used_bytes) + ' used');
                    setMeter('storage-value', 'storage-bar', data.storage.percent, null, '');
                    var updated = new Date(data.updated_at).toLocaleTimeString();
                    setText('detail-updated', updated);
                    setText('system-refresh-state', 'Live · ' + updated);
                } catch (error) {
                    setText('system-refresh-state', 'Metrics unavailable');
                } finally {
                    requestPending = false;
                }
            }

            async function refreshLogs() {
                try {
                    var response = await fetch(logsUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
                    if (!response.ok) throw new Error('Logs unavailable');
                    var data = await response.json();
                    var content = data.lines.join('\n');
                    var shouldScroll = logContainer.scrollTop + logContainer.clientHeight >= logContainer.scrollHeight - 20;
                    if (content !== lastLogContent) {
                        logContainer.replaceChildren();
                        if (data.lines.length === 0) {
                            var empty = document.createElement('div');
                            empty.className = 'system-log-empty';
                            empty.textContent = 'No application log entries yet.';
                            logContainer.appendChild(empty);
                        } else {
                            data.lines.forEach(function (text) {
                                var line = document.createElement('div');
                                line.className = 'system-log-line';
                                if (/\b(error|critical|alert|emergency)\b/i.test(text)) line.classList.add('error');
                                else if (/\b(warning|notice)\b/i.test(text)) line.classList.add('warning');
                                else if (/\b(info)\b/i.test(text)) line.classList.add('info');
                                line.textContent = text;
                                logContainer.appendChild(line);
                            });
                        }
                        lastLogContent = content;
                        if (shouldScroll) logContainer.scrollTop = logContainer.scrollHeight;
                    }
                    setText('logs-updated', 'Updated ' + new Date(data.updated_at).toLocaleTimeString());
                } catch (error) {
                    setText('logs-updated', 'Log stream unavailable');
                }
            }

            function refreshAll() {
                refreshMetrics();
                refreshLogs();
            }

            document.getElementById('system-refresh').addEventListener('click', refreshAll);
            refreshAll();
            window.setInterval(refreshMetrics, 5000);
            window.setInterval(refreshLogs, 2500);
        })();
    </script>
@endsection
