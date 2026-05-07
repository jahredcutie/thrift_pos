<?php
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
$dailySales = $dailySales ?? [];
$allSales = $allSales ?? [];
$showAllOrders = $showAllOrders ?? false;
$selectedDate = $selectedDate ?? date('Y-m-d');
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div x-data="reportsApp()" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main :class="sidebarOpen ? 'ml-64' : 'ml-20 md:ml-64'" class="flex-1 bg-background min-h-screen p-4 md:p-8 transition-soft">
        <header class="mb-6 md:mb-10">
            <h1 class="text-xl md:text-2xl font-extrabold text-primary tracking-tight">Analytics & Reports</h1>
        </header>

        <div class="grid grid-cols-1 gap-6 md:gap-8">
            <!-- Date Filter -->
            <div class="bg-surface rounded-xl p-4 md:p-6 shadow-sm border border-border">
                <div class="flex flex-col md:flex-row gap-4 items-start md:items-center">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Date Filter</label>
                        <div class="flex gap-3">
                            <div class="relative flex-1">
                                <i class="fa-solid fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-secondary/40 pointer-events-none"></i>
                                <input type="date" x-model="selectedDate" @change="window.location.href = '<?php echo $base_url; ?>/reports?date=' + this.selectedDate"
                                    class="w-full pl-10 pr-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm">
                            </div>
                            <div class="flex gap-2">
                                <button @click="selectedDate = '<?php echo date('Y-m-d', strtotime('-1 day')); ?>'; window.location.href='<?php echo $base_url; ?>/reports?date=' + '<?php echo date('Y-m-d', strtotime('-1 day')); ?>'"
                                    class="px-4 py-2.5 bg-background border border-border hover:border-accent/30 transition-all rounded-lg text-xs font-bold">
                                    Yesterday
                                </button>
                                <button @click="selectedDate = '<?php echo date('Y-m-d'); ?>'; window.location.href='<?php echo $base_url; ?>/reports?date=' + '<?php echo date('Y-m-d'); ?>'"
                                    class="px-4 py-2.5 bg-accent text-white hover:bg-accent-hover transition-all rounded-lg text-xs font-bold">
                                    Today
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Sales Summary -->
            <div class="bg-surface rounded-xl p-4 md:p-6 shadow-sm border border-border">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-primary">Daily Sales Summary</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-background rounded-lg p-4 border border-border">
                        <p class="text-xs font-bold uppercase tracking-widest text-secondary mb-1">Total Earnings</p>
                        <p class="text-2xl font-bold text-primary">₱<?php echo number_format($dailyEarnings ?? 0, 2); ?></p>
                    </div>
                    <div class="bg-background rounded-lg p-4 border border-border">
                        <p class="text-xs font-bold uppercase tracking-widest text-secondary mb-1">Number of Transactions</p>
                        <p class="text-2xl font-bold text-primary"><?php echo count($dailyTransactions ?? []); ?></p>
                    </div>
                </div>
            </div>

            <!-- Sales Trends -->
            <div class="bg-surface rounded-xl p-4 md:p-6 shadow-sm border border-border">
                <h3 class="text-lg font-bold text-primary mb-4 md:mb-6">Recent Sales Trends (Daily)</h3>
                <div class="space-y-3">
                    <?php foreach ($dailySales as $day): ?>
                    <div class="flex items-center justify-between p-4 bg-background rounded-lg border border-border hover:border-accent/30 transition-soft">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-primary truncate"><?php echo date('l, M d', strtotime($day['date'])); ?></p>
                            <p class="text-[10px] text-secondary font-medium"><?php echo $day['count']; ?> transactions</p>
                        </div>
                        <div class="text-right flex-shrink-0 ml-3">
                            <p class="text-lg md:text-xl font-bold text-primary">₱<?php echo number_format($day['total'], 2); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function reportsApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true', 
        sidebarOpen: localStorage.getItem('sidebarOpen') === 'true',
        selectedDate: '<?php echo $selectedDate; ?>',
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
