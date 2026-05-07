<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<div x-data="posApp()" x-init="init()" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') === 'true' }" 
          :class="sidebarOpen ? 'ml-64' : 'ml-20 md:ml-64'" 
          class="flex-1 flex flex-col md:flex-row min-h-screen md:h-screen overflow-auto md:overflow-hidden transition-soft">
        
        <div class="flex-1 flex flex-col bg-background border-r border-border">
            <div class="p-6 bg-surface border-b border-border flex-shrink-0">
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-3">
                        <button @click="selectSection('women')" 
                            :class="selectedSection === 'women' ? 'bg-accent text-white' : 'bg-surface text-secondary border border-border hover:border-accent/30'"
                            class="px-4 py-3 rounded-xl text-sm font-bold transition-all">
                            Women Section
                        </button>
                        <button @click="selectSection('men')"
                            :class="selectedSection === 'men' ? 'bg-accent text-white' : 'bg-surface text-secondary border border-border hover:border-accent/30'"
                            class="px-4 py-3 rounded-xl text-sm font-bold transition-all">
                            Men Section
                        </button>
                    </div>

                    <div x-show="selectedSection" x-cloak class="space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-secondary font-bold">Selected section</p>
                                <h2 class="text-lg font-bold text-primary" x-text="selectedSection === 'women' ? 'Women Section' : 'Men Section'"></h2>
                            </div>
                            <button @click="selectedSection = ''; selectedCategoryFilter = ''; search = ''; rackCategories = []"
                                class="text-secondary/70 hover:text-secondary text-xs font-bold uppercase tracking-widest transition-colors">
                                Reset selection
                            </button>
                        </div>

                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                            <template x-for="categoryFilter in categoryFilters" :key="categoryFilter">
                                <button @click="selectedCategoryFilter = categoryFilter"
                                    :class="selectedCategoryFilter === categoryFilter ? 'bg-accent text-white' : 'bg-surface text-secondary border border-border hover:border-accent/30'"
                                    class="px-3 py-2 rounded-xl text-[11px] font-bold whitespace-nowrap transition-all text-center">
                                    <span x-text="categoryFilter"></span>
                                </button>
                            </template>
                        </div>

                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-secondary/40 pointer-events-none"></i>
                            <input type="text" x-model="search" @input.debounce.300ms="filterRackCategories()"
                                :disabled="!selectedSection"
                                :class="selectedSection ? 'bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent w-full pl-10 pr-4 py-2 rounded-lg outline-none transition-all text-sm' : 'bg-surface border border-border/50 cursor-not-allowed w-full pl-10 pr-4 py-2 rounded-lg outline-none transition-all text-sm'"
                                placeholder="Search rack categories...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-6 scrollbar-thin">
                <div x-show="loading" class="space-y-4">
                    <template x-for="i in 6">
                        <div class="bg-surface rounded-xl p-4 border border-border animate-pulse">
                            <div class="h-4 bg-background rounded w-3/4 mb-3"></div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="h-8 bg-background rounded"></div>
                                <div class="h-8 bg-background rounded"></div>
                                <div class="h-8 bg-background rounded"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="!loading && selectedSection" class="space-y-8">
                    <template x-for="subcategory in Object.keys(groupedRackCategories())" :key="subcategory">
                        <div x-show="groupedRackCategories()[subcategory].length > 0">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-secondary mb-4" x-text="subcategory"></h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="category in groupedRackCategories()[subcategory]" :key="category.id">
                                    <div @click="openRackModal(category)" class="bg-surface rounded-xl p-4 border border-border hover:border-accent/30 transition-all cursor-pointer">
                                        <div class="flex items-center justify-between mb-3">
                                            <h3 class="text-sm font-bold text-primary" x-text="category.name + ' Rack'"></h3>
                                            <span :class="(category.stock_available / category.stock_total > 0.5 ? 'bg-green-50 text-green-600' : (category.stock_available / category.stock_total > 0.2 ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600')" class="px-2 py-1 rounded-full text-[10px] font-bold">
                                                <span x-text="category.stock_available"></span>/<span x-text="category.stock_total"></span>
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="price in category.price_tiers" :key="price">
                                                <span class="px-3 py-1 bg-background border border-border rounded-full text-xs font-bold text-primary">
                                                    ₱<span x-text="price"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="!loading && !selectedSection" class="flex items-center justify-center h-64">
                    <div class="text-center">
                        <i class="fa-solid fa-tags text-secondary/20 text-4xl mb-4"></i>
                        <p class="text-sm text-secondary">Select a section to view rack categories</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-[350px] lg:w-[380px] bg-surface flex flex-col md:h-full border-t border-border md:border-l flex-shrink-0">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <h2 class="text-lg font-bold flex items-center gap-2 text-primary">
                    <i class="fa-solid fa-cart-shopping text-accent"></i>
                    Current Order
                </h2>
                <button @click="cart = []" class="text-secondary/40 hover:text-red-500 transition-colors">
                    <i class="fa-solid fa-trash-can text-sm"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-3 scrollbar-thin">
                <template x-for="(cartItem, index) in cart" :key="index">
                    <div class="flex items-center gap-3 bg-background p-4 rounded-xl border border-border group transition-all">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-primary text-sm" x-text="cartItem.category.name + ' Rack'"></h4>
                            <p class="text-xs text-secondary" x-text="cartItem.category.subcategory"></p>
                            <p class="text-xs font-bold text-accent">
                                ₱<span x-text="cartItem.selectedPrice"></span> each
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="updateCartQuantity(index, -1)" 
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-background text-secondary hover:text-accent border border-border hover:border-accent/30 transition-all">
                                <i class="fa-solid fa-minus text-xs"></i>
                            </button>
                            <span class="px-3 py-1 bg-background border border-border rounded-lg text-sm font-bold text-primary min-w-[3rem] text-center" x-text="cartItem.quantity"></span>
                            <button @click="updateCartQuantity(index, 1)" 
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-background text-secondary hover:text-accent border border-border hover:border-accent/30 transition-all">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-primary">₱<span x-text="(cartItem.selectedPrice * cartItem.quantity).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></p>
                        </div>
                    </div>
                </template>

                <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-full text-secondary/20">
                    <i class="fa-solid fa-shopping-basket text-4xl mb-3"></i>
                    <p class="text-sm font-medium">Your cart is empty</p>
                </div>
            </div>

            <div class="sticky bottom-0 left-0 p-6 bg-background border-t border-border space-y-3 z-10">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Bargained Price (Optional)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-secondary/30">₱</span>
                            <input type="number" x-model="bargainedPrice" @input="calculateFinalTotal()"
                                placeholder="0"
                                class="w-full pl-8 pr-4 py-2.5 bg-surface border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-bold outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-secondary uppercase tracking-widest">Item Photo (Proof)</span>
                        <button @click="openCameraModal('checkout')" 
                            class="text-xs font-bold text-accent hover:text-accent-hover transition-colors">
                            <i class="fa-solid fa-camera mr-1"></i>
                            <span x-text="checkoutCapturedImage ? 'Change Photo' : 'Take Photo'"></span>
                        </button>
                    </div>
                    
                    <div x-show="checkoutCapturedImage" class="relative">
                        <img :src="checkoutCapturedImage" class="w-full h-32 object-cover rounded-lg border border-border">
                        <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded text-xs font-bold">
                            <i class="fa-solid fa-check mr-1"></i>Captured
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-end pt-2 border-t border-border/50">
                    <span class="text-primary font-bold text-sm">Total</span>
                    <span class="text-2xl font-bold text-primary">₱<span x-text="finalTotal()"></span></span>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-6">
                    <button @click="openPaymentModal('cash')" :disabled="cart.length === 0" 
                        class="bg-surface border border-border py-3 rounded-lg font-bold text-xs flex flex-col items-center gap-1.5 hover:border-accent/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-money-bill-wave text-accent"></i>
                        Cash
                    </button>
                    <button @click="openPaymentModal('epayment')" :disabled="cart.length === 0"
                        class="bg-[#007DFE] text-white py-3 rounded-lg font-bold text-xs flex flex-col items-center gap-1.5 hover:opacity-90 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-qrcode"></i>
                        E-Payment (QR)
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Rack Action Modal -->
    <div x-show="rackModal" x-cloak class="fixed inset-0 z-50 flex items-end lg:items-center justify-center p-4 bg-primary/40 backdrop-blur-sm overflow-auto">
        <div @click.away="rackModal = false" class="bg-surface w-full max-w-md rounded-xl overflow-hidden shadow-xl scale-in border border-border">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <h3 class="text-lg font-bold text-primary" x-text="selectedCategory?.name + ' Rack'"></h3>
                <button @click="rackModal = false" class="text-secondary hover:text-primary transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-8 space-y-4">
                <button @click="startAddToCart()"
                    class="w-full bg-accent text-white py-4 rounded-xl text-xl font-bold transition-all hover:bg-accent-hover">
                    <i class="fa-solid fa-cart-shopping mr-2"></i>Add to Cart
                </button>
                <button @click="startReserve()"
                    class="w-full bg-surface text-primary border border-border py-4 rounded-xl text-xl font-bold transition-all hover:border-accent/30">
                    <i class="fa-solid fa-bookmark mr-2"></i>Reserve Item
                </button>
            </div>
        </div>
    </div>

    <!-- Price Selection Modal -->
    <div x-show="priceModal" x-cloak class="fixed inset-0 z-50 flex items-end lg:items-center justify-center p-4 bg-primary/40 backdrop-blur-sm overflow-auto">
        <div @click.away="priceModal = false" class="bg-surface w-full max-w-md rounded-xl overflow-hidden shadow-xl scale-in border border-border">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <h3 class="text-lg font-bold text-primary" x-text="selectedCategory?.name + ' Rack'"></h3>
                <button @click="priceModal = false" class="text-secondary hover:text-primary transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-8 space-y-6">
                <p class="text-sm text-secondary text-center">Select a price tier for this item</p>
                <div class="space-y-3">
                    <template x-for="price in selectedCategory?.price_tiers" :key="price">
                        <button @click="selectPrice(price)"
                            :class="selectedPrice === price ? 'bg-accent text-white' : 'bg-surface text-primary border border-border hover:border-accent/30'"
                            class="w-full py-4 rounded-xl text-xl font-bold transition-all">
                            ₱<span x-text="price"></span>
                        </button>
                    </template>
                </div>
                <button @click="addToCart()" :disabled="!selectedPrice"
                    class="w-full bg-accent text-white py-4 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>

    <!-- Reservation Price Selection Modal -->
    <div x-show="reservePriceModal" x-cloak class="fixed inset-0 z-50 flex items-end lg:items-center justify-center p-4 bg-primary/40 backdrop-blur-sm overflow-auto">
        <div @click.away="reservePriceModal = false" class="bg-surface w-full max-w-md rounded-xl overflow-hidden shadow-xl scale-in border border-border">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <h3 class="text-lg font-bold text-primary" x-text="selectedCategory?.name + ' Rack'"></h3>
                <button @click="reservePriceModal = false" class="text-secondary hover:text-primary transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-8 space-y-6">
                <p class="text-sm text-secondary text-center">Select a price tier for this item</p>
                <div class="space-y-3">
                    <template x-for="price in selectedCategory?.price_tiers" :key="price">
                        <button @click="selectReservePrice(price)"
                            :class="reservationSelectedPrice === price ? 'bg-accent text-white' : 'bg-surface text-primary border border-border hover:border-accent/30'"
                            class="w-full py-4 rounded-xl text-xl font-bold transition-all">
                            ₱<span x-text="price"></span>
                        </button>
                    </template>
                </div>
                <button @click="openReservationForm()" :disabled="!reservationSelectedPrice"
                    class="w-full bg-accent text-white py-4 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    Continue to Reservation Form
                </button>
            </div>
        </div>
    </div>

    <!-- Reservation Form Modal -->
    <div x-show="reserveModal" x-cloak class="fixed inset-0 z-50 flex items-end lg:items-center justify-center p-4 bg-primary/40 backdrop-blur-sm overflow-auto">
        <div @click.away="if (!cameraModalActive) reserveModal = false" class="bg-surface w-full max-w-lg rounded-xl overflow-hidden shadow-xl scale-in border border-border">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <h3 class="text-lg font-bold text-primary">Reserve Item</h3>
                <button @click="reserveModal = false" class="text-secondary hover:text-primary transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form @submit.prevent="submitReservation()" class="p-6 md:p-8 space-y-4">
                <div class="bg-accent/5 border border-accent/20 rounded-lg p-4">
                    <p class="text-xs font-bold text-secondary uppercase tracking-widest mb-1">Selected Item</p>
                    <p class="font-bold text-primary" x-text="selectedCategory?.name + ' Rack'"></p>
                    <p class="text-sm font-bold text-accent">₱<span x-text="reservationSelectedPrice"></span></p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Customer Name <span class="text-accent">*</span></label>
                    <input type="text" x-model="reservationForm.customerName" required
                        class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Contact Number <span class="text-accent">*</span></label>
                    <input type="tel" x-model="reservationForm.contactNumber" required minlength="11" maxlength="11" pattern="\d{11}"
                        class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Quantity of items to reserve</label>
                    <input type="number" x-model.number="reservationForm.quantity" min="1" required
                        class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Reservation Duration (days)</label>
                    <input type="number" x-model="reservationForm.duration" min="1" max="30" required
                        class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Item Photo (Proof)</label>
                    <div class="space-y-3">
                        <div class="flex gap-2">
                            <button @click="openCameraModal('reservation')" type="button"
                                class="flex-1 bg-surface border border-border py-3 rounded-lg font-bold text-xs hover:border-accent/30 transition-all">
                                <i class="fa-solid fa-camera mr-1"></i>
                                <span x-text="reservationCapturedImage ? 'Change Photo' : 'Take Photo'"></span>
                            </button>
                        </div>
                        <div x-show="reservationCapturedImage" class="relative">
                            <img :src="reservationCapturedImage" class="w-full h-32 object-cover rounded-lg border border-border">
                            <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded text-xs font-bold">
                                <i class="fa-solid fa-check mr-1"></i>Captured
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" :disabled="!reservationCapturedImage"
                    class="w-full bg-accent text-white py-4 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    Reserve Item
                </button>
            </form>
        </div>
    </div>

    <!-- Payment Modal (Cash) -->
    <div x-show="paymentModal === 'cash'" x-cloak class="fixed inset-0 z-50 flex items-end lg:items-center justify-center p-4 bg-primary/40 backdrop-blur-sm overflow-auto">
        <div @click.away="paymentModal = null" class="bg-surface w-full max-w-md rounded-xl overflow-hidden shadow-xl scale-in border border-border">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <h3 class="text-lg font-bold text-primary">Cash Payment</h3>
                <button @click="paymentModal = null" class="text-secondary hover:text-primary transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-8 space-y-6">
                <div class="text-center">
                    <p class="text-xs font-bold text-secondary uppercase tracking-widest mb-1">Total Amount Due</p>
                    <h2 class="text-3xl font-bold text-primary">₱<span x-text="finalTotal()"></span></h2>
                </div>
                <div>
                    <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Cash Received</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-secondary/30">₱</span>
                        <input type="number" x-model="cashReceived" @input="calculateChange()"
                            class="w-full pl-8 pr-4 py-3 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-xl font-bold outline-none transition-all">
                    </div>
                </div>
                <div x-show="cashReceived >= finalTotal()" class="p-4 bg-green-50 rounded-lg flex justify-between items-center border border-green-100">
                    <span class="text-green-700 text-xs font-bold uppercase">Change</span>
                    <span class="text-xl font-bold text-green-700">₱<span x-text="change"></span></span>
                </div>
                <button @click="processPayment('cash')" :disabled="parseFloat(cashReceived) < parseFloat(finalTotal().toString().replace(/,/g, ''))"
                    class="w-full bg-accent text-white py-4 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    Complete Transaction
                </button>
            </div>
        </div>
    </div>

    <!-- E-Payment Modal -->
    <div x-show="paymentModal === 'epayment'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm overflow-auto">
        <div @click.away="closePaymentModal()" class="bg-surface w-full max-w-4xl rounded-xl overflow-hidden shadow-xl scale-in border border-border my-auto">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <div>
                    <h4 class="text-lg font-bold text-primary">E-Payment (QR)</h4>
                </div>
                <button @click="closePaymentModal()" class="text-secondary hover:text-primary transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-6 grid gap-6 lg:grid-cols-[1fr_1.2fr]">
                <div class="space-y-4">
                    <div class="rounded-2xl border border-border p-4 space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-widest text-secondary">Payment Category</h4>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <button type="button" @click="selectedEpaymentCategory = 'ewallet'" :class="selectedEpaymentCategory === 'ewallet' ? 'bg-accent text-white' : 'bg-surface text-primary border border-border'" class="w-full rounded-xl px-3 py-4 text-left text-sm font-bold transition-all">
                                <span>E-Wallet</span>
                            </button>
                            <button type="button" @click="selectedEpaymentCategory = 'card'" :class="selectedEpaymentCategory === 'card' ? 'bg-accent text-white' : 'bg-surface text-primary border border-border'" class="w-full rounded-xl px-3 py-4 text-left text-sm font-bold transition-all">
                                <span>Card</span>
                            </button>
                            <button type="button" @click="selectedEpaymentCategory = 'bank'" :class="selectedEpaymentCategory === 'bank' ? 'bg-accent text-white' : 'bg-surface text-primary border border-border'" class="w-full rounded-xl px-3 py-4 text-left text-sm font-bold transition-all">
                                <span>Bank Transfer</span>
                            </button>
                        </div>
                    </div>

                    <template x-if="selectedEpaymentCategory === 'ewallet'">
                        <div class="rounded-2xl border border-border p-4 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-widest text-secondary">E-Wallets</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="selectedEpaymentMethod = 'gcash'" :class="selectedEpaymentMethod === 'gcash' ? 'bg-accent text-white' : 'bg-surface text-primary border border-border'" class="rounded-xl px-3 py-4 text-sm font-bold transition-all">GCash</button>
                                <button type="button" @click="selectedEpaymentMethod = 'paymaya'" :class="selectedEpaymentMethod === 'paymaya' ? 'bg-accent text-white' : 'bg-surface text-primary border border-border'" class="rounded-xl px-3 py-4 text-sm font-bold transition-all">Maya</button>
                            </div>
                        </div>
                    </template>

                    <template x-if="selectedEpaymentCategory === 'card'">
                        <div class="rounded-2xl border border-border p-4 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-widest text-secondary">Card</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-secondary mb-1 uppercase tracking-widest">Cardholder Name <span class="text-accent">*</span></label>
                                    <input type="text" x-model="cardHolderName" placeholder="Full Name" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-secondary mb-1 uppercase tracking-widest">Card Number <span class="text-accent">*</span></label>
                                    <input type="text" x-model="cardNumber" @input="formatCardNumber($event)" placeholder="1234 5678 9012 3456" maxlength="19" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-secondary mb-1 uppercase tracking-widest">CVV <span class="text-accent">*</span></label>
                                        <input type="text" x-model="cardCvv" placeholder="123" maxlength="3" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-secondary mb-1 uppercase tracking-widest">Expiry Date <span class="text-accent">*</span></label>
                                        <input type="text" x-model="cardExpiry" @input="formatCardExpiry($event)" placeholder="MM/YY" maxlength="5" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="selectedEpaymentCategory === 'bank'">
                        <div class="rounded-2xl border border-border p-4 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-widest text-secondary">Bank Transfer</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="selectedEpaymentMethod = 'bdo'" :class="selectedEpaymentMethod === 'bdo' ? 'bg-accent text-white' : 'bg-surface text-primary border border-border'" class="rounded-xl px-3 py-4 text-sm font-bold transition-all">BDO</button>
                                <button type="button" @click="selectedEpaymentMethod = 'bpi'" :class="selectedEpaymentMethod === 'bpi' ? 'bg-accent text-white' : 'bg-surface text-primary border border-border'" class="rounded-xl px-3 py-4 text-sm font-bold transition-all">BPI</button>
                                <button type="button" @click="selectedEpaymentMethod = 'unionbank'" :class="selectedEpaymentMethod === 'unionbank' ? 'bg-accent text-white' : 'bg-surface text-primary border border-border'" class="rounded-xl px-3 py-4 text-sm font-bold transition-all">UnionBank</button>
                                <button type="button" @click="selectedEpaymentMethod = 'maribank'" :class="selectedEpaymentMethod === 'maribank' ? 'bg-accent text-white' : 'bg-surface text-primary border border-border'" class="rounded-xl px-3 py-4 text-sm font-bold transition-all">MariBank</button>
                                <button type="button" @click="selectedEpaymentMethod = 'other_bank'" :class="selectedEpaymentMethod === 'other_bank' ? 'bg-accent text-white' : 'bg-surface text-primary border border-border'" class="rounded-xl px-3 py-4 text-sm font-bold transition-all col-span-2">Other Bank</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-border p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="font-bold text-primary">Order summary</h4>
                                <p class="text-xs text-secondary">Total amount due via E-Payment</p>
                            </div>
                            <span class="text-2xl font-bold text-primary">₱<span x-text="finalTotal()"></span></span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs text-secondary">
                                <span>Selected channel</span>
                                <span x-text="selectedEpaymentMethod || 'None'"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-secondary">
                                <span>Waiting state</span>
                                <span x-text="paymentStatus || 'Choose a category'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Always Visible -->
                    <template x-if="selectedEpaymentMethod">
                        <div class="rounded-2xl border border-border p-4 bg-surface space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-primary" x-text="'QR Payment'"></p>
                                    <p class="text-[10px] text-secondary">Scan this QR with your app</p>
                                </div>
                            </div>
                            <img :src="getBankQrPreview()" class="mx-auto w-48 h-48" alt="QR Payment">
                            <div class="text-center text-[11px] text-secondary">
                                <p class="font-bold text-primary" x-text="maribankAccountName"></p>
                                <p x-text="'Account No: ' + maribankAccountNumber"></p>
                            </div>
                        </div>
                    </template>

                    <div x-show="paymentResult" class="rounded-2xl p-4 border border-border bg-background space-y-3" x-cloak>
                        <p class="text-xs uppercase tracking-widest font-bold text-secondary">Payment status</p>
                        <div class="rounded-xl p-4 bg-green-50 text-green-700" x-show="paymentResult === 'paid'">Payment completed successfully.</div>
                        <div class="rounded-xl p-4 bg-yellow-50 text-yellow-700" x-show="paymentResult === 'pending'">Waiting for payment. Complete the QR or checkout link.</div>
                        <div class="rounded-xl p-4 bg-red-50 text-red-700" x-show="paymentResult === 'failed'">Payment failed or was cancelled.</div>
                        <div x-show="paymentResult === 'pending'" class="flex gap-2">
                            <button type="button" @click="markAsPaid()" class="flex-1 bg-green-600 text-white py-3 rounded-lg font-bold text-sm hover:bg-green-700 transition-all">Mark as Paid</button>
                            <button type="button" @click="cancelPayment()" class="flex-1 bg-red-600 text-white py-3 rounded-lg font-bold text-sm hover:bg-red-700 transition-all">Cancel</button>
                        </div>
                    </div>

                    <button type="button" @click="submitEpayment('checkout')" :disabled="!selectedEpaymentMethod || paymentLoading" class="w-full bg-accent text-white py-4 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!paymentLoading">Create Payment</span>
                        <span x-show="paymentLoading"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div x-show="receiptModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-primary/60 backdrop-blur-sm">
        <div class="bg-surface w-full max-w-sm rounded-xl overflow-hidden shadow-xl border border-border">
            <div id="receipt-content" class="p-8 text-center text-primary font-mono">
                <div class="mb-6">
                    <h2 class="text-xl font-bold tracking-tight">Dehlia's Thrift Store</h2>
                    <p class="text-[10px] text-secondary">2 Madalena, Pugong Ginto Brgy Sta. Monica Novaliches Quezon City</p>
                    <p class="text-[10px] text-secondary mt-1">Order #<span x-text="lastSaleId"></span></p>
                </div>
                <div class="border-y border-dashed border-border py-4 my-4 text-left text-[10px] space-y-2">
                    <template x-for="item in cart" :key="item.category.id">
                        <div class="flex justify-between">
                            <span x-text="item.category.name + ' Rack'"></span>
                            <span x-text="'₱' + (item.selectedPrice * item.quantity).toFixed(2)"></span>
                        </div>
                    </template>
                </div>
                <div class="space-y-1 text-right text-[10px]">
                    <div class="flex justify-between font-bold text-sm mt-2 pt-2 border-t border-border/50">
                        <span>TOTAL:</span>
                        <span x-text="'₱' + finalTotal()"></span>
                    </div>
                </div>
                <div class="mt-6 text-left text-[10px] space-y-1">
                    <p class="font-bold">Paid Via:</p>
                    <p x-text="receiptPaymentMethod || 'Cash'" class="text-secondary"></p>
                </div>
                <div class="mt-8 pt-8 border-t border-dashed border-border">
                    <p class="text-[10px] text-secondary">Thank you for your purchase!</p>
                </div>
            </div>
            <div class="p-4 border-t border-border flex gap-3">
                <button @click="printReceipt()" class="flex-1 bg-accent text-white py-3 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all">
                    <i class="fa-solid fa-print mr-2"></i>Print Receipt
                </button>
                <button @click="closeReceipt()" class="flex-1 bg-surface border border-border text-primary py-3 rounded-lg font-bold text-sm hover:bg-background transition-all">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- Camera Modal -->
    <div x-show="cameraModalActive" x-cloak @click.stop class="fixed inset-0 z-[70] flex items-center justify-center p-0 md:p-4 bg-primary/40 backdrop-blur-sm">
        <div class="bg-surface w-full h-full md:max-w-2xl md:h-auto md:rounded-xl overflow-hidden shadow-xl border border-border md:border">
            <!-- Header -->
            <div class="p-4 md:p-6 border-b border-border flex items-center justify-between bg-surface">
                <h3 class="text-lg font-bold text-primary">
                    <i class="fa-solid fa-camera mr-2"></i>Take Photo
                </h3>
                <button @click="closeCameraModal()" class="text-secondary hover:text-primary transition-colors text-xl">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Camera Preview Area -->
            <div class="flex flex-col h-[calc(100%-120px)] md:h-96 bg-black">
                <!-- Video Feed -->
                <div x-show="!modalCapturedImage" class="flex-1 relative bg-black overflow-hidden flex items-center justify-center">
                    <video x-ref="modalCameraVideo" class="w-full h-full object-cover" autoplay muted playsinline></video>
                </div>

                <!-- Captured Image Preview -->
                <div x-show="modalCapturedImage" class="flex-1 relative bg-black overflow-hidden flex items-center justify-center">
                    <img :src="modalCapturedImage" class="w-full h-full object-cover">
                    <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-2">
                        <i class="fa-solid fa-check"></i>Photo Captured
                    </div>
                </div>

                <!-- Hidden Canvas for Capture -->
                <canvas x-ref="modalCameraCanvas" class="hidden"></canvas>
            </div>

            <!-- Actions Footer -->
            <div class="p-4 md:p-6 border-t border-border bg-surface space-y-3">
                <div class="flex gap-3">
                    <button @click="captureModalPhoto()" x-show="!modalCapturedImage"
                        class="flex-1 bg-accent text-white py-3 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-camera"></i>Capture Photo
                    </button>
                    <button @click="retakeModalPhoto()" x-show="modalCapturedImage"
                        class="flex-1 bg-secondary text-white py-3 rounded-lg font-bold text-sm hover:opacity-90 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-rotate-left"></i>Retake
                    </button>
                    <button @click="confirmCameraCapture()" x-show="modalCapturedImage"
                        class="flex-1 bg-accent text-white py-3 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i>Confirm
                    </button>
                </div>
                <button @click="closeCameraModal()"
                    class="w-full bg-surface border border-border text-primary py-2.5 rounded-lg font-bold text-sm hover:bg-background transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function posApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true',
        selectedSection: '',
        selectedCategoryFilter: '',
        search: '',
        rackCategories: [],
        loading: true,
        cart: [],
        rackModal: false,
        priceModal: false,
        reservePriceModal: false,
        reserveModal: false,
        selectedCategory: null,
        selectedPrice: null,
        reservationSelectedPrice: null,
        paymentModal: null,
        cashReceived: '',
        change: 0,
        selectedEpaymentCategory: '',
        selectedEpaymentMethod: '',
        paymentResult: '',
        paymentStatus: '',
        paymentLoading: false,
        qrCodeUrl: '',
        checkoutUrl: '',
        cardHolderName: '',
        cardNumber: '',
        cardCvv: '',
        cardExpiry: '',
        bargainedPrice: '',
        maribankAccountName: 'Dehlia\'s Thrift Store',
        maribankAccountNumber: '001234567890',
        receiptModal: false,
        lastSaleId: '',
        receiptPaymentMethod: '',
        checkoutCameraActive: false,
        checkoutCapturedImage: null,
        checkoutImageData: null,
        reservationCameraActive: false,
        reservationCapturedImage: null,
        reservationImageData: null,
        cameraModalActive: false,
        activeCameraType: null,
        modalCapturedImage: null,
        reservationForm: {
            customerName: '',
            contactNumber: '',
            duration: 1,
            quantity: 1
        },

        get categoryFilters() {
            if (this.selectedSection === 'women') {
                return ['TOPS', 'BOTTOMS', 'DRESSES', 'OUTERWEAR', 'FOOTWEAR', 'ACCESSORIES'];
            } else if (this.selectedSection === 'men') {
                return ['TOPS', 'BOTTOMS', 'OUTERWEAR', 'FOOTWEAR', 'ACCESSORIES'];
            }
            return [];
        },

        init() {
            this.fetchRackCategories();
        },

        async selectSection(section) {
            this.selectedSection = section;
            this.selectedCategoryFilter = '';
            await this.fetchRackCategories();
        },

        async fetchRackCategories() {
            if (!this.selectedSection) return;
            
            this.loading = true;
            try {
                const response = await fetch('/thrift_pos/pos/rack-categories?section=' + this.selectedSection);
                const data = await response.json();
                this.rackCategories = data;
            } catch (error) {
                console.error('Error fetching rack categories:', error);
            } finally {
                this.loading = false;
            }
        },

        filterRackCategories() {
            // Filtering is done in groupedRackCategories
        },

        groupedRackCategories() {
            let filtered = this.rackCategories;
            
            if (this.selectedCategoryFilter) {
                filtered = filtered.filter(cat => cat.subcategory === this.selectedCategoryFilter);
            }
            
            if (this.search) {
                filtered = filtered.filter(cat => 
                    cat.name.toLowerCase().includes(this.search.toLowerCase())
                );
            }
            
            const groups = {};
            filtered.forEach(cat => {
                if (!groups[cat.subcategory]) {
                    groups[cat.subcategory] = [];
                }
                groups[cat.subcategory].push(cat);
            });
            return groups;
        },

        openRackModal(category) {
            this.selectedCategory = category;
            this.rackModal = true;
        },

        startAddToCart() {
            this.rackModal = false;
            this.priceModal = true;
            this.selectedPrice = null;
        },

        startReserve() {
            this.rackModal = false;
            this.reservePriceModal = true;
            this.reservationSelectedPrice = null;
            this.reservationForm.customerName = '';
            this.reservationForm.contactNumber = '';
            this.reservationForm.duration = 1;
            this.reservationCapturedImage = null;
            this.reservationImageData = null;
        },

        selectPrice(price) {
            this.selectedPrice = price;
        },

        selectReservePrice(price) {
            this.reservationSelectedPrice = price;
        },

        openReservationForm() {
            this.reservePriceModal = false;
            this.reserveModal = true;
            this.reservationForm.quantity = 1;
        },

        addToCart() {
            if (!this.selectedPrice) return;
            
            const existingIndex = this.cart.findIndex(item => 
                item.category.id === this.selectedCategory.id && 
                item.selectedPrice === this.selectedPrice
            );

            if (existingIndex > -1) {
                this.cart[existingIndex].quantity++;
            } else {
                this.cart.push({
                    category: this.selectedCategory,
                    selectedPrice: this.selectedPrice,
                    quantity: 1
                });
            }

            this.priceModal = false;
            this.selectedCategory = null;
            this.selectedPrice = null;
        },

        async submitReservation() {
            const quantity = parseInt(this.reservationForm.quantity, 10) || 1;
            const customerName = (this.reservationForm.customerName || '').trim();
            const contactNumber = (this.reservationForm.contactNumber || '').trim();

            if (!customerName || !contactNumber || contactNumber.length !== 11 || !/^[0-9]+$/.test(contactNumber)) {
                alert('Please enter a valid customer name and 11-digit contact number.');
                return;
            }
            if (!this.reservationCapturedImage || !this.reservationSelectedPrice) {
                alert('Please capture a photo and select a reservation price before continuing.');
                return;
            }
            if (quantity < 1) {
                alert('Please enter a valid reservation quantity.');
                return;
            }
            
            const formData = new FormData();
            formData.append('category_id', this.selectedCategory.id);
            formData.append('customer_name', customerName);
            formData.append('contact_number', contactNumber);
            formData.append('duration', this.reservationForm.duration);
            formData.append('quantity', quantity);
            formData.append('image', this.reservationImageData);
            formData.append('price', this.reservationSelectedPrice);
            
            try {
                const response = await fetch('/thrift_pos/reservations/add', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Item reserved successfully!');
                    this.reserveModal = false;
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = '/thrift_pos/reservations';
                    }
                } else {
                    alert(data.message || 'Unable to reserve item.');
                }
            } catch (error) {
                console.error('Error reserving item:', error);
                alert('Error reserving item: ' + (error.message || 'Unexpected response')); 
            }
        },

        updateCartQuantity(index, delta) {
            this.cart[index].quantity += delta;
            if (this.cart[index].quantity <= 0) {
                this.cart.splice(index, 1);
            }
        },

        cartTotal() {
            return this.cart.reduce((sum, item) => sum + (item.selectedPrice * item.quantity), 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        },

        finalTotal() {
            const subtotal = this.cart.reduce((sum, item) => sum + (item.selectedPrice * item.quantity), 0);
            const parsedBargain = parseFloat(this.bargainedPrice);
            const final = (!isNaN(parsedBargain) && parsedBargain > 0) ? parsedBargain : subtotal;
            return final.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        },

        calculateFinalTotal() {
            this.change = 0;
            this.cashReceived = '';
        },

        calculateChange() {
            const total = parseFloat(this.finalTotal().replace(/,/g, ''));
            const cash = parseFloat(this.cashReceived);
            this.change = (cash - total).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        },

        openPaymentModal(type) {
            this.paymentModal = type;
            this.cashReceived = '';
            this.change = 0;
            this.selectedEpaymentCategory = '';
            this.selectedEpaymentMethod = '';
            this.paymentResult = '';
            this.paymentStatus = '';
            this.paymentLoading = false;
            this.qrCodeUrl = '';
            this.checkoutUrl = '';
        },

        closePaymentModal() {
            this.paymentModal = null;
        },

        isMobileDevice() {
            const ua = navigator.userAgent || navigator.vendor || window.opera || '';
            return /Mobi|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua) || navigator.maxTouchPoints > 1;
        },

        isSecureContext() {
            return window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        },

        showCameraPermissionError(error) {
            const errorMessage = error?.name || error?.message || '';
            let userMessage = 'Unable to access camera. ';

            if (!this.isSecureContext()) {
                userMessage += 'Your browser requires a secure connection (HTTPS) or localhost to access the camera. Please use HTTPS or access the app from localhost instead of an IP address.';
            } else if (errorMessage.includes('Permission')) {
                userMessage += 'Camera permission was denied. Please allow camera access in your browser settings.';
            } else if (errorMessage.includes('NotFound')) {
                userMessage += 'No camera device found on this device.';
            } else if (errorMessage.includes('NotSupported')) {
                userMessage += 'Camera API is not supported in your browser.';
            } else {
                userMessage += 'Please check your camera permissions and try again.';
            }

            alert(userMessage);
            console.error('Camera error:', error);
        },

        getCameraConstraints() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                return { video: true };
            }

            if (this.isMobileDevice()) {
                return {
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                };
            }

            return {
                video: {
                    facingMode: 'user',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };
        },

        toggleCheckoutCamera() {
            this.checkoutCameraActive = !this.checkoutCameraActive;
            if (this.checkoutCameraActive) {
                this.$nextTick(() => {
                    this.startCheckoutCamera();
                });
            } else {
                this.stopCheckoutCamera();
            }
        },

        startCheckoutCamera() {
            const constraints = this.getCameraConstraints();
            const setStream = (stream) => {
                const video = this.$refs.checkoutCameraVideo;
                if (!video) return;
                video.srcObject = stream;
                video.muted = true;
                video.play().catch(playError => {
                    console.warn('Video play failed:', playError);
                });
            };

            navigator.mediaDevices.getUserMedia(constraints)
                .then(setStream)
                .catch(err => {
                    if (this.isMobileDevice()) {
                        const fallbackConstraints = {
                            video: {
                                facingMode: { exact: 'environment' },
                                width: { ideal: 1280 },
                                height: { ideal: 720 }
                            }
                        };
                        navigator.mediaDevices.getUserMedia(fallbackConstraints)
                            .then(setStream)
                            .catch(error => {
                                navigator.mediaDevices.getUserMedia({ video: true })
                                    .then(setStream)
                                    .catch(finalError => {
                                        this.showCameraPermissionError(finalError);
                                    });
                            });
                    } else {
                        navigator.mediaDevices.getUserMedia({ video: true })
                            .then(setStream)
                            .catch(finalError => {
                                this.showCameraPermissionError(finalError);
                            });
                    }
                });
        },

        stopCheckoutCamera() {
            if (this.$refs.checkoutCameraVideo && this.$refs.checkoutCameraVideo.srcObject) {
                const tracks = this.$refs.checkoutCameraVideo.srcObject.getTracks();
                tracks.forEach(track => track.stop());
            }
        },

        captureCheckoutPhoto() {
            const canvas = this.$refs.checkoutCameraCanvas;
            const video = this.$refs.checkoutCameraVideo;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            this.checkoutCapturedImage = canvas.toDataURL('image/png');
            this.checkoutImageData = this.checkoutCapturedImage;
        },

        retakeCheckoutPhoto() {
            this.checkoutCapturedImage = null;
            this.checkoutImageData = null;
        },

        toggleReservationCamera() {
            this.reservationCameraActive = !this.reservationCameraActive;
            if (this.reservationCameraActive) {
                this.$nextTick(() => {
                    this.startReservationCamera();
                });
            } else {
                this.stopReservationCamera();
            }
        },

        startReservationCamera() {
            const constraints = this.getCameraConstraints();
            const setStream = (stream) => {
                const video = this.$refs.reservationCameraVideo;
                if (!video) return;
                video.srcObject = stream;
                video.muted = true;
                video.play().catch(playError => {
                    console.warn('Video play failed:', playError);
                });
            };

            navigator.mediaDevices.getUserMedia(constraints)
                .then(setStream)
                .catch(err => {
                    if (this.isMobileDevice()) {
                        const fallbackConstraints = {
                            video: {
                                facingMode: { exact: 'environment' },
                                width: { ideal: 1280 },
                                height: { ideal: 720 }
                            }
                        };
                        navigator.mediaDevices.getUserMedia(fallbackConstraints)
                            .then(setStream)
                            .catch(error => {
                                navigator.mediaDevices.getUserMedia({ video: true })
                                    .then(setStream)
                                    .catch(finalError => {
                                        this.showCameraPermissionError(finalError);
                                    });
                            });
                    } else {
                        navigator.mediaDevices.getUserMedia({ video: true })
                            .then(setStream)
                            .catch(finalError => {
                                this.showCameraPermissionError(finalError);
                            });
                    }
                });
        },

        stopReservationCamera() {
            if (this.$refs.reservationCameraVideo && this.$refs.reservationCameraVideo.srcObject) {
                const tracks = this.$refs.reservationCameraVideo.srcObject.getTracks();
                tracks.forEach(track => track.stop());
            }
        },

        captureReservationPhoto() {
            const canvas = this.$refs.reservationCameraCanvas;
            const video = this.$refs.reservationCameraVideo;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            this.reservationCapturedImage = canvas.toDataURL('image/png');
            this.reservationImageData = this.reservationCapturedImage;
        },

        async processPayment(method) {
            const items = this.cart.map(item => ({
                category_id: item.category.id,
                selected_price: item.selectedPrice,
                quantity: item.quantity
            }));

            const paymentMethod = method === 'cash' ? 'cash' : this.selectedEpaymentMethod;
            const finalAmount = parseFloat(this.finalTotal().replace(/,/g, ''));
            
            try {
                const response = await fetch('/thrift_pos/pos/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        items: items,
                        total: finalAmount,
                        final_total: finalAmount,
                        bargained_price: this.bargainedPrice ? parseFloat(this.bargainedPrice) : null,
                        payment_method: paymentMethod,
                        cash_received: method === 'cash' ? parseFloat(this.cashReceived) : null,
                        change: method === 'cash' ? parseFloat(this.change) : null,
                        image: this.checkoutImageData
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.lastSaleId = data.sale_id;
                    this.receiptPaymentMethod = paymentMethod === 'cash' ? 'Cash' : paymentMethod;
                    this.paymentModal = null;
                    this.receiptModal = true;
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                alert('Error processing payment');
            }
        },

        formatCardNumber(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '').slice(0, 16);
            let formatted = '';
            for (let i = 0; i < value.length; i += 4) {
                formatted += value.slice(i, i + 4) + ' ';
            }
            e.target.value = formatted.trim();
        },

        formatCardExpiry(e) {
            let value = e.target.value.replace(/\D/g, '').slice(0, 4);
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2);
            }
            e.target.value = value;
        },

        getBankQrPreview() {
            return '/thrift_pos/assets/images/qrmaribank.png';
        },

        async submitEpayment() {
            await this.processPayment('epayment');
        },

        markAsPaid() {
            this.paymentResult = 'paid';
            this.processPayment('epayment');
        },

        cancelPayment() {
            this.paymentResult = 'failed';
            this.closePaymentModal();
        },

        printReceipt() {
            const content = document.getElementById('receipt-content').innerHTML;
            const printWindow = window.open('', '', 'width=300,height=600');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Receipt</title>
                        <style>
                            body { font-family: monospace; text-align: center; padding: 20px; }
                        </style>
                    </head>
                    <body>${content}</body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        },

        closeReceipt() {
            this.receiptModal = false;
            this.cart = [];
            this.lastSaleId = '';
            this.checkoutCapturedImage = null;
            this.checkoutImageData = null;
            this.bargainedPrice = '';
        },

        openCameraModal(cameraType) {
            this.activeCameraType = cameraType;
            this.cameraModalActive = true;
            this.modalCapturedImage = null;
            this.$nextTick(() => {
                this.startModalCamera();
            });
        },

        closeCameraModal() {
            this.cameraModalActive = false;
            this.stopModalCamera();
            this.modalCapturedImage = null;
            this.activeCameraType = null;
        },

        startModalCamera() {
            const constraints = this.getCameraConstraints();
            const setStream = (stream) => {
                const video = this.$refs.modalCameraVideo;
                if (!video) return;
                video.srcObject = stream;
                video.muted = true;
                video.play().catch(playError => {
                    console.warn('Video play failed:', playError);
                });
            };

            navigator.mediaDevices.getUserMedia(constraints)
                .then(setStream)
                .catch(err => {
                    if (this.isMobileDevice()) {
                        const fallbackConstraints = {
                            video: {
                                facingMode: { exact: 'environment' },
                                width: { ideal: 1280 },
                                height: { ideal: 720 }
                            }
                        };
                        navigator.mediaDevices.getUserMedia(fallbackConstraints)
                            .then(setStream)
                            .catch(error => {
                                navigator.mediaDevices.getUserMedia({ video: true })
                                    .then(setStream)
                                    .catch(finalError => {
                                        this.showCameraPermissionError(finalError);
                                        this.closeCameraModal();
                                    });
                            });
                    } else {
                        navigator.mediaDevices.getUserMedia({ video: true })
                            .then(setStream)
                            .catch(finalError => {
                                this.showCameraPermissionError(finalError);
                                this.closeCameraModal();
                            });
                    }
                });
        },

        stopModalCamera() {
            if (this.$refs.modalCameraVideo && this.$refs.modalCameraVideo.srcObject) {
                const tracks = this.$refs.modalCameraVideo.srcObject.getTracks();
                tracks.forEach(track => track.stop());
            }
        },

        captureModalPhoto() {
            const canvas = this.$refs.modalCameraCanvas;
            const video = this.$refs.modalCameraVideo;
            if (!canvas || !video) return;
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            this.modalCapturedImage = canvas.toDataURL('image/png');
        },

        retakeModalPhoto() {
            this.modalCapturedImage = null;
        },

        confirmCameraCapture() {
            if (!this.modalCapturedImage) return;

            if (this.activeCameraType === 'checkout') {
                this.checkoutCapturedImage = this.modalCapturedImage;
                this.checkoutImageData = this.modalCapturedImage;
            } else if (this.activeCameraType === 'reservation') {
                this.reservationCapturedImage = this.modalCapturedImage;
                this.reservationImageData = this.modalCapturedImage;
            }

            this.closeCameraModal();
        }
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
