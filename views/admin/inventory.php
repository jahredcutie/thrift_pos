<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php 
$items = $items ?? [];
$categories = $categories ?? [];
?>

<div x-data="inventoryApp()" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main :class="sidebarOpen ? 'ml-64' : 'ml-20 md:ml-64'" class="flex-1 bg-[#f8fafc] min-h-screen p-6 transition-all duration-300">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-800 tracking-tight">Inventory Management</h1>
            </div>
            <div class="flex gap-3">
                <button @click="showBulkModal = true" 
                    class="bg-white text-gray-700 px-4 py-2.5 rounded-lg font-semibold text-xs flex items-center justify-center gap-2 border border-gray-200 hover:border-gray-300 transition-all">
                    <i class="fa-solid fa-upload"></i>
                    Bulk Upload
                </button>
                <!-- Add New Item button removed per request -->
            </div>
        </header>

        <!-- Items Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-100">
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">ITEM</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">CATEGORY</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">GENDER/SIZE</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">PRICE</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">STOCK</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-if="groupedItems().length === 0">
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">No inventory items found.</td>
                            </tr>
                        </template>
                        <template x-for="group in groupedItems()" :key="group.name + group.category">
                        <tr class="hover:bg-gray-50 transition-all group">
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-gray-900" x-text="group.name"></span>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600" x-text="group.category"></td>
                            <td class="px-6 py-4 text-xs text-gray-600" x-text="(group.gender ? group.gender.charAt(0).toUpperCase() + group.gender.slice(1) : 'Unisex') + ' / N/A'"></td>
                            <td class="px-6 py-4 text-xs font-bold text-gray-900" x-text="'₱' + parseFloat(group.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></td>
                            <td class="px-6 py-4 text-xs text-gray-600" x-text="group.stock"></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                    <button @click="editItem(group.items[0])" 
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-gray-500 hover:text-teal-600 border border-gray-200 hover:border-teal-200 transition-all">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <form action="<?php echo $base_url; ?>/inventory/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete all these items?')">
                                        <template x-for="item in group.items" :key="item.id">
                                            <input type="hidden" name="ids[]" :value="item.id">
                                        </template>
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-gray-500 hover:text-red-600 border border-gray-200 hover:border-red-200 transition-all">
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
        </div>

        <!-- Add/Edit Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
            <div @click.away="showModal = false" class="bg-white w-full max-w-lg rounded-xl overflow-hidden shadow-xl scale-in border border-gray-200">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800" x-text="editMode ? 'Edit Item' : 'Add New Item'"></h3>
                    <button @click="showModal = false" class="text-gray-500 hover:text-gray-800 transition-colors"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form :action="editMode ? '<?php echo $base_url; ?>/inventory/update' : '<?php echo $base_url; ?>/inventory/add'" method="POST" class="p-6 md:p-8 space-y-6">
                    <input type="hidden" name="id" :value="currentItem.id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Item Name</label>
                            <input type="text" name="name" :value="currentItem.name" required
                                class="w-full px-4 py-2.5 bg-white border border-gray-200 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 rounded-lg outline-none transition-all text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Category</label>
                            <select name="category" required
                                class="w-full px-4 py-2.5 bg-white border border-gray-200 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 rounded-lg outline-none transition-all appearance-none text-sm">
                                <option value="" disabled :selected="!currentItem.category">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" :selected="currentItem.category === '<?php echo $cat; ?>'"><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Gender</label>
                            <select name="gender" required
                                class="w-full px-4 py-2.5 bg-white border border-gray-200 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 rounded-lg outline-none transition-all appearance-none text-sm">
                                <option value="women" :selected="currentItem.gender === 'women'">Women</option>
                                <option value="men" :selected="currentItem.gender === 'men'">Men</option>
                                <option value="unisex" :selected="currentItem.gender === 'unisex'">Unisex</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Price (₱)</label>
                            <input type="number" step="0.01" name="price" :value="currentItem.price" required
                                class="w-full px-4 py-2.5 bg-white border border-gray-200 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 rounded-lg outline-none transition-all text-sm">
                        </div>
                        <div x-show="editMode">
                            <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Status</label>
                            <select name="status"
                                class="w-full px-4 py-2.5 bg-white border border-gray-200 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 rounded-lg outline-none transition-all appearance-none text-sm">
                                <option value="available" :selected="currentItem.status === 'available'">Available</option>
                                <option value="sold" :selected="currentItem.status === 'sold'">Sold</option>
                                <option value="reserved" :selected="currentItem.status === 'reserved'">Reserved</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-teal-600 text-white py-3.5 rounded-lg font-bold text-sm hover:bg-teal-700 transition-all shadow-sm">
                        <span x-text="editMode ? 'Save Changes' : 'Add Item'"></span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Bulk Upload Modal -->
        <div x-show="showBulkModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-auto">
            <div @click.away="showBulkModal = false" class="bg-white w-full max-w-3xl rounded-xl overflow-hidden shadow-xl scale-in border border-gray-200">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">Bulk Product Upload</h3>
                    <button @click="showBulkModal = false" class="text-gray-500 hover:text-gray-800 transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <form action="<?php echo $base_url; ?>/inventory/add-bulk" method="POST" @submit.prevent="submitBulkUpload" class="p-6 space-y-6">
                    <input type="hidden" name="bulk_products" x-model="JSON.stringify(bulkProducts)">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr>
                                    <th class="px-2 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">NAME</th>
                                    <th class="px-2 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">CATEGORY</th>
                                    <th class="px-2 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">GENDER</th>
                                    <th class="px-2 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">PRICE</th>
                                    <th class="px-2 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">STOCK</th>
                                    <th class="px-2 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(product, index) in bulkProducts" :key="index">
                                <tr class="border-b border-gray-100">
                                    <td class="px-2 py-3">
                                        <input type="text" x-model="product.name" placeholder="Item name"
                                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-teal-500 transition-all">
                                    </td>
                                    <td class="px-2 py-3">
                                        <select x-model="product.category"
                                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-teal-500 transition-all appearance-none">
                                            <option value="" disabled>Select</option>
                                            <option value="Tops">Tops</option>
                                            <option value="Bottoms">Bottoms</option>
                                            <option value="Outerwear">Outerwear</option>
                                            <option value="Footwear">Footwear</option>
                                            <option value="Dresses">Dresses</option>
                                            <option value="Accessories">Accessories</option>
                                        </select>
                                    </td>
                                    <td class="px-2 py-3">
                                        <select x-model="product.gender"
                                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-teal-500 transition-all appearance-none">
                                            <option value="unisex">Unisex</option>
                                            <option value="women">Women</option>
                                            <option value="men">Men</option>
                                        </select>
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="number" step="0.01" x-model="product.price" placeholder="Price"
                                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-teal-500 transition-all">
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="number" min="1" x-model.number="product.stock"
                                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs outline-none focus:border-teal-500 transition-all">
                                    </td>
                                    <td class="px-2 py-3">
                                        <button type="button" @click="removeBulkProduct(index)"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-red-500 hover:bg-red-50 transition-all">
                                            <i class="fa-solid fa-trash-alt text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="button" @click="addBulkProduct()"
                            class="text-teal-600 font-semibold text-xs flex items-center gap-1 hover:text-teal-700 transition-all">
                            <i class="fa-solid fa-plus"></i>
                            Add Another Row
                        </button>
                        <button type="submit"
                            class="bg-teal-600 text-white px-6 py-2.5 rounded-lg font-semibold text-xs hover:bg-teal-700 transition-all">
                            Save All Products
                        </button>
                    </div>
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
        showBulkModal: false,
        bulkProducts: [
            { name: '', category: '', gender: 'unisex', price: '', stock: 1 }
        ],
        init() {
            this.$watch('darkMode', val => localStorage.setItem('darkMode', val));
            this.$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val));
            window.addEventListener('darkModeChanged', (e) => {
                this.darkMode = e.detail;
            });
        },
        getBaseName(name) {
            return name.replace(/\s+\d+$/, '');
        },
        groupedItems() {
            const groups = {};
            const filtered = this.items.filter(item => {
                if (this.selectedSection === 'all') return true;
                return item.gender === this.selectedSection || item.gender === 'unisex';
            });
            
            filtered.forEach(item => {
                const baseName = this.getBaseName(item.name);
                const key = `${baseName}-${item.category}-${item.gender}-${item.price}`;
                
                if (!groups[key]) {
                    groups[key] = {
                        ...item,
                        name: baseName,
                        stock: 0,
                        items: []
                    };
                }
                
                groups[key].stock++;
                groups[key].items.push(item);
            });
            
            return Object.values(groups);
        },
        editItem(item) {
            this.editMode = true;
            this.currentItem = {...item};
            this.showModal = true;
        },
        addBulkProduct() {
            this.bulkProducts.push({ name: '', category: '', gender: 'unisex', price: '', stock: 1 });
        },
        removeBulkProduct(index) {
            if (this.bulkProducts.length > 1) {
                this.bulkProducts.splice(index, 1);
            }
        },
        submitBulkUpload() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo $base_url; ?>/inventory/add-bulk';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'bulk_products';
            input.value = JSON.stringify(this.bulkProducts);
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
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
