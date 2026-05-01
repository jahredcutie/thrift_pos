<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php function formatPaymentLabel($method) {
    $method = strtolower(trim((string)$method));
    $map = [
        'cash' => 'Paid via Cash',
        'gcash' => 'Paid via GCash',
        'maribank' => 'Paid via MariBank',
        'maya' => 'Paid via Maya',
        'card' => 'Paid via Card',
        'bdo' => 'Paid via BDO',
        'bpi' => 'Paid via BPI',
        'unionbank' => 'Paid via UnionBank',
        'other_bank' => 'Paid via Bank'
    ];
    return $map[$method] ?? 'Paid via ' . ucfirst(str_replace('_', ' ', $method));
} ?>

<div x-data="returnApp()" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <?php $sales = $sales ?? []; ?>

    <main class="flex-1 ml-20 md:ml-64 bg-background min-h-screen p-4 md:p-8">
        <header class="mb-8">
            <h1 class="text-2xl md:text-3xl font-extrabold text-primary">Process Returns</h1>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
            <!-- Sales List -->
            <div class="bg-surface rounded-2xl shadow-sm border border-border overflow-hidden">
                <div class="p-4 md:p-6 border-b border-border">
                    <h3 class="font-bold text-primary">Recent Sales</h3>
                </div>
                <div class="divide-y divide-border/50">
                    <?php foreach ($sales as $sale): ?>
                    <button @click="selectSale(<?php echo htmlspecialchars(json_encode($sale)); ?>)" 
                        class="w-full p-4 md:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 hover:bg-background transition-all text-left">
                        <div class="flex-1">
                            <p class="font-bold text-primary">Order #<?php echo $sale['id']; ?> • ₱<?php echo number_format($sale['total_amount'], 2); ?></p>
                            <p class="text-xs text-secondary">
                                <?php echo date('M d, h:i A', strtotime($sale['created_at'])); ?> • By <?php echo $sale['username']; ?>
                            </p>
                        </div>
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <?php if (!empty($sale['payment_method'])): ?>
                            <span class="border border-blue-500/30 text-blue-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase flex-shrink-0"><?php echo formatPaymentLabel($sale['payment_method']); ?></span>
                            <?php endif; ?>
                            <i class="fa-solid fa-chevron-right text-secondary flex-shrink-0"></i>
                        </div>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sale Items (to return) -->
            <div x-show="selectedSale" class="bg-surface rounded-2xl shadow-sm border border-border p-6 md:p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-bold text-primary">Items in Order #<span x-text="selectedSale.id"></span></h3>
                        <p class="text-xs text-secondary mt-2">Payment: <span x-text="selectedSale.payment_method ? formatPaymentLabel(selectedSale.payment_method) : 'N/A'"></span></p>
                    </div>
                    <button @click="selectedSale = null" class="text-secondary hover:text-primary">Close</button>
                </div>

                <div x-show="loadingItems" class="space-y-4">
                    <template x-for="i in 3">
                        <div class="h-20 bg-background rounded-2xl animate-pulse"></div>
                    </template>
                </div>

                <div x-show="!loadingItems" class="space-y-4">
                    <template x-for="item in saleItems" :key="item.id">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 p-4 bg-background rounded-2xl">
                            <div class="flex items-center gap-4 flex-1">
                                <img :src="item.image_url" class="w-12 h-12 rounded-xl object-cover">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-primary truncate" x-text="item.name"></p>
                                    <p class="text-xs text-secondary">₱<span x-text="item.final_price"></span></p>
                                </div>
                            </div>
                            <form action="<?php echo $base_url; ?>/returns/process" method="POST" onsubmit="return confirm('Restore this item to inventory?')" class="w-full md:w-auto">
                                <input type="hidden" name="sale_item_id" :value="item.id">
                                <input type="hidden" name="item_id" :value="item.item_id">
                                <button type="submit" class="w-full md:w-auto px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 text-xs font-bold rounded-xl hover:bg-red-600 hover:text-white transition-all">
                                    Return
                                </button>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
            
            <div x-show="!selectedSale" class="flex flex-col items-center justify-center p-8 md:p-20 text-secondary/30 border-2 border-dashed border-border rounded-2xl">
                <i class="fa-solid fa-receipt text-4xl md:text-6xl mb-4"></i>
                <p class="font-medium text-center text-sm">Select a sale from the left to process a return</p>
            </div>
        </div>
    </main>
</div>

<script>
function returnApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true',
        sidebarOpen: localStorage.getItem('sidebarOpen') === 'true',
        selectedSale: null,
        saleItems: [],
        loadingItems: false,

        formatPaymentLabel(method) {
            method = (method || '').toString().trim().toLowerCase();
            const map = {
                cash: 'Paid via Cash',
                gcash: 'Paid via GCash',
                maribank: 'Paid via MariBank',
                maya: 'Paid via Maya',
                card: 'Paid via Card',
                bdo: 'Paid via BDO',
                bpi: 'Paid via BPI',
                unionbank: 'Paid via UnionBank',
                other_bank: 'Paid via Bank'
            };
            return map[method] || `Paid via ${method.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())}`;
        },

        selectSale(sale) {
            this.selectedSale = sale;
            this.loadingItems = true;
            this.saleItems = [];
            
            fetch(`<?php echo $base_url; ?>/api/sale-items/${sale.id}`)
                .then(res => {
                    if (!res.ok) throw new Error('Failed to fetch items');
                    return res.json();
                })
                .then(data => {
                    this.saleItems = data;
                })
                .catch(err => {
                    console.error(err);
                    alert('Error loading items. Please try again.');
                })
                .finally(() => {
                    this.loadingItems = false;
                });
        }
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
