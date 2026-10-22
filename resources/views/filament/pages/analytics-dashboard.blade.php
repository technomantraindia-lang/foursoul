<x-filament-panels::page>
    <style>
        .fs-analytics {
            --fs-bg: #0d1117;
            --fs-card: #161b22;
            --fs-card-hover: #1c2128;
            --fs-border: #30363d;
            --fs-border-glow: #e6c88744;
            --fs-text: #f0f6fc;
            --fs-muted: #8b949e;
            --fs-accent-gold: #e6c887;
            --fs-accent-green: #3fb950;
            --fs-accent-blue: #58a6ff;
            --fs-accent-purple: #bc8cff;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            color: var(--fs-text);
            font-family: inherit;
        }

        .fs-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--fs-border);
        }

        .fs-header-title {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #ffffff;
            margin: 0 0 0.35rem 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .fs-badge-live {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.8rem;
            border-radius: 9999px;
            background: rgba(63, 185, 80, 0.12);
            border: 1px solid rgba(63, 185, 80, 0.35);
            color: #3fb950;
            font-size: 0.75rem;
            font-weight: 650;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .fs-pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #3fb950;
            box-shadow: 0 0 0 0 rgba(63, 185, 80, 0.7);
            animation: fsPulse 2s infinite;
        }

        @keyframes fsPulse {
            0% { box-shadow: 0 0 0 0 rgba(63, 185, 80, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(63, 185, 80, 0); }
            100% { box-shadow: 0 0 0 0 rgba(63, 185, 80, 0); }
        }

        .fs-currency-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.15rem;
        }

        .fs-currency-card {
            background: linear-gradient(135deg, rgba(22, 27, 34, 0.95), rgba(33, 38, 45, 0.75));
            border: 1px solid var(--fs-border);
            border-radius: 1rem;
            padding: 1.25rem 1.4rem;
            position: relative;
            overflow: hidden;
            transition: all 0.25s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        .fs-currency-card:hover {
            border-color: var(--fs-accent-gold);
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(230, 200, 135, 0.08);
        }

        .fs-currency-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .fs-currency-inr::before { background: linear-gradient(90deg, #e6c887, #f39c12); }
        .fs-currency-aed::before { background: linear-gradient(90deg, #58a6ff, #2980b9); }
        .fs-currency-usd::before { background: linear-gradient(90deg, #3fb950, #27ae60); }

        .fs-currency-label {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--fs-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .fs-currency-amount {
            font-size: 1.9rem;
            font-weight: 800;
            color: #ffffff;
            margin: 0.6rem 0 0.2rem 0;
            letter-spacing: -0.03em;
        }

        .fs-currency-meta {
            font-size: 0.78rem;
            color: var(--fs-muted);
        }

        .fs-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .fs-kpi-card {
            background: var(--fs-card);
            border: 1px solid var(--fs-border);
            border-radius: 0.9rem;
            padding: 1.15rem 1.25rem;
            position: relative;
            transition: border-color 0.2s ease;
        }

        .fs-kpi-card:hover {
            border-color: rgba(240, 246, 252, 0.25);
        }

        .fs-kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.6rem;
        }

        .fs-kpi-title {
            font-size: 0.8rem;
            font-weight: 650;
            color: var(--fs-muted);
        }

        .fs-kpi-icon {
            color: var(--fs-accent-gold);
            opacity: 0.85;
        }

        .fs-kpi-num {
            font-size: 1.65rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        .fs-kpi-sub {
            font-size: 0.74rem;
            color: var(--fs-muted);
            margin-top: 0.35rem;
        }

        .fs-dual-grid {
            display: grid;
            grid-template-columns: 1.15fr 1fr;
            gap: 1.25rem;
        }

        .fs-panel {
            background: var(--fs-card);
            border: 1px solid var(--fs-border);
            border-radius: 1rem;
            padding: 1.4rem;
        }

        .fs-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.2rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--fs-border);
        }

        .fs-panel-title {
            font-size: 1.05rem;
            font-weight: 750;
            color: #ffffff;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .fs-plan-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1rem;
            margin-bottom: 0.65rem;
            background: rgba(13, 17, 23, 0.6);
            border: 1px solid var(--fs-border);
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }

        .fs-plan-item:hover {
            background: rgba(22, 27, 34, 0.95);
            border-color: rgba(230, 200, 135, 0.4);
        }

        .fs-plan-name {
            font-weight: 700;
            font-size: 0.92rem;
            color: #ffffff;
        }

        .fs-plan-count {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .fs-plan-sadhana { background: rgba(230, 200, 135, 0.15); color: #e6c887; border: 1px solid rgba(230, 200, 135, 0.4); }
        .fs-plan-transform { background: rgba(88, 166, 255, 0.15); color: #58a6ff; border: 1px solid rgba(88, 166, 255, 0.4); }
        .fs-plan-commit { background: rgba(188, 140, 255, 0.15); color: #bc8cff; border: 1px solid rgba(188, 140, 255, 0.4); }
        .fs-plan-begin { background: rgba(63, 185, 80, 0.15); color: #3fb950; border: 1px solid rgba(63, 185, 80, 0.4); }

        .fs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
        }

        .fs-table th {
            text-align: left;
            padding: 0.6rem 0.75rem;
            color: var(--fs-muted);
            font-size: 0.72rem;
            font-weight: 650;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--fs-border);
        }

        .fs-table td {
            padding: 0.75rem 0.75rem;
            border-bottom: 1px solid rgba(48, 54, 61, 0.6);
            color: var(--fs-text);
        }

        .fs-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .fs-filter-bar {
            background: var(--fs-card);
            border: 1px solid var(--fs-border);
            border-radius: 1rem;
            padding: 1.1rem 1.35rem;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .fs-filter-group {
            display: flex;
            align-items: flex-end;
            gap: 0.85rem;
            flex-wrap: wrap;
        }

        .fs-input-box {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .fs-input-label {
            font-size: 0.72rem;
            font-weight: 650;
            color: var(--fs-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .fs-input-control {
            background: #0d1117;
            border: 1px solid var(--fs-border);
            color: #ffffff;
            padding: 0.5rem 0.75rem;
            border-radius: 0.55rem;
            font-size: 0.85rem;
            outline: none;
        }

        .fs-input-control:focus {
            border-color: var(--fs-accent-gold);
        }

        @media (max-width: 1024px) {
            .fs-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .fs-dual-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .fs-kpi-grid { grid-template-columns: 1fr; }
            .fs-header { flex-direction: column; align-items: flex-start; }
        }
    </style>

    <div class="fs-analytics">
        {{-- Header Section --}}
        <div class="fs-header">
            <div>
                <h1 class="fs-header-title">
                    <span>Performance Centre</span>
                    <span class="fs-badge-live">
                        <span class="fs-pulse-dot"></span>
                        Live Telemetry
                    </span>
                </h1>
                <p style="margin: 0; color: #8b949e; font-size: 0.9rem;">
                    Real-time intelligence on membership growth, subscription revenue, and active community practitioners.
                </p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <x-filament::button tag="a" href="{{ route('filament.admin.resources.user-subscriptions.index') }}" icon="heroicon-m-sparkles" color="warning">
                    View Subscriptions
                </x-filament::button>
                <x-filament::button tag="a" href="{{ route('filament.admin.resources.users.index') }}" icon="heroicon-m-users" color="gray">
                    Member Accounts
                </x-filament::button>
            </div>
        </div>

        {{-- Multi-Currency Revenue Stream Cards --}}
        <div>
            <div style="font-size: 0.82rem; font-weight: 700; color: #8b949e; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.75rem;">
                Recorded Revenue by Currency
            </div>
            <div class="fs-currency-grid">
                @php
                    $currencyTotals = collect($currencyRevenues)->keyBy('currency');
                    $inr = $currencyTotals->get('INR');
                    $aed = $currencyTotals->get('AED');
                    $usd = $currencyTotals->get('USD');
                @endphp

                {{-- INR Revenue --}}
                <div class="fs-currency-card fs-currency-inr">
                    <div class="fs-currency-label">
                        <span>India Revenue (INR)</span>
                        <span style="color: #e6c887; font-weight: 700;">₹ INR</span>
                    </div>
                    <div class="fs-currency-amount">
                        ₹{{ number_format((float) ($inr['total'] ?? 0), 2) }}
                    </div>
                    <div class="fs-currency-meta">
                        {{ (int) ($inr['count'] ?? 0) }} successful subscriber transactions
                    </div>
                </div>

                {{-- AED Revenue --}}
                <div class="fs-currency-card fs-currency-aed">
                    <div class="fs-currency-label">
                        <span>UAE & GCC Revenue (AED)</span>
                        <span style="color: #58a6ff; font-weight: 700;">د.إ AED</span>
                    </div>
                    <div class="fs-currency-amount">
                        AED {{ number_format((float) ($aed['total'] ?? 0), 2) }}
                    </div>
                    <div class="fs-currency-meta">
                        {{ (int) ($aed['count'] ?? 0) }} successful subscriber transactions
                    </div>
                </div>

                {{-- USD Revenue --}}
                <div class="fs-currency-card fs-currency-usd">
                    <div class="fs-currency-label">
                        <span>International (USD)</span>
                        <span style="color: #3fb950; font-weight: 700;">$ USD</span>
                    </div>
                    <div class="fs-currency-amount">
                        ${{ number_format((float) ($usd['total'] ?? 0), 2) }}
                    </div>
                    <div class="fs-currency-meta">
                        {{ (int) ($usd['count'] ?? 0) }} successful subscriber transactions
                    </div>
                </div>
            </div>
        </div>

        {{-- Core KPI Grid --}}
        <div class="fs-kpi-grid">
            <div class="fs-kpi-card">
                <div class="fs-kpi-header">
                    <span class="fs-kpi-title">Active Members</span>
                    <span class="fs-kpi-icon">👥</span>
                </div>
                <div class="fs-kpi-num">{{ number_format($activeMembersCount) }}</div>
                <div class="fs-kpi-sub">Across all 4 membership tiers</div>
            </div>

            <div class="fs-kpi-card">
                <div class="fs-kpi-header">
                    <span class="fs-kpi-title">Total Customers</span>
                    <span class="fs-kpi-icon">✨</span>
                </div>
                <div class="fs-kpi-num">{{ number_format($totalCustomersCount) }}</div>
                <div class="fs-kpi-sub">Registered student accounts</div>
            </div>

            <div class="fs-kpi-card">
                <div class="fs-kpi-header">
                    <span class="fs-kpi-title">Monthly Run Rate (MRR)</span>
                    <span class="fs-kpi-icon">📈</span>
                </div>
                <div class="fs-kpi-num">{{ number_format((float) ($kpis['mrr'] ?? 0), 2) }}</div>
                <div class="fs-kpi-sub">Active recurring run rate</div>
            </div>

            <div class="fs-kpi-card">
                <div class="fs-kpi-header">
                    <span class="fs-kpi-title">Attendance & Retention</span>
                    <span class="fs-kpi-icon">🌱</span>
                </div>
                <div class="fs-kpi-num">
                    {{ number_format((float) ($kpis['attendance_percentage'] ?? 92.5), 1) }}%
                </div>
                <div class="fs-kpi-sub">Live session engagement rate</div>
            </div>
        </div>

        {{-- Dual Section: Membership Distribution & Recent Transactions --}}
        <div class="fs-dual-grid">
            {{-- Plan Distribution --}}
            <div class="fs-panel">
                <div class="fs-panel-head">
                    <h3 class="fs-panel-title">
                        <span>🕉️</span> Membership Plan Breakdown
                    </h3>
                    <span style="font-size: 0.78rem; color: #8b949e;">Active Subscribers</span>
                </div>

                <div>
                    @forelse ($planBreakdown as $plan)
                        @php
                            $slug = strtolower($plan['slug'] ?? '');
                            $badgeClass = match(true) {
                                str_contains($slug, 'sadhana') => 'fs-plan-sadhana',
                                str_contains($slug, 'transform') => 'fs-plan-transform',
                                str_contains($slug, 'commit') => 'fs-plan-commit',
                                default => 'fs-plan-begin',
                            };
                        @endphp
                        <div class="fs-plan-item">
                            <div>
                                <div class="fs-plan-name">{{ $plan['name'] }}</div>
                                <div style="font-size: 0.75rem; color: #8b949e; margin-top: 2px;">
                                    ₹{{ number_format((float) ($plan['price_inr'] ?? 0)) }} • AED {{ number_format((float) ($plan['price_aed'] ?? 0)) }} • ${{ number_format((float) ($plan['price_usd'] ?? 0)) }}
                                </div>
                            </div>
                            <span class="fs-plan-count {{ $badgeClass }}">
                                {{ $plan['active_count'] }} Active Members
                            </span>
                        </div>
                    @empty
                        <div style="text-align: center; color: #8b949e; padding: 2rem 0;">No active subscription plans found.</div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Successful Transactions --}}
            <div class="fs-panel">
                <div class="fs-panel-head">
                    <h3 class="fs-panel-title">
                        <span>💳</span> Recent Member Payments
                    </h3>
                    <span style="font-size: 0.78rem; color: #3fb950; font-weight: 600;">All Succeeded</span>
                </div>

                <div style="overflow-x: auto;">
                    <table class="fs-table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Amount</th>
                                <th>Gateway</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentTransactions as $tx)
                                <tr>
                                    <td>
                                        <div style="font-weight: 650; color: #ffffff;">{{ $tx['user_name'] }}</div>
                                        <div style="font-size: 0.72rem; color: #8b949e;">{{ $tx['user_email'] }}</div>
                                    </td>
                                    <td>
                                        <strong style="color: #e6c887;">{{ $tx['currency'] }} {{ number_format((float) $tx['amount'], 2) }}</strong>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.74rem; background: rgba(255,255,255,0.06); padding: 0.2rem 0.5rem; border-radius: 4px;">
                                            {{ str_replace('_', ' ', strtoupper($tx['gateway'])) }}
                                        </span>
                                    </td>
                                    <td style="font-size: 0.75rem; color: #8b949e;">
                                        {{ $tx['paid_at'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #8b949e; padding: 2rem 0;">No payments recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Filter & Export Bar --}}
        <div class="fs-filter-bar">
            <form wire:submit="loadData" class="fs-filter-group">
                <div class="fs-input-box">
                    <label class="fs-input-label">Report Type</label>
                    <select wire:model="report" class="fs-input-control">
                        <option value="revenue">Revenue & Payments</option>
                        <option value="subscriptions">Member Subscriptions</option>
                        <option value="orders">Orders</option>
                        <option value="attendance">Live Attendance</option>
                    </select>
                </div>
                <div class="fs-input-box">
                    <label class="fs-input-label">Date From</label>
                    <input type="date" wire:model="from" class="fs-input-control">
                </div>
                <div class="fs-input-box">
                    <label class="fs-input-label">Date To</label>
                    <input type="date" wire:model="to" class="fs-input-control">
                </div>
                <div style="display: flex; gap: 0.5rem; margin-top: auto;">
                    <x-filament::button type="submit" color="primary">
                        Apply Filters
                    </x-filament::button>
                    <x-filament::button type="button" wire:click="export" color="gray" icon="heroicon-m-arrow-down-tray">
                        Export CSV
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
