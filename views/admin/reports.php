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
$staffPerformance = $staffPerformance ?? [];
$inventoryStatus = $inventoryStatus ?? [];
$allSales = $allSales ?? [];
$showAllOrders = $showAllOrders ?? false;
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div x-data="reportsApp()" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main :class="sidebarOpen ? 'ml-64' : 'ml-20 md:ml-64'" class="flex-1 bg-background min-h-screen p-4 md:p-8 transition-soft">
        <header class="mb-6 md:mb-10">
            <h1 class="text-xl md:text-2xl font-extrabold text-primary tracking-tight">Analytics & Reports</h1>
           
        </header>

        <div class="grid grid-cols-1 gap-6 md:gap-8">
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

            <!-- Staff Performance -->
            <div class="bg-surface rounded-xl p-4 md:p-6 shadow-sm border border-border">
                <h3 class="text-lg font-bold text-primary mb-4 md:mb-6">Staff Performance</h3>
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-bold text-secondary uppercase tracking-widest border-b border-border">
                                <th class="pb-4">Staff Member</th>
                                <th class="pb-4">Transactions</th>
                                <th class="pb-4 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <?php foreach ($staffPerformance as $staff): ?>
                            <tr>
                                <td class="py-4 text-sm font-bold text-primary capitalize"><?php echo $staff['username']; ?></td>
                                <td class="py-4 text-xs text-secondary font-medium"><?php echo $staff['sales_count']; ?></td>
                                <td class="py-4 text-right text-sm font-bold text-accent">₱<?php echo number_format($staff['total_revenue'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Mobile Cards -->
                <div class="md:hidden space-y-3">
                    <?php foreach ($staffPerformance as $staff): ?>
                    <div class="p-4 bg-background rounded-lg border border-border flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold text-primary capitalize"><?php echo $staff['username']; ?></p>
                            <p class="text-xs text-secondary font-medium"><?php echo $staff['sales_count']; ?> transactions</p>
                        </div>
                        <p class="text-sm font-bold text-accent">₱<?php echo number_format($staff['total_revenue'], 2); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Inventory Breakdown -->
            <div class="bg-surface rounded-xl p-4 md:p-6 shadow-sm border border-border">
                <h3 class="text-lg font-bold text-primary mb-4 md:mb-6">Inventory Status Breakdown</h3>
                <div class="grid grid-cols-3 gap-2 md:gap-4">
                    <!-- Available -->
                    <div class="text-center p-3 md:p-5 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800">
                        <div class="flex justify-center mb-1 md:mb-2">
                            <div class="w-8 h-8 md:w-10 md:h-10 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-boxes-stacked text-sm md:text-lg"></i>
                            </div>
                        </div>
                        <p class="text-[8px] md:text-[10px] font-bold text-green-600 dark:text-green-400 uppercase tracking-widest mb-1 md:mb-2">Available</p>
                        <h4 class="text-lg md:text-xl font-bold text-green-700 dark:text-green-300">
                            <?php
                            $availableCount = 0;
                            foreach ($inventoryStatus as $status) {
                                if (strtolower($status['status']) === 'available') {
                                    $availableCount = $status['count'];
                                    break;
                                }
                            }
                            echo $availableCount;
                            ?>
                        </h4>
                    </div>

                    <!-- Sold -->
                    <div class="text-center p-3 md:p-5 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                        <div class="flex justify-center mb-1 md:mb-2">
                            <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-cart-shopping text-sm md:text-lg"></i>
                            </div>
                        </div>
                        <p class="text-[8px] md:text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1 md:mb-2">Sold</p>
                        <h4 class="text-lg md:text-xl font-bold text-blue-700 dark:text-blue-300">
                            <?php
                            $soldCount = 0;
                            foreach ($inventoryStatus as $status) {
                                if (strtolower($status['status']) === 'sold') {
                                    $soldCount = $status['count'];
                                    break;
                                }
                            }
                            echo $soldCount;
                            ?>
                        </h4>
                    </div>

                    <!-- Reserved -->
                    <div class="text-center p-3 md:p-5 bg-orange-50 dark:bg-orange-900/20 rounded-xl border border-orange-200 dark:border-orange-800">
                        <div class="flex justify-center mb-1 md:mb-2">
                            <div class="w-8 h-8 md:w-10 md:h-10 bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-400 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-clock text-sm md:text-lg"></i>
                            </div>
                        </div>
                        <p class="text-[8px] md:text-[10px] font-bold text-orange-600 dark:text-orange-400 uppercase tracking-widest mb-1 md:mb-2">Reserved</p>
                        <h4 class="text-lg md:text-xl font-bold text-orange-700 dark:text-orange-300">
                            <?php
                            $reservedCount = 0;
                            foreach ($inventoryStatus as $status) {
                                if (strtolower($status['status']) === 'reserved') {
                                    $reservedCount = $status['count'];
                                    break;
                                }
                            }
                            echo $reservedCount;
                            ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders List (at bottom) -->
        <div class="mt-6 md:mt-8 bg-surface rounded-xl p-4 md:p-6 shadow-sm border border-border">
            <div class="flex items-center justify-between mb-4 md:mb-6">
                <h3 class="text-lg font-bold text-primary">All Orders</h3>
                <button @click="showAllOrders = !showAllOrders" class="text-xs font-bold text-accent hover:text-accent-hover transition-soft">
                    <span x-text="showAllOrders ? 'Show Less' : 'View All'"></span>
                </button>
            </div>
            <div class="space-y-3">
                <?php 
                $visibleOrders = $showAllOrders ? $allSales : array_slice($allSales, 0, 5);
                foreach ($visibleOrders as $sale): 
                ?>
                <div class="flex flex-col gap-3 p-4 bg-background rounded-lg border border-border hover:border-accent/30 transition-soft">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-surface rounded-lg flex items-center justify-center border border-border flex-shrink-0">
                                <i class="fa-solid fa-receipt text-accent"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-primary truncate">Order #<?php echo $sale['id']; ?></p>
                                <p class="text-[10px] text-secondary font-medium"><?php echo date('M d, h:i A', strtotime($sale['created_at'])); ?> • by <?php echo $sale['username']; ?></p>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 ml-3">
                            <p class="text-sm font-bold text-primary">₱<?php echo number_format($sale['total_amount'], 2); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <?php if (!empty($sale['payment_method'])): ?>
                        <span class="border border-accent/30 text-accent px-3 py-1 rounded-full text-[10px] font-bold uppercase flex-shrink-0"><?php echo formatPaymentLabel($sale['payment_method']); ?></span>
                        <?php endif; ?>
                        <span :class="{
                            'bg-green-100 text-green-700': '<?php echo $sale['status']; ?>' === 'paid',
                            'bg-yellow-100 text-yellow-700': '<?php echo $sale['status']; ?>' === 'pending'
                        }" class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase flex-shrink-0"><?php echo $sale['status']; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<script>
function reportsApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true', 
        showAllOrders: false,
        sidebarOpen: localStorage.getItem('sidebarOpen') === 'true',
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