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
                
            </div>
            <button @click="editMode = false; currentItem = {}; showModal = true" 
                class="w-full md:w-auto bg-accent text-white px-4 md:px-5 py-2.5 rounded-lg font-bold text-xs flex items-center justify-center gap-2 hover:bg-accent-hover transition-all shadow-sm">
                <i class="fa-solid fa-plus"></i>
                Add New Item
            </button>
        </header>

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
                        <template x-for="item in items" :key="item.id">
                        <tr class="hover:bg-background/50 transition-all group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <img :src="item.image_url" class="w-10 h-10 rounded-lg object-cover border border-border">
                                    <span class="text-sm font-bold text-primary" x-text="item.name"></span>
                                </div>
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
                <template x-for="item in items" :key="item.id">
                <div class="p-4 flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <img :src="item.image_url" class="w-14 h-14 rounded-lg object-cover border border-border">
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
                <form :action="editMode ? '<?php echo $base_url; ?>/inventory/update' : '<?php echo $base_url; ?>/inventory/add'" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
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
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Price (₱)</label>
                            <input type="number" step="0.01" name="price" :value="currentItem.price" required
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Tag Color</label>
                            <select name="tag_color" required
                                class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all appearance-none text-sm font-medium">
                                <option value="red" :selected="currentItem.tag_color === 'red'">Red (50% Off)</option>
                                <option value="blue" :selected="currentItem.tag_color === 'blue'">Blue (30% Off)</option>
                                <option value="green" :selected="currentItem.tag_color === 'green'">Green (20% Off)</option>
                                <option value="yellow" :selected="currentItem.tag_color === 'yellow'">Yellow (No Discount)</option>
                            </select>
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
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Item Image</label>
                            <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                                <div class="w-20 h-20 rounded-lg bg-background border border-dashed border-border flex items-center justify-center overflow-hidden relative flex-shrink-0">
                                    <template x-if="currentItem.image_url">
                                        <img :src="currentItem.image_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!currentItem.image_url">
                                        <i class="fa-solid fa-cloud-arrow-up text-secondary/20 text-2xl"></i>
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="image" accept="image/*"
                                        class="w-full text-[10px] text-secondary file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-primary file:text-white hover:file:opacity-80 transition-all">
                                    <p class="mt-1.5 text-[10px] text-secondary/40 font-medium">Real product photo from your device</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-accent text-white py-3.5 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all shadow-sm">
                        <span x-text="editMode ? 'Save Changes' : 'Add Item'"></span>
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
        init() {
            this.$watch('darkMode', val => localStorage.setItem('darkMode', val));
            this.$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val));
            window.addEventListener('darkModeChanged', (e) => {
                this.darkMode = e.detail;
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