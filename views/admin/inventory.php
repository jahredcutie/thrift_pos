<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php 
$items = $items ?? [];
$categories = $categories ?? [];
?>

<div x-data="inventoryApp()" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main :class="sidebarOpen ? 'ml-64' : 'ml-20 md:ml-64'" class="flex-1 bg-background min-h-screen p-4 md:p-8 transition-all duration-300">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 md:mb-10 gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-extrabold text-primary tracking-tight">Inventory Management</h1>
                <p class="text-sm text-secondary mt-2">Browse and manage items by section. Use the tabs to separate women and men inventory.</p>
            </div>
            <div class="flex flex-col md:flex-row gap-3">
                <button @click="showBulkModal = true" 
                    class="w-full md:w-auto bg-accent text-white px-4 md:px-5 py-2.5 rounded-lg font-bold text-xs flex items-center justify-center gap-2 hover:bg-accent-hover transition-all shadow-sm">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    Bulk Add Items
                </button>
            </div>
        </header>

        <div class="flex flex-col gap-3 mb-6">
            <div class="inline-flex rounded-full bg-surface border border-border p-1">
                <button type="button" @click="selectedSection = 'all'"
                    :class="selectedSection === 'all' ? 'bg-accent text-white' : 'bg-transparent text-secondary hover:text-primary'"
                    class="px-4 py-2 rounded-full text-xs font-bold transition-all">All</button>
                <button type="button" @click="selectedSection = 'women'"
                    :class="selectedSection === 'women' ? 'bg-accent text-white' : 'bg-transparent text-secondary hover:text-primary'"
                    class="px-4 py-2 rounded-full text-xs font-bold transition-all">Women</button>
                <button type="button" @click="selectedSection = 'men'"
                    :class="selectedSection === 'men' ? 'bg-accent text-white' : 'bg-transparent text-secondary hover:text-primary'"
                    class="px-4 py-2 rounded-full text-xs font-bold transition-all">Men</button>
            </div>
            <div class="text-xs text-secondary">
                Showing <span x-text="filteredItems().length"></span> item(s) in <span x-text="selectedSection === 'all' ? 'all sections' : selectedSection === 'women' ? 'women section' : 'men section'"></span>.
            </div>
        </div>

        <!-- Items Table / Cards -->
        <div class="bg-surface rounded-xl shadow-sm border border-border overflow-hidden">
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-background border-b border-border">
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Item</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Category</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Price</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-secondary uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <template x-if="filteredItems().length === 0">
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-secondary">No inventory items found for this section. Use Bulk Add Items or switch section.</td>
                            </tr>
                        </template>
                        <template x-for="item in filteredItems()" :key="item.id">
                        <tr class="hover:bg-background/50 transition-all group">
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-primary" x-text="item.name"></span>
                            </td>
                            <td class="px-6 py-4 text-xs text-secondary font-medium" x-text="item.category"></td>
                            <td class="px-6 py-4 text-xs font-bold text-primary" x-text="'₱' + parseFloat(item.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></td>
                            <td class="px-6 py-4">
                                <span :class="item.status == 'available' ? 'bg-green-50 text-green-600 border border-green-100' : (item.status == 'sold' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-yellow-50 text-yellow-600 border border-yellow-100')" 
                                    class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider"
                                    x-text="item.status"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                    <button @click="editItem(item)" 
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-background text-secondary hover:text-accent border border-border hover:border-accent/30 transition-all">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <form action="<?php echo $base_url; ?>/inventory/delete" method="POST" onsubmit="return confirm('Are you sure?')">
                                        <input type="hidden" name="id" :value="item.id">
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-background text-secondary hover:text-red-600 border border-border hover:border-red-100 transition-all">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Cards -->
            <div class="md:hidden divide-y divide-border">
                <template x-if="filteredItems().length === 0">
                    <div class="p-8 text-center text-sm text-secondary">
                        No inventory items found for this section. Tap “Bulk Add Items” to start adding stock.
                    </div>
                </template>
                <template x-for="item in filteredItems()" :key="item.id">
                <div class="p-4 flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-primary truncate" x-text="item.name"></p>
                            <p class="text-xs text-secondary" x-text="item.category"></p>
                        </div>
                        <span :class="item.status == 'available' ? 'bg-green-50 text-green-600 border border-green-100' : (item.status == 'sold' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-yellow-50 text-yellow-600 border border-yellow-100')" 
                            class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider flex-shrink-0"
                            x-text="item.status"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-bold text-primary" x-text="'₱' + parseFloat(item.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
                        <div class="flex items-center gap-2">
                            <button @click="editItem(item)" 
                                class="w-9 h-9 flex items-center justify-center rounded-lg bg-background text-secondary hover:text-accent border border-border hover:border-accent/30 transition-all">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                            <form action="<?php echo $base_url; ?>/inventory/delete" method="POST" onsubmit="return confirm('Are you sure?')">
                                <input type="hidden" name="id" :value="item.id">
                                <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-lg bg-background text-secondary hover:text-red-600 border border-border hover:border-red-100 transition-all">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                </template>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
            <div @click.away="showModal = false" class="bg-surface w-full max-w-lg rounded-xl overflow-hidden shadow-xl scale-in border border-border">
                <div class="p-6 border-b border-border flex items-center justify-between">
                    <h3 class="text-lg font-bold text-primary" x-text="editMode ? 'Edit Item' : 'Add New Item'"></h3>
                    <button @click="showModal = false" class="text-secondary hover:text-primary transition-colors"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="editMode ? '<?php echo $base_url; ?>/inventory/update' : '<?php echo $base_url; ?>/inventory/add'" method="POST" class="p-6 md:p-8 space-y-6">
                    <input type="hidden" name="id" :value="currentItem.id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Item Name</label>
                            <input type="text" name="name" :value="currentItem.name" required
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Category</label>
                            <select name="category" required
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all appearance-none text-sm font-medium">
                                <option value="" disabled :selected="!currentItem.category">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" :selected="currentItem.category === '<?php echo $cat; ?>'"><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Gender</label>
                            <select name="gender" required
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all appearance-none text-sm font-medium">
                                <option value="women" :selected="currentItem.gender === 'women'">Women</option>
                                <option value="men" :selected="currentItem.gender === 'men'">Men</option>
                                <option value="unisex" :selected="currentItem.gender === 'unisex'">Unisex</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Price (₱)</label>
                            <input type="number" step="0.01" name="price" :value="currentItem.price" required
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm font-medium">
                        </div>
                        <div x-show="editMode">
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Status</label>
                            <select name="status"
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all appearance-none text-sm font-medium">
                                <option value="available" :selected="currentItem.status === 'available'">Available</option>
                                <option value="sold" :selected="currentItem.status === 'sold'">Sold</option>
                                <option value="reserved" :selected="currentItem.status === 'reserved'">Reserved</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-accent text-white py-3.5 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all shadow-sm">
                        <span x-text="editMode ? 'Save Changes' : 'Add Item'"></span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Bulk Add Modal -->
        <div x-show="showBulkModal" x-cloak class="fixed inset-0 z-50 flex items-end lg:items-center justify-center p-4 bg-primary/40 backdrop-blur-sm overflow-auto">
            <div @click.away="showBulkModal = false" class="bg-surface w-full max-w-2xl rounded-xl overflow-hidden shadow-xl scale-in border border-border">
                <div class="p-6 border-b border-border flex items-center justify-between">
                    <h3 class="text-lg font-bold text-primary">Bulk Product Creation</h3>
                    <button @click="showBulkModal = false" class="text-secondary hover:text-primary transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <form action="<?php echo $base_url; ?>/inventory/add-bulk" method="POST" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Category (Rack)</label>
                        <select name="category" required
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all appearance-none text-sm font-medium">
                            <option value="" disabled>Select Category</option>
                            <?php foreach ($rackCategories as $cat): ?>
                            <option value="<?php echo $cat['name']; ?>"><?php echo $cat['name']; ?> - ₱<?php echo number_format($cat['price'], 2); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mt-1 text-[10px] text-secondary/60">Select the rack/category for bulk creation</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Gender</label>
                        <select name="gender" required
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all appearance-none text-sm font-medium">
                            <option value="women">Women</option>
                            <option value="men">Men</option>
                            <option value="unisex">Unisex</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Price (₱)</label>
                        <input type="number" step="0.01" name="price" 
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm font-medium">
                        <p class="mt-1 text-[10px] text-secondary/60">Leave empty to use rack's default price</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Stock Quantity</label>
                        <input type="number" name="quantity" min="1" max="1000" required value="10"
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm font-medium">
                        <p class="mt-1 text-[10px] text-secondary/60">Number of items to add (1-1000)</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Batch Name (Optional)</label>
                        <input type="text" name="batch_name" placeholder="e.g., Printed Shirts Rack A"
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm font-medium">
                        <p class="mt-1 text-[10px] text-secondary/60">Optional name for this batch of items</p>
                    </div>
                    <div class="bg-accent/5 border border-accent/20 rounded-lg p-4">
                        <h4 class="text-sm font-bold text-primary mb-2">What will happen:</h4>
                        <ul class="text-xs text-secondary space-y-1">
                            <li>• System will auto-generate <strong>unique IDs</strong> for each item</li>
                            <li>• Items will be tagged under the selected category with <strong>selected price</strong></li>
                            <li>• All items will default to <strong>"Available"</strong> status</li>
                            <li>• Rack stock will be updated automatically</li>
                        </ul>
                    </div>
                    <button type="submit"
                        class="w-full bg-accent text-white py-3.5 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all shadow-sm">
                        Create Items
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
function inventoryApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true' || '<?php echo $_SESSION['theme'] ?? 'light'; ?>' === 'dark',
        showModal: false,
        editMode: false,
        currentItem: {},
        sidebarOpen: localStorage.getItem('sidebarOpen') === 'true',
        items: <?php echo json_encode($items ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
        selectedSection: 'all',
        init() {
            this.$watch('darkMode', val => localStorage.setItem('darkMode', val));
            this.$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val));
            window.addEventListener('darkModeChanged', (e) => {
                this.darkMode = e.detail;
            });
        },
        filteredItems() {
            return this.items.filter(item => {
                if (this.selectedSection === 'all') return true;
                return item.gender === this.selectedSection || item.gender === 'unisex';
            });
        },
        editItem(item) {
            this.editMode = true;
            this.currentItem = {...item};
            this.showModal = true;
        }
    };
}
</script>

<style>
    @keyframes scale-in {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .scale-in { animation: scale-in 0.2s ease-out forwards; }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
