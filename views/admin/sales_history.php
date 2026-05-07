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
$allSales = $allSales ?? [];
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div x-data="salesHistoryApp()" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main :class="sidebarOpen ? 'ml-64' : 'ml-20 md:ml-64'" class="flex-1 bg-background min-h-screen p-4 md:p-8 transition-soft">
        <header class="mb-6 md:mb-10">
            <h1 class="text-xl md:text-2xl font-extrabold text-primary tracking-tight">Sales History</h1>
        </header>

        <!-- Orders List -->
        <div class="bg-surface rounded-xl p-4 md:p-6 shadow-sm border border-border">
            <div class="flex items-center justify-between mb-4 md:mb-6">
                <h3 class="text-lg font-bold text-primary">All Orders</h3>
            </div>
            <div class="space-y-3">
                <?php foreach ($allSales as $sale): ?>
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
                    <div class="flex items-center justify-between gap-3">
                        <?php if (!empty($sale['payment_method'])): ?>
                        <span class="border border-accent/30 text-accent px-3 py-1 rounded-full text-[10px] font-bold uppercase flex-shrink-0"><?php echo formatPaymentLabel($sale['payment_method']); ?></span>
                        <?php endif; ?>
                        <span :class="{
                            'bg-green-100 text-green-700': '<?php echo $sale['status']; ?>' === 'paid',
                            'bg-yellow-100 text-yellow-700': '<?php echo $sale['status']; ?>' === 'pending'
                        }" class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase flex-shrink-0"><?php echo $sale['status']; ?></span>
                        <button @click="selectedOrder = selectedOrder === <?php echo $sale['id']; ?> ? null : <?php echo $sale['id']; ?>" class="text-xs font-bold text-accent hover:text-accent-hover transition-soft">
                            <i class="fa-solid fa-eye mr-1"></i>View Item
                        </button>
                    </div>
                    <?php 
                    $db = getDB();
                    $saleItems = $db->prepare("SELECT i.*, si.price FROM sale_items si JOIN items i ON si.item_id = i.id WHERE si.sale_id = ?");
                    $saleItems->execute([$sale['id']]);
                    $saleItems = $saleItems->fetchAll();
                    ?>
                    <div x-show="selectedOrder === <?php echo $sale['id']; ?>" x-cloak class="pt-3 border-t border-border space-y-3">
                        <?php foreach ($saleItems as $item): ?>
                        <div class="flex flex-col md:flex-row gap-3 items-start md:items-center">
                            <div class="flex-1">
                                <p class="text-sm font-bold text-primary"><?php echo $item['name']; ?></p>
                                <p class="text-xs text-secondary"><?php echo $item['category']; ?></p>
                            </div>
                            <p class="text-sm font-bold text-primary">₱<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        <?php endforeach; ?>
                        <?php if (!empty($sale['image_url'])): ?>
                        <div class="mt-3">
                            <p class="text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Proof Photo</p>
                            <img src="<?php echo $sale['image_url']; ?>" class="w-full h-32 object-cover rounded-lg border border-border" alt="Proof Photo">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<script>
function salesHistoryApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true', 
        sidebarOpen: localStorage.getItem('sidebarOpen') === 'true',
        selectedOrder: null,
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
