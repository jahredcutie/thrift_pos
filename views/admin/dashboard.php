<?php
/** @var array $stats */
/** @var array $recentSales */
/** @var array $categoryStats */
/** @var string $base_url */
require_once __DIR__ . '/../layouts/header.php';

$stats = $stats ?? [
    'today' => 0,
    'available' => 0,
    'reserved' => 0,
    'items_sold' => 0,
];
$recentSales = $recentSales ?? [];
$categoryStats = $categoryStats ?? [];
$base_url = $base_url ?? '';

function formatPaymentLabel($method) {
    $map = [
        'cash' => 'Paid via Cash',
        'gcash' => 'Paid via GCash',
        'maribank' => 'Paid via MariBank',
        'paymaya' => 'Paid via PayMaya',
        'card' => 'Paid via Card',
        'bdo' => 'Paid via BDO',
        'bpi' => 'Paid via BPI',
        'unionbank' => 'Paid via UnionBank',
        'other_bank' => 'Paid via Bank'
    ];
    return $map[$method] ?? 'Paid via ' . ucfirst(str_replace('_', ' ', $method));
}
?>

<div x-data="dashboardApp()" x-init="init()" class="flex min-h-screen transition-all duration-300" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main :class="sidebarOpen ? 'ml-64' : 'ml-20 md:ml-64'" class="flex-1 bg-background min-h-screen p-4 md:p-8 transition-all duration-300">
        <header class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-primary tracking-tight">Business Overview</h1>
                
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-surface rounded-lg border border-border flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-calendar text-accent"></i>
                    <input type="date" x-model="selectedDate" @change="window.location.href = '<?php echo $base_url; ?>/dashboard?date=' + this.selectedDate" class="text-xs font-semibold text-primary bg-transparent border-none outline-none">
                </div>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- Revenue Today -->
            <div class="bg-surface p-6 rounded-xl shadow-sm border border-border transition-soft hover:border-accent/30">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-coins text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-secondary tracking-wider uppercase">Revenue Today</span>
                </div>
                <h2 class="text-2xl font-bold text-primary tracking-tight">₱<?php echo number_format($stats['sales_on_date'], 2); ?></h2>
            </div>

            <!-- Available Items -->
            <div class="bg-surface p-6 rounded-xl shadow-sm border border-border transition-soft hover:border-accent/30">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-box-open text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-secondary tracking-wider uppercase">Available Items</span>
                </div>
                <h2 class="text-2xl font-bold text-primary tracking-tight"><?php echo $stats['available']; ?></h2>
            </div>

            <!-- Reserved Items -->
            <div class="bg-surface p-6 rounded-xl shadow-sm border border-border transition-soft hover:border-accent/30">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-clock text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-secondary tracking-wider uppercase">Reserved</span>
                </div>
                <h2 class="text-2xl font-bold text-primary tracking-tight"><?php echo $stats['reserved']; ?></h2>
            </div>

            <!-- Items Sold -->
            <div class="bg-surface p-6 rounded-xl shadow-sm border border-border transition-soft hover:border-accent/30">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-accent/10 text-accent rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-tags text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-secondary tracking-wider uppercase">Sold Items</span>
                </div>
                <h2 class="text-2xl font-bold text-primary tracking-tight"><?php echo $stats['items_sold']; ?></h2>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Earnings Calendar -->
            <div class="bg-surface rounded-3xl p-6 shadow-sm border border-border">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-primary">Earnings Calendar</h3>
                        <p class="text-sm text-secondary mt-1">Overview of daily revenue for the selected month.</p>
                    </div>
                    <div class="inline-flex items-center gap-2">
                        <button type="button" @click="window.location.href = '<?php echo $base_url; ?>/dashboard?date=' + '<?php echo date('Y-m-d', strtotime($stats['selected_date'].' -1 month')); ?>'" class="w-10 h-10 rounded-2xl bg-background border border-border text-secondary hover:border-accent hover:text-accent transition-all">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <div class="px-4 py-2 rounded-2xl bg-background border border-border text-sm font-semibold text-primary">
                            <?php echo date('F Y', strtotime($stats['selected_date'])); ?>
                        </div>
                        <button type="button" @click="window.location.href = '<?php echo $base_url; ?>/dashboard?date=' + '<?php echo date('Y-m-d', strtotime($stats['selected_date'].' +1 month')); ?>'" class="w-10 h-10 rounded-2xl bg-background border border-border text-secondary hover:border-accent hover:text-accent transition-all">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-2 text-[10px] uppercase tracking-[0.25em] text-secondary mb-3">
                    <div class="text-center py-2 rounded-2xl bg-background">Sun</div>
                    <div class="text-center py-2 rounded-2xl bg-background">Mon</div>
                    <div class="text-center py-2 rounded-2xl bg-background">Tue</div>
                    <div class="text-center py-2 rounded-2xl bg-background">Wed</div>
                    <div class="text-center py-2 rounded-2xl bg-background">Thu</div>
                    <div class="text-center py-2 rounded-2xl bg-background">Fri</div>
                    <div class="text-center py-2 rounded-2xl bg-background">Sat</div>
                </div>
                <div class="grid grid-cols-7 gap-3">
                    <?php
                    $monthStart = strtotime(date('Y-m-01', strtotime($stats['selected_date'])));
                    $daysInMonth = date('t', $monthStart);
                    $startDayOfWeek = date('w', $monthStart);
                    $maxAmount = $earningsCalendarData ? max($earningsCalendarData) : 0;
                    for ($blank = 0; $blank < $startDayOfWeek; $blank++):
                    ?>
                        <div class="min-h-[7rem] rounded-3xl bg-background border border-border"></div>
                    <?php endfor; ?>
                    <?php for ($day = 1; $day <= $daysInMonth; $day++):
                        $dayKey = date('Y-m-d', strtotime(sprintf('%s-%02d', date('Y-m', $monthStart), $day)));
                        $amount = $earningsCalendarData[$dayKey] ?? 0;
                        $hasSales = $amount > 0;
                        $isSelected = $dayKey === $stats['selected_date'];
                        $cellClass = $isSelected ? 'border-accent/60 ring-1 ring-accent/15 bg-accent/5' : 'bg-background';
                        $progressWidth = $maxAmount > 0 ? min(100, ($amount / $maxAmount) * 100) : 0;
                    ?>
                    <div class="min-h-[7rem] p-4 rounded-3xl border border-border <?php echo $cellClass; ?>">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-sm font-bold text-primary"><?php echo $day; ?></span>
                            <?php if ($isSelected): ?>
                                <span class="text-[10px] font-semibold uppercase tracking-[0.25em] text-accent">Today</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($hasSales): ?>
                            <p class="text-sm font-bold text-emerald-700 mt-3">₱<?php echo number_format($amount, 2); ?></p>
                            <div class="mt-3 h-2 rounded-full bg-background border border-border overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: <?php echo $progressWidth; ?>%;"></div>
                            </div>
                            <div class="flex items-center gap-2 mt-3">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-[11px] text-secondary">Daily earnings</span>
                            </div>
                        <?php else: ?>
                            <p class="text-[11px] text-secondary mt-3">No sales</p>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="bg-surface rounded-xl p-6 shadow-sm border border-border">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-primary">Recent Sales</h3>
                    <a href="<?php echo $base_url; ?>/sales-history" class="text-xs font-bold text-accent hover:text-accent-hover transition-soft">View All</a>
                </div>
                <div class="space-y-3">
                    <?php foreach ($recentSales as $sale): ?>
                    <div class="flex items-center justify-between p-4 bg-background rounded-lg border border-border hover:border-accent/30 transition-soft">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-surface rounded-lg flex items-center justify-center border border-border">
                                <i class="fa-solid fa-receipt text-accent"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-primary">Order #<?php echo $sale['id']; ?></p>
                                <p class="text-[10px] text-secondary font-medium"><?php echo date('M d, h:i A', strtotime($sale['created_at'])); ?> • by <?php echo $sale['username']; ?> <?php if (!empty($sale['payment_method'])): ?>• <?php echo formatPaymentLabel($sale['payment_method']); ?><?php endif; ?></p>
                            </div>
                        </div>
                        <p class="text-sm font-bold text-primary">₱<?php echo number_format($sale['total_amount'], 2); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-surface rounded-xl p-6 shadow-sm border border-border">
            <h3 class="text-lg font-bold text-primary mb-6">Rack Stock Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($rackCategories as $cat):
                    $stockPercent = $cat['stock_total'] > 0 ? ($cat['stock_available'] / $cat['stock_total']) * 100 : 0;
                    $stockPercent = min(max($stockPercent, 0), 100);
                    $colorClass = $stockPercent > 50 ? 'bg-green-500' : ($stockPercent > 20 ? 'bg-yellow-500' : 'bg-red-500');
                ?>
                <div class="bg-background rounded-3xl border border-border p-4">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <p class="text-sm font-bold text-primary"><?php echo $cat['name']; ?> <?php echo !empty($cat['gender']) ? '('.ucfirst($cat['gender']).')' : ''; ?></p>
                            <?php if (!empty($cat['subcategory'])): ?><p class="text-[11px] text-secondary mt-1"><?php echo $cat['subcategory']; ?></p><?php endif; ?>
                        </div>
                        <span class="text-[11px] font-bold text-secondary"><?php echo $cat['stock_available']; ?>/<?php echo $cat['stock_total']; ?></span>
                    </div>
                    <div class="h-2 bg-background rounded-full overflow-hidden border border-border">
                        <div class="h-full <?php echo $colorClass; ?> rounded-full" style="width: <?php echo $stockPercent; ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<script>
function dashboardApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true' || '<?php echo $_SESSION['theme'] ?? 'light'; ?>' === 'dark',
        sidebarOpen: localStorage.getItem('sidebarOpen') === 'true',
        selectedDate: '<?php echo $stats['selected_date']; ?>',

        init() {
            this.$watch('darkMode', val => localStorage.setItem('darkMode', val));
            this.$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val));
            window.addEventListener('darkModeChanged', (e) => {
                this.darkMode = e.detail;
            });
        }
    };
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
