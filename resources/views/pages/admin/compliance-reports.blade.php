{{-- Port of frontend/src/views/admin/ComplianceReportsPage.tsx + ComplianceReportGenerator.tsx. Chart viewer and file export are not generated server-side. --}}
<x-layouts.admin>
<div
    class="space-y-6"
    x-data="{
        reportType: 'monthly_summary',
        dateRange: 'last_30_days',
        anonymize: true,
        includePersonal: false,
        typeOpen: false,
        exportOpen: false,
        generated: false,
        descriptions: {
            monthly_summary: 'Comprehensive monthly overview of case management performance and key metrics',
            quarterly_summary: 'Quarterly compliance report with performance trends and analytics',
            annual_summary: 'Annual compliance summary with year-over-year comparisons',
            frequency_analysis: 'Detailed analysis of case volumes, patterns, and frequency trends',
            trend_analysis: 'Historical trend analysis with predictive insights and pattern recognition',
            category_breakdown: 'Statistical breakdown of cases by category and classification',
            location_analysis: 'Geographic analysis of case distribution across locations',
            severity_analysis: 'Analysis of incident severity levels and priority distribution',
            resolution_time: 'Performance metrics for case resolution times and SLA compliance',
            handler_performance: 'Detailed analysis of staff performance, workload, and efficiency metrics'
        },
        labels: {
            monthly_summary: 'Monthly Performance Summary',
            quarterly_summary: 'Quarterly Performance Summary',
            annual_summary: 'Annual Performance Summary',
            frequency_analysis: 'Case Volume & Frequency Analysis',
            trend_analysis: 'Trend & Pattern Analysis',
            category_breakdown: 'Case Classification Report',
            location_analysis: 'Geographic Distribution Report',
            severity_analysis: 'Severity Level Distribution',
            resolution_time: 'Resolution Performance Metrics',
            handler_performance: 'Staff Performance Analysis'
        },
        ranges: {
            last_7_days: 'Last 7 Days',
            last_30_days: 'Last 30 Days (Monthly)',
            last_90_days: 'Last 90 Days (Quarterly)',
            last_6_months: 'Last 6 Months',
            last_year: 'Last 12 Months (Annual)',
            custom: 'Custom Date Range'
        },
        complete() {
            return ['monthly_summary', 'quarterly_summary', 'annual_summary'].includes(this.reportType);
        }
    }"
>
    <div class="flex items-center justify-between">
        <div>
            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500">Compliance</p>
            <h1 class="text-xl font-bold text-gray-900">Compliance Reports</h1>
            <p class="mt-1 text-sm text-gray-500">Generate privacy-compliant analytics and compliance reports</p>
        </div>
        <div class="flex items-center gap-1.5 text-xs text-green-700">
            <x-lucide-shield class="h-3.5 w-3.5" />
            <span class="font-medium">GDPR Compliant</span>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-[#1D9E75]/20">
                            <x-lucide-file-text class="h-5 w-5 text-[#1D9E75]" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold leading-none tracking-tight">Generate Compliance Report</h3>
                            <p class="text-sm text-muted-foreground">Create privacy-compliant analytics and reports</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                        <x-lucide-shield class="h-3.5 w-3.5 text-[#1D9E75]" />
                        <span class="font-medium">GDPR Compliant</span>
                    </div>
                </div>
            </div>
            <div class="space-y-6 p-6 pt-0">
                <div class="space-y-3">
                    <div>
                        <label for="report-type" class="text-base font-semibold">Report Type</label>
                        <p class="mt-1 text-sm text-gray-500" x-text="descriptions[reportType]"></p>
                    </div>
                    <div class="relative">
                        <button id="report-type" type="button" @click="typeOpen = !typeOpen" class="flex h-auto w-full items-center justify-between rounded-md border border-input bg-background px-3 py-3 text-left text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                            <span x-text="labels[reportType]"></span>
                            <x-lucide-chevron-down class="h-4 w-4 opacity-50" />
                        </button>
                        <div x-show="typeOpen" x-cloak @click.outside="typeOpen = false" class="absolute z-50 mt-1 max-h-96 w-full overflow-auto rounded-md border bg-popover p-1 text-popover-foreground shadow-md">
                            <div class="border-b px-2 py-2 text-xs font-semibold text-gray-500">Compliance & Performance Overview</div>
                            <button type="button" class="flex w-full items-start gap-3 rounded-sm px-2 py-2 text-left text-sm hover:bg-accent" @click="reportType = 'monthly_summary'; typeOpen = false"><x-lucide-file-text class="mt-0.5 h-4 w-4 text-gray-500" /><span><span class="block font-medium">Monthly Performance Summary</span><span class="mt-0.5 block text-xs text-gray-500">Comprehensive monthly overview</span></span></button>
                            <button type="button" class="flex w-full items-start gap-3 rounded-sm px-2 py-2 text-left text-sm hover:bg-accent" @click="reportType = 'quarterly_summary'; typeOpen = false"><x-lucide-file-text class="mt-0.5 h-4 w-4 text-gray-500" /><span><span class="block font-medium">Quarterly Performance Summary</span><span class="mt-0.5 block text-xs text-gray-500">90-day performance trends</span></span></button>
                            <button type="button" class="flex w-full items-start gap-3 rounded-sm px-2 py-2 text-left text-sm hover:bg-accent" @click="reportType = 'annual_summary'; typeOpen = false"><x-lucide-file-text class="mt-0.5 h-4 w-4 text-gray-500" /><span><span class="block font-medium">Annual Performance Summary</span><span class="mt-0.5 block text-xs text-gray-500">Year-over-year comparisons</span></span></button>
                            <div class="mt-2 border-b border-t px-2 py-2 text-xs font-semibold text-gray-500">Analytical & Statistical Reports</div>
                            <button type="button" class="flex w-full items-start gap-3 rounded-sm px-2 py-2 text-left text-sm hover:bg-accent" @click="reportType = 'frequency_analysis'; typeOpen = false"><x-lucide-bar-chart-3 class="mt-0.5 h-4 w-4 text-blue-600" /><span><span class="block font-medium">Case Volume & Frequency Analysis</span><span class="mt-0.5 block text-xs text-gray-500">Volume patterns & trends</span></span></button>
                            <button type="button" class="flex w-full items-start gap-3 rounded-sm px-2 py-2 text-left text-sm hover:bg-accent" @click="reportType = 'trend_analysis'; typeOpen = false"><x-lucide-trending-up class="mt-0.5 h-4 w-4 text-purple-600" /><span><span class="block font-medium">Trend & Pattern Analysis</span><span class="mt-0.5 block text-xs text-gray-500">Historical analysis & predictions</span></span></button>
                            <button type="button" class="flex w-full items-start gap-3 rounded-sm px-2 py-2 text-left text-sm hover:bg-accent" @click="reportType = 'category_breakdown'; typeOpen = false"><x-lucide-bar-chart-3 class="mt-0.5 h-4 w-4 text-indigo-600" /><span><span class="block font-medium">Case Classification Report</span><span class="mt-0.5 block text-xs text-gray-500">Classification statistics</span></span></button>
                            <button type="button" class="flex w-full items-start gap-3 rounded-sm px-2 py-2 text-left text-sm hover:bg-accent" @click="reportType = 'location_analysis'; typeOpen = false"><x-lucide-bar-chart-3 class="mt-0.5 h-4 w-4 text-emerald-600" /><span><span class="block font-medium">Geographic Distribution Report</span><span class="mt-0.5 block text-xs text-gray-500">Campus location distribution</span></span></button>
                            <button type="button" class="flex w-full items-start gap-3 rounded-sm px-2 py-2 text-left text-sm hover:bg-accent" @click="reportType = 'severity_analysis'; typeOpen = false"><x-lucide-alert-circle class="mt-0.5 h-4 w-4 text-red-600" /><span><span class="block font-medium">Severity Level Distribution</span><span class="mt-0.5 block text-xs text-gray-500">Harassment degree / severity mix</span></span></button>
                            <div class="mt-2 border-b border-t px-2 py-2 text-xs font-semibold text-gray-500">Performance & Metrics Reports</div>
                            <button type="button" class="flex w-full items-start gap-3 rounded-sm px-2 py-2 text-left text-sm hover:bg-accent" @click="reportType = 'resolution_time'; typeOpen = false"><x-lucide-clock class="mt-0.5 h-4 w-4 text-amber-600" /><span><span class="block font-medium">Resolution Performance Metrics</span><span class="mt-0.5 block text-xs text-gray-500">SLA compliance & timings</span></span></button>
                            <button type="button" class="flex w-full items-start gap-3 rounded-sm px-2 py-2 text-left text-sm hover:bg-accent" @click="reportType = 'handler_performance'; typeOpen = false"><x-lucide-users class="mt-0.5 h-4 w-4 text-teal-600" /><span><span class="block font-medium">Staff Performance Analysis</span><span class="mt-0.5 block text-xs text-gray-500">Staff efficiency & workload</span></span></button>
                        </div>
                    </div>
                </div>

                <div x-show="complete()" class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <div class="text-sm text-gray-700"><strong>Complete Report:</strong> This report includes all available analyses (Frequency, Trends, Resolution Metrics, and Staff Performance).</div>
                </div>
                <div x-show="!complete()" x-cloak class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                    <div class="text-sm text-blue-800"><strong>Focused Report:</strong> This report contains detailed in-depth analysis of one specific area.</div>
                </div>

                <div class="space-y-2">
                    <label for="date-range" class="text-sm font-medium leading-none">Date Range</label>
                    <select id="date-range" x-model="dateRange" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="last_7_days">Last 7 Days</option>
                        <option value="last_30_days">Last 30 Days (Monthly)</option>
                        <option value="last_90_days">Last 90 Days (Quarterly)</option>
                        <option value="last_6_months">Last 6 Months</option>
                        <option value="last_year">Last 12 Months (Annual)</option>
                        <option value="custom">Custom Date Range</option>
                    </select>
                </div>

                <div x-show="dateRange === 'custom'" x-cloak class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="start-date" class="text-sm font-medium leading-none">Start Date</label>
                        <input id="start-date" type="date" class="w-full rounded-md border px-3 py-2">
                    </div>
                    <div class="space-y-2">
                        <label for="end-date" class="text-sm font-medium leading-none">End Date</label>
                        <input id="end-date" type="date" class="w-full rounded-md border px-3 py-2">
                    </div>
                </div>

                <div class="space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                        <x-lucide-shield class="h-4 w-4 text-[#1D9E75]" />
                        Privacy & Compliance Settings
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <label for="anonymize" class="text-sm font-medium leading-none">Anonymize Personal Data</label>
                            <p class="text-sm text-gray-500">Replace names and emails with generic identifiers</p>
                        </div>
                        <button id="anonymize" type="button" role="switch" @click="anonymize = !anonymize; if (anonymize) includePersonal = false" class="inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors" :class="anonymize ? 'bg-primary' : 'bg-input'">
                            <span class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg transition-transform" :class="anonymize ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <label for="personal-data" class="text-sm font-medium leading-none">Include Personal Data</label>
                            <p class="text-sm text-gray-500">Include names and contact information (requires authorization)</p>
                        </div>
                        <button id="personal-data" type="button" role="switch" :disabled="anonymize" @click="if (!anonymize) includePersonal = !includePersonal" class="inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors disabled:cursor-not-allowed disabled:opacity-50" :class="includePersonal ? 'bg-primary' : 'bg-input'">
                            <span class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg transition-transform" :class="includePersonal ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                    <div x-show="includePersonal && !anonymize" x-cloak class="flex items-start gap-2 rounded border border-yellow-200 bg-yellow-50 p-3">
                        <x-lucide-alert-circle class="mt-0.5 h-4 w-4 flex-shrink-0 text-yellow-600" />
                        <div class="text-sm text-yellow-800"><strong>Warning:</strong> Including personal data requires proper authorization and must comply with GDPR and data protection regulations.</div>
                    </div>
                </div>

                <button type="button" @click="generated = true" class="inline-flex h-11 w-full items-center justify-center rounded-md px-8 text-sm font-medium text-white" style="background-color: #1D9E75;">
                    <x-lucide-file-text class="h-4 w-4" />
                    <span class="ml-2">Generate <span x-text="labels[reportType]"></span></span>
                </button>
            </div>
        </div>

        <div x-show="generated" x-cloak class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="flex items-center gap-2 text-2xl font-semibold leading-none tracking-tight">
                            Report Generated Successfully
                            <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-0.5 text-xs font-semibold text-gray-700" x-text="labels[reportType]"></span>
                        </h3>
                        <p class="text-sm text-muted-foreground" x-text="ranges[dateRange]"></p>
                    </div>
                    <button type="button" @click="exportOpen = true" class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent">
                        <x-lucide-download class="mr-2 h-4 w-4" />
                        Export
                    </button>
                </div>
            </div>
            <div class="p-6 pt-0">
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-white p-5"><div class="text-3xl font-bold text-gray-900">0</div><div class="mt-1 text-sm font-medium text-gray-500">Total Incidents</div></div>
                    <div class="rounded-xl border border-gray-200 bg-white p-5"><div class="text-3xl font-bold text-gray-900">0</div><div class="mt-1 text-sm font-medium text-gray-500">Resolved</div></div>
                    <div class="rounded-xl border border-gray-200 bg-white p-5"><div class="text-3xl font-bold text-gray-900">0</div><div class="mt-1 text-sm font-medium text-amber-600">In Progress</div></div>
                    <div class="rounded-xl border border-gray-200 bg-white p-5"><div class="text-3xl font-bold text-gray-900">0.0%</div><div class="mt-1 text-sm font-medium text-gray-500">Resolution Rate</div></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-0.5 text-xs font-semibold text-gray-700"><x-lucide-shield class="mr-1 h-3 w-3" /> Privacy Compliant</span>
                    <span x-show="anonymize" class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-0.5 text-xs font-semibold text-gray-700">Data Anonymized</span>
                    <span x-show="!includePersonal" class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-0.5 text-xs font-semibold text-gray-700">No Personal Data</span>
                </div>
            </div>
        </div>
    </div>

    <div x-show="!generated">
        <div class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <div class="mb-3 flex items-center gap-2">
                    <x-lucide-shield class="h-4 w-4 text-[#1D9E75]" />
                    <h3 class="text-sm font-semibold text-gray-900">About Compliance Reports</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start gap-2"><div class="mt-2 h-1 w-1 flex-shrink-0 rounded-full bg-gray-400"></div><span>All reports are <strong>GDPR and data privacy compliant</strong></span></li>
                    <li class="flex items-start gap-2"><div class="mt-2 h-1 w-1 flex-shrink-0 rounded-full bg-gray-400"></div><span>Personal data can be <strong>anonymized automatically</strong></span></li>
                    <li class="flex items-start gap-2"><div class="mt-2 h-1 w-1 flex-shrink-0 rounded-full bg-gray-400"></div><span>Reports include <strong>anonymous vs identified complainant breakdown</strong>, category trends, and performance metrics</span></li>
                    <li class="flex items-start gap-2"><div class="mt-2 h-1 w-1 flex-shrink-0 rounded-full bg-gray-400"></div><span>Export reports in <strong>PDF, Excel, CSV, or JSON format</strong></span></li>
                </ul>
            </div>
            <div class="rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-indigo-50 p-6">
                <h3 class="mb-4 font-semibold text-blue-900">What's Included in Each Report Type?</h3>
                <div class="grid gap-4 text-sm md:grid-cols-2">
                    <div class="rounded-lg border border-blue-200 bg-white p-4">
                        <div class="mb-2 font-semibold text-blue-900">Complete Summary Reports</div>
                        <div class="mb-2 text-xs text-blue-700">Monthly • Quarterly • Annual</div>
                        <ul class="list-disc space-y-1 pl-5 text-blue-800">
                            <li>Anonymous vs Identified filing analytics</li>
                            <li>Frequency Analysis</li>
                            <li>Trend Analysis</li>
                            <li>Resolution Metrics</li>
                            <li>Staff Performance</li>
                        </ul>
                    </div>
                    <div class="rounded-lg border border-blue-200 bg-white p-4">
                        <div class="mb-2 font-semibold text-blue-900">Focused Analysis Reports</div>
                        <div class="mb-2 text-xs text-blue-700">Frequency • Trend • Resolution • Staff</div>
                        <ul class="list-disc space-y-1 pl-5 text-blue-800">
                            <li>One specific analysis type</li>
                            <li>In-depth detailed data</li>
                            <li>Targeted insights</li>
                            <li>Quick generation</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="generated" x-cloak class="mt-8 space-y-6">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-semibold leading-none tracking-tight">Compliance Report</h3>
                        <p class="text-sm text-muted-foreground">Generated on <span x-text="new Date().toLocaleString()"></span></p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600">Period</div>
                        <div class="font-medium" x-text="ranges[dateRange]"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6"><h3 class="text-2xl font-semibold leading-none tracking-tight">Summary Overview</h3></div>
            <div class="p-6 pt-0">
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-7">
                    <div class="rounded-xl border border-green-200 bg-gradient-to-br from-green-50 to-green-100 p-5"><div class="text-3xl font-bold text-green-900">0</div><div class="mt-1 text-sm font-medium text-green-600">Total Incidents</div></div>
                    <div class="rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-5"><div class="text-3xl font-bold text-indigo-900">0</div><div class="mt-1 flex items-center gap-1 text-sm font-medium text-indigo-600"><x-lucide-eye-off class="h-3.5 w-3.5" /> Anonymous</div><div class="mt-1 text-xs text-indigo-500">0.0% of filings</div></div>
                    <div class="rounded-xl border border-teal-200 bg-gradient-to-br from-teal-50 to-teal-100 p-5"><div class="text-3xl font-bold text-teal-900">0</div><div class="mt-1 flex items-center gap-1 text-sm font-medium text-teal-600"><x-lucide-user-check class="h-3.5 w-3.5" /> Identified</div><div class="mt-1 text-xs text-teal-500">0.0% of filings</div></div>
                    <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-emerald-100 p-5"><div class="text-3xl font-bold text-emerald-900">0</div><div class="mt-1 text-sm font-medium text-emerald-600">Resolved</div></div>
                    <div class="rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50 to-amber-100 p-5"><div class="text-3xl font-bold text-amber-900">0</div><div class="mt-1 text-sm font-medium text-amber-600">In Progress</div></div>
                    <div class="rounded-xl border border-orange-200 bg-gradient-to-br from-orange-50 to-orange-100 p-5"><div class="text-3xl font-bold text-orange-900">0</div><div class="mt-1 text-sm font-medium text-orange-600">Pending</div></div>
                    <div class="rounded-xl border border-cyan-200 bg-gradient-to-br from-cyan-50 to-cyan-100 p-5"><div class="text-3xl font-bold text-cyan-900">0%</div><div class="mt-1 text-sm font-medium text-cyan-600">Resolution Rate</div></div>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6"><h3 class="text-2xl font-semibold leading-none tracking-tight">Key Analytics & Insights</h3></div>
            <div class="space-y-4 p-6 pt-0">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-indigo-100 p-4"><div class="text-sm font-medium text-indigo-600">Avg Resolution Time</div><div class="mt-1 text-2xl font-bold text-indigo-900">0h</div></div>
                    <div class="rounded-xl border border-cyan-200 bg-gradient-to-br from-cyan-50 to-cyan-100 p-4"><div class="text-sm font-medium text-cyan-600">Cases/Day Avg</div><div class="mt-1 text-2xl font-bold text-cyan-900">0.0</div></div>
                    <div class="rounded-xl border border-rose-200 bg-gradient-to-br from-rose-50 to-rose-100 p-4"><div class="text-sm font-medium text-rose-600">Dismissed Rate</div><div class="mt-1 text-2xl font-bold text-rose-900">0.0%</div></div>
                </div>
                <div class="space-y-2 rounded-xl border border-green-200 bg-gradient-to-br from-green-50 to-emerald-50 p-4">
                    <div class="mb-2 font-semibold text-green-900">Key Insights</div>
                    <ul class="list-disc space-y-1 pl-5 text-sm text-green-800">
                        <li>0 incidents resolved (0% success rate)</li>
                        <li>0 anonymous filings (0%) vs 0 identified (0%)</li>
                        <li>0 cases currently under investigation</li>
                        <li>0 cases awaiting review or assignment</li>
                    </ul>
                </div>
                <div class="flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 p-3">
                    <x-lucide-shield class="h-4 w-4 text-green-600" />
                    <span class="text-sm text-green-700" x-text="(anonymize ? 'Data anonymized' : 'Personal data included') + ' • GDPR Compliant'"></span>
                </div>
            </div>
        </div>
        <div class="rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 shadow-sm">
            <div class="p-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-semibold text-gray-700">Analyses Included:</span>
                    <span x-show="complete() || reportType === 'frequency_analysis'" class="inline-flex items-center rounded-full bg-blue-600 px-2.5 py-0.5 text-xs font-semibold text-white"><x-lucide-bar-chart-3 class="mr-1 h-3 w-3" /> Frequency Analysis</span>
                    <span x-show="complete() || reportType === 'trend_analysis'" class="inline-flex items-center rounded-full bg-purple-600 px-2.5 py-0.5 text-xs font-semibold text-white"><x-lucide-trending-up class="mr-1 h-3 w-3" /> Trend Analysis</span>
                    <span x-show="complete() || reportType === 'resolution_time'" class="inline-flex items-center rounded-full bg-amber-600 px-2.5 py-0.5 text-xs font-semibold text-white"><x-lucide-clock class="mr-1 h-3 w-3" /> Resolution Metrics</span>
                    <span x-show="complete() || reportType === 'handler_performance'" class="inline-flex items-center rounded-full bg-teal-600 px-2.5 py-0.5 text-xs font-semibold text-white"><x-lucide-users class="mr-1 h-3 w-3" /> Staff Performance</span>
                    <span x-show="!complete() && !['frequency_analysis','trend_analysis','resolution_time','handler_performance'].includes(reportType)" class="text-sm text-gray-500">Summary data only</span>
                </div>
            </div>
        </div>
        <div x-data="{ tab: 'frequency' }" class="w-full">
            <div class="inline-flex h-10 w-full items-center justify-center rounded-md bg-muted p-1 text-muted-foreground">
                <button type="button" @click="tab = 'frequency'" class="inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium" :class="tab === 'frequency' ? 'bg-background text-foreground shadow-sm' : ''">Frequency Analysis</button>
                <button type="button" @click="tab = 'trends'" class="inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium" :class="tab === 'trends' ? 'bg-background text-foreground shadow-sm' : ''">Trend Analysis</button>
                <button type="button" @click="tab = 'resolution'" class="inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium" :class="tab === 'resolution' ? 'bg-background text-foreground shadow-sm' : ''">Resolution Metrics</button>
                <button type="button" @click="tab = 'handlers'" class="inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium" :class="tab === 'handlers' ? 'bg-background text-foreground shadow-sm' : ''">Staff Performance</button>
            </div>
            <div class="mt-2 rounded-lg border bg-card p-6 text-center text-sm text-gray-500" x-show="tab === 'frequency'">No data yet</div>
            <div class="mt-2 rounded-lg border bg-card p-6 text-center text-sm text-gray-500" x-show="tab === 'trends'" x-cloak>No data yet</div>
            <div class="mt-2 rounded-lg border bg-card p-6 text-center text-sm text-gray-500" x-show="tab === 'resolution'" x-cloak>No data yet</div>
            <div class="mt-2 rounded-lg border bg-card p-6 text-center text-sm text-gray-500" x-show="tab === 'handlers'" x-cloak>No data yet</div>
        </div>
    </div>

    <div x-show="exportOpen" x-cloak class="fixed inset-0 z-50 bg-black/80" @keydown.escape.window="exportOpen = false"></div>
    <div x-show="exportOpen" x-cloak class="fixed left-1/2 top-1/2 z-50 grid w-full max-w-md -translate-x-1/2 -translate-y-1/2 gap-4 border bg-background p-6 shadow-lg sm:rounded-lg" @click.outside="exportOpen = false">
        <button type="button" class="absolute right-4 top-4 rounded-sm opacity-70 hover:opacity-100" @click="exportOpen = false">
            <x-lucide-x class="h-4 w-4" />
            <span class="sr-only">Close</span>
        </button>
        <div class="flex flex-col space-y-1.5 text-center sm:text-left">
            <h2 class="text-lg font-semibold leading-none tracking-tight">Export Report</h2>
            <p class="text-sm text-muted-foreground">Download in single or multiple formats</p>
        </div>
        <div class="mt-4 space-y-4">
            <button type="button" class="inline-flex h-10 w-full items-center justify-center rounded-md px-4 text-sm font-medium text-white" style="background-color: #1D9E75;">
                <x-lucide-download class="mr-2 h-4 w-4" />
                Download All Formats (PDF + CSV + JSON)
            </button>
            <div class="relative">
                <div class="absolute inset-0 flex items-center"><span class="w-full border-t"></span></div>
                <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-gray-500">Or choose individual format</span></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" class="inline-flex h-10 items-center justify-start rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent"><x-lucide-file-text class="mr-2 h-4 w-4" /> PDF Only</button>
                <a href="/admin/compliance-reports/export" class="inline-flex h-10 items-center justify-start rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent"><x-lucide-file-text class="mr-2 h-4 w-4" /> CSV Only</a>
                <button type="button" class="inline-flex h-10 items-center justify-start rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent"><x-lucide-file-text class="mr-2 h-4 w-4" /> JSON Only</button>
                <button type="button" disabled class="inline-flex h-10 cursor-not-allowed items-center justify-start rounded-md border border-input bg-background px-4 text-sm font-medium opacity-50"><x-lucide-file-text class="mr-2 h-4 w-4" /> Excel (Soon)</button>
            </div>
        </div>
    </div>
</div>
</x-layouts.admin>
