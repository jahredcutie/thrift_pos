<?php
/** @var array $categories */
/** @var string $base_url */
require_once __DIR__ . '/../layouts/header.php';

$categories = $categories ?? [];
$base_url = $base_url ?? '';
?>

<div x-data="posApp()" x-init="init()" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') === 'true' }" 
          :class="sidebarOpen ? 'ml-64' : 'ml-20 md:ml-64'" 
          class="flex-1 flex flex-col md:flex-row min-h-screen md:h-screen overflow-auto md:overflow-hidden transition-soft">
        
        <!-- Left: Item Grid -->
        <div class="flex-1 flex flex-col bg-background border-r border-border">
            <!-- Header/Filters -->
            <div class="p-6 bg-surface border-b border-border flex-shrink-0">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-secondary/40"></i>
                        <input type="text" x-model="search" @input.debounce.300ms="fetchItems()" 
                            placeholder="Search items..." 
                            class="w-full pl-10 pr-4 py-2 bg-background border border-border rounded-lg focus:ring-1 focus:ring-accent focus:border-accent outline-none transition-all text-sm">
                    </div>
                    <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0 scrollbar-hide">
                        <button @click="category = ''; fetchItems()" 
                            :class="category === '' ? 'bg-accent text-white' : 'bg-surface text-secondary border border-border hover:border-accent/30'"
                            class="px-4 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition-all">
                            All Items
                        </button>
                        <?php foreach ($categories as $cat): ?>
                            <?php if (in_array(strtolower($cat), ['accessories', 'others'], true)) continue; ?>
                        <button @click="category = '<?php echo $cat; ?>'; fetchItems()" 
                            :class="category === '<?php echo $cat; ?>' ? 'bg-accent text-white' : 'bg-surface text-secondary border border-border hover:border-accent/30'"
                            class="px-4 py-2 rounded-lg text-xs font-bold whitespace-nowrap transition-all">
                            <?php echo $cat; ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Items Grid (scrollable) -->
            <div class="flex-1 overflow-y-auto p-6 scrollbar-thin">
                <div x-show="loading" class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <template x-for="i in 8">
                        <div class="bg-surface rounded-xl p-3 border border-border animate-pulse">
                            <div class="w-full aspect-square bg-background rounded-lg mb-3"></div>
                            <div class="h-3 bg-background rounded w-3/4 mb-2"></div>
                            <div class="h-3 bg-background rounded w-1/2"></div>
                        </div>
                    </template>
                </div>

                <div x-show="!loading" class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <template x-for="item in items" :key="item.id">
                        <div
                            :class="item.status !== 'available' ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-accent/30 hover:shadow-sm'"
                            class="group relative bg-surface rounded-xl p-3 transition-all border border-border">
                            
                            <!-- Status Badge -->
                            <div class="absolute top-4 left-4 z-10 flex flex-col gap-2">
                                <span :class="{
                                    'bg-accent': item.status === 'available',
                                    'bg-red-500': item.status === 'sold',
                                    'bg-yellow-500': item.status === 'reserved'
                                }" class="px-2 py-0.5 rounded-md text-[9px] font-bold text-white uppercase tracking-wider shadow-sm" x-text="item.status"></span>
                                
                                <!-- Tag Discount Badge -->
                                <span x-show="getDiscountRate(item.tag_color) > 0" 
                                    class="bg-red-50 text-red-600 px-1.5 py-0.5 rounded text-[8px] font-bold border border-red-100">
                                    <span x-text="Math.round(getDiscountRate(item.tag_color) * 100)"></span>% OFF
                                </span>
                            </div>

                            <!-- Tag Color Indicator -->
                            <div class="absolute top-4 right-4 z-10">
                                <div :class="'bg-'+item.tag_color+'-500'" class="w-2.5 h-2.5 rounded-full shadow-sm ring-2 ring-white"></div>
                            </div>

                            <div class="relative w-full aspect-square rounded-lg overflow-hidden mb-3 bg-background">
                                <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                            
                            <h3 class="font-bold text-primary text-sm mb-1 truncate" x-text="item.name"></h3>
                            <div class="flex items-center justify-between">
                                <p class="text-[10px] font-medium text-secondary" x-text="item.category"></p>
                                <p class="font-bold text-primary text-sm">₱<span x-text="item.price"></span></p>
                            </div>

                            <!-- Actions Overlay -->
                            <div class="mt-3 flex gap-2 opacity-0 group-hover:opacity-100 pointer-events-none group-hover:pointer-events-auto transition-all">
                                <button @click.prevent.stop="openReservationModal(item)" x-show="item.status === 'available'"
                                    class="flex-1 bg-black text-white border border-black py-2 rounded-lg text-[10px] font-bold hover:bg-gray-800 transition-all shadow-sm">
                                    Reserve
                                </button>
                                <button @click.prevent.stop="addToCart(item)" x-show="item.status === 'available'"
                                    class="flex-1 bg-accent text-white py-2 rounded-lg text-[10px] font-bold hover:bg-accent-hover transition-all shadow-sm">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="!loading && items.length === 0" class="flex flex-col items-center justify-center min-h-[40vh] text-secondary/30">
                    <i class="fa-solid fa-box-open text-4xl mb-3"></i>
                    <p class="text-sm font-medium">No items found</p>
                </div>
            </div>
        </div>

        <!-- Right: Cart Panel -->
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

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-6 space-y-3 scrollbar-thin">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="flex items-center gap-3 bg-background p-3 rounded-xl border border-border group transition-all">
                        <img :src="item.image_url" class="w-12 h-12 rounded-lg object-cover border border-border">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-primary text-sm truncate" x-text="item.name"></h4>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] font-medium text-secondary/50 line-through" x-show="item.discount > 0">₱<span x-text="item.price"></span></span>
                                <span class="text-xs font-bold text-primary">₱<span x-text="item.final_price"></span></span>
                            </div>
                        </div>
                        <button @click="removeFromCart(index)" class="w-7 h-7 flex items-center justify-center rounded-lg text-secondary/40 hover:bg-red-50 hover:text-red-500 transition-all">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                </template>

                <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-full text-secondary/20">
                    <i class="fa-solid fa-shopping-basket text-4xl mb-3"></i>
                    <p class="text-sm font-medium">Your cart is empty</p>
                </div>
            </div>

            <!-- Summary -->
            <div class="sticky bottom-0 left-0 p-6 bg-background border-t border-border space-y-3 z-10">
                <div class="flex justify-between text-xs text-secondary font-medium">
                    <span>Subtotal</span>
                    <span>₱<span x-text="cartTotal().subtotal"></span></span>
                </div>
                <div class="flex justify-between text-xs text-red-500 font-medium">
                    <span>Discounts</span>
                    <span>-₱<span x-text="cartTotal().discount"></span></span>
                </div>
                <div class="flex justify-between items-end pt-2 border-t border-border/50">
                    <span class="text-primary font-bold text-sm">Total</span>
                    <span class="text-2xl font-bold text-primary">₱<span x-text="cartTotal().total"></span></span>
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
                    <h2 class="text-3xl font-bold text-primary">₱<span x-text="cartTotal().total"></span></h2>
                </div>
                <div>
                    <label class="block text-xs font-bold text-secondary mb-2 uppercase tracking-widest">Cash Received</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-secondary/30">₱</span>
                        <input type="number" x-model="cashReceived" @input="calculateChange()"
                            class="w-full pl-8 pr-4 py-3 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-xl font-bold outline-none transition-all">
                    </div>
                </div>
                <div x-show="cashReceived >= cartTotal().total" class="p-4 bg-green-50 rounded-lg flex justify-between items-center border border-green-100">
                    <span class="text-green-700 text-xs font-bold uppercase">Change</span>
                    <span class="text-xl font-bold text-green-700">₱<span x-text="change"></span></span>
                </div>
                <button @click="processPayment('cash')" :disabled="parseFloat(cashReceived) < parseFloat(cartTotal().total)"
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
                            <div class="grid grid-cols-3 gap-3">
                                <button type="button" @click="selectedEpaymentMethod = 'card'" :class="selectedEpaymentMethod === 'card' ? 'bg-accent text-white' : 'bg-surface text-primary border border-border'" class="rounded-xl px-3 py-4 text-sm font-bold transition-all col-span-3">Visa / Mastercard / Debit</button>
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
                            <span class="text-2xl font-bold text-primary">₱<span x-text="cartTotal().total"></span></span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs text-secondary">
                                <span>Selected method</span>
                                <span x-text="selectedEpaymentMethod || 'None'"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-secondary">
                                <span>Waiting state</span>
                                <span x-text="paymentStatus || 'Choose a category'"></span>
                            </div>
                        </div>
                    </div>

                    <template x-if="selectedEpaymentMethod === 'maribank' && !qrCodeUrl">
                        <div class="rounded-2xl border border-border p-4 bg-surface space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-primary">MariBank QR Preview</p>
                                    <p class="text-[10px] text-secondary">Scan this QR with the MariBank app once you create the payment.</p>
                                </div>
                            </div>
                            <img :src="getMaribankQrPreview()" class="mx-auto w-40 h-40" alt="MariBank QR Preview">
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
                        <template x-if="selectedEpaymentMethod === 'card'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-secondary mb-1 uppercase tracking-widest">Cardholder Name</label>
                                    <input type="text" x-model="cardHolderName" placeholder="Full Name" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-secondary mb-1 uppercase tracking-widest">Card Number</label>
                                    <input type="text" x-model="cardNumber" @input="formatCardNumber($event)" placeholder="1234 5678 9012 3456" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-secondary mb-1 uppercase tracking-widest">CVV</label>
                                        <input type="text" x-model="cardCvv" placeholder="123" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-secondary mb-1 uppercase tracking-widest">Expiry Date</label>
                                        <input type="text" x-model="cardExpiry" @input="formatCardExpiry($event)" placeholder="MM/YY" class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="qrCodeUrl && selectedEpaymentMethod !== 'card'">
                            <div class="text-center">
                                <img :src="qrCodeUrl" class="mx-auto w-48 h-48" alt="Payment QR Code">
                                <p class="text-[10px] text-secondary mt-3">Scan this code with your app.</p>
                            </div>
                        </template>
                        <template x-if="!qrCodeUrl && selectedEpaymentMethod === 'maribank' && paymentResult === 'pending'">
                            <div class="rounded-2xl border border-border p-4 bg-surface text-center">
                                <p class="text-xs font-bold uppercase tracking-widest text-secondary mb-2">MariBank payment</p>
                                <p class="text-sm font-bold text-primary">Use the QR code above or the bank details shown.</p>
                            </div>
                        </template>
                        <template x-if="checkoutUrl && !qrCodeUrl && selectedEpaymentMethod !== 'card'">
                            <a :href="checkoutUrl" target="_blank" class="inline-flex items-center justify-center w-full bg-black text-white py-3 rounded-lg font-bold text-sm">Continue to Payment</a>
                        </template>
                        <template x-if="isBpi && selectedEpaymentMethod !== 'card'">
                            <div class="rounded-xl bg-blue-50 border border-blue-100 p-3 text-sm text-blue-700">Scan this QR using the BPI mobile app.</div>
                        </template>
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

    <!-- Reservation Modal -->
    <div x-show="reservationModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
        <div @click.away="reservationModal = false" class="bg-surface w-full max-w-md rounded-xl overflow-hidden shadow-xl scale-in border border-border">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <h3 class="text-lg font-bold text-primary">Reserve Item</h3>
                <button @click="reservationModal = false" class="text-secondary hover:text-primary transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form @submit.prevent="submitReservation" class="p-8 space-y-5">
                <input type="hidden" name="item_id" :value="reservingItem ? reservingItem.id : ''">
                <div class="flex items-center gap-3 p-3 bg-background rounded-xl border border-border">
                    <img :src="reservingItem ? reservingItem.image_url : ''" class="w-12 h-12 rounded-lg object-cover border border-border">
                    <div>
                        <p class="font-bold text-primary text-sm" x-text="reservingItem ? reservingItem.name : ''"></p>
                        <p class="text-xs font-bold text-accent">₱<span x-text="reservingItem ? reservingItem.price : ''"></span></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-secondary mb-1.5 uppercase tracking-widest">Customer Name *</label>
                        <input type="text" x-model="customerName" required placeholder="Full Name"
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-secondary mb-1.5 uppercase tracking-widest">Contact Number *</label>
                        <input type="text" x-model="contactNumber" inputmode="numeric" maxlength="11" required placeholder="09XXXXXXXXX"
                            @input="contactNumber = contactNumber.replace(/[^0-9]/g, '').slice(0, 11)"
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-secondary mb-1.5 uppercase tracking-widest">Reservation Duration (in days) *</label>
                        <input type="number" x-model="durationDays" min="1" step="1" required placeholder="Number of days"
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-secondary mb-1.5 uppercase tracking-widest">Notes</label>
                        <textarea x-model="notes" rows="2" placeholder="Any special instructions..."
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all resize-none"></textarea>
                    </div>
                </div>

                <button type="submit" :disabled="!customerName || loading"
                    class="w-full bg-black text-white py-4 rounded-lg font-bold text-sm hover:bg-gray-900 transition-all disabled:opacity-50 shadow-lg active:scale-[0.98] mt-2">
                    <span x-show="!loading">Confirm Reservation</span>
                    <span x-show="loading"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Processing...</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div x-show="receiptModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-primary/60 backdrop-blur-sm">
        <div class="bg-surface w-full max-w-sm rounded-xl overflow-hidden shadow-xl border border-border">
            <div id="receipt-content" class="p-8 text-center text-primary font-mono">
                <div class="mb-6">
                    <h2 class="text-xl font-bold tracking-tight">THRIFT SHOP</h2>
                    <p class="text-[10px] text-secondary">2 Madalena, Pugong Ginto Brgy Sta. Monica Novaliches Quezon City</p>
                    <p class="text-[10px] text-secondary mt-1">Order #<span x-text="lastSaleId"></span></p>
                </div>
                <div class="border-y border-dashed border-border py-4 my-4 text-left text-[10px] space-y-2">
                    <template x-for="item in cart" :key="item.id">
                        <div class="flex justify-between">
                            <span x-text="item.name"></span>
                            <span x-text="'₱' + item.final_price"></span>
                        </div>
                    </template>
                </div>
                <div class="space-y-1 text-right text-[10px]">
                    <div class="flex justify-between">
                        <span class="text-secondary">Subtotal:</span>
                        <span x-text="'₱' + cartTotal().subtotal"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-secondary">Discount:</span>
                        <span x-text="'-₱' + cartTotal().discount"></span>
                    </div>
                    <div class="flex justify-between font-bold text-sm mt-2 pt-2 border-t border-border/50">
                        <span>TOTAL:</span>
                        <span x-text="'₱' + cartTotal().total"></span>
                    </div>
                </div>
                <div class="mt-6 text-left text-[10px] space-y-1">
                    <p class="font-bold">Payment Method:</p>
                    <p x-text="receiptPaymentMethod || 'Paid via Cash'" class="text-secondary"></p>
                </div>
                <div class="mt-8 pt-8 border-t border-dashed border-border">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-secondary">Thank You for your purchase!</p>
                    <p class="text-[8px] text-secondary/50 mt-2" x-text="new Date().toLocaleString()"></p>
                </div>
            </div>
            <div class="p-4 bg-background border-t border-border grid grid-cols-2 gap-3">
                <button @click="window.print()" class="bg-surface border border-border text-primary py-2.5 rounded-lg text-xs font-bold hover:bg-background transition-all">
                    Print Receipt
                </button>
                <button @click="resetPos()" class="bg-primary text-white py-2.5 rounded-lg text-xs font-bold hover:opacity-90 transition-all shadow-sm">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function posApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true' || '<?php echo $_SESSION['theme'] ?? 'light'; ?>' === 'dark',
        loading: false,
        items: [],
        cart: [],
        category: '',
        search: '',
        paymentModal: null,
        selectedEpaymentCategory: 'ewallet',
        selectedEpaymentMethod: null,
        qrCodeUrl: null,
        checkoutUrl: null,
        paymentStatus: 'idle',
        paymentResult: null,
        paymentLoading: false,
        paymentIntentId: null,
        sourceId: null,
        transactionId: null,
        maribankAccountName: 'JAHRED CUASITO',
        maribankAccountNumber: '1940 0576 833',
        maribankQrImage: '<?php echo $base_url; ?>/assets/images/maribank-qr.png',
        cardHolderName: '',
        cardNumber: '',
        cardCvv: '',
        cardExpiry: '',
        isBpi: false,
        pendingPayment: null,
        receiptModal: false,
        reservationModal: false,
        reservingItem: null,
        customerName: '',
        contactNumber: '',
        notes: '',
        durationDays: 1,
        cashReceived: 0,
        change: 0,
        receiptPaymentMethod: '',
        baseUrl: '<?php echo trim($base_url, '/'); ?>',
        lastSaleId: null,
        discounts: {
            red: 0.50,
            blue: 0.30,
            green: 0.20,
            yellow: 0.00
        },
        resolveUrl(path) {
            if (!path.startsWith('/')) {
                path = '/' + path;
            }
            return this.baseUrl ? '/' + this.baseUrl + path : path;
        },

        formatPaymentLabel(method) {
            method = (method || '').toString().trim().toLowerCase();
            const map = {
                cash: 'Paid via Cash',
                gcash: 'Paid via GCash',
                maribank: 'Paid via MariBank',
                paymaya: 'Paid via PayMaya',
                card: 'Paid via Card',
                bdo: 'Paid via BDO',
                bpi: 'Paid via BPI',
                unionbank: 'Paid via UnionBank',
                other_bank: 'Paid via Bank'
            };
            return map[method] || `Paid via ${method.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())}`;
        },

        init() {
            this.fetchItems();
            this.$watch('darkMode', val => localStorage.setItem('darkMode', val));
        },

        fetchItems() {
            this.loading = true;
            const params = new URLSearchParams({
                category: this.category,
                search: this.search
            });
            const endpoint = this.resolveUrl(`/api/items?${params}`);
            fetch(endpoint)
                .then(res => res.json())
                .then(data => {
                    this.items = data;
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Fetch items error:', error);
                    this.loading = false;
                });
        },

        getDiscountRate(tagColor) {
            return this.discounts[tagColor.toLowerCase()] || 0;
        },

        addToCart(item) {
            if (item.status !== 'available') return;
            
            // Check if item already in cart (since each thrift item is unique)
            if (this.cart.some(i => i.id === item.id)) {
                this.showToast('Item already in cart', 'error');
                return;
            }

            const discountRate = this.getDiscountRate(item.tag_color);
            const discountAmount = item.price * discountRate;
            const finalPrice = item.price - discountAmount;

            this.cart.push({
                ...item,
                discount: discountAmount,
                final_price: finalPrice
            });
            this.showToast('Added to cart');
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        cartTotal() {
            const subtotal = this.cart.reduce((sum, item) => sum + parseFloat(item.price), 0);
            const discount = this.cart.reduce((sum, item) => sum + parseFloat(item.discount), 0);
            const total = subtotal - discount;
            return {
                subtotal: subtotal.toFixed(2),
                discount: discount.toFixed(2),
                total: total.toFixed(2)
            };
        },

        getMaribankQrPreview() {
            if (this.selectedEpaymentMethod !== 'maribank') {
                return null;
            }
            return this.maribankQrImage;
        },

        formatCardNumber(event) {
            let cleaned = event.target.value.replace(/\D/g, '').slice(0, 16);
            const groups = cleaned.match(/.{1,4}/g);
            this.cardNumber = groups ? groups.join(' ') : cleaned;
            event.target.value = this.cardNumber;
        },

        formatCardExpiry(event) {
            let cleaned = event.target.value.replace(/\D/g, '').slice(0, 4);
            if (cleaned.length > 2) {
                cleaned = cleaned.slice(0, 2) + '/' + cleaned.slice(2);
            }
            this.cardExpiry = cleaned;
            event.target.value = this.cardExpiry;
        },

        openPaymentModal(type) {
            this.paymentModal = type;
            this.cashReceived = '';
            this.change = 0;
            this.selectedEpaymentCategory = 'ewallet';
            this.selectedEpaymentMethod = null;
            this.qrCodeUrl = null;
            this.checkoutUrl = null;
            this.paymentStatus = 'idle';
            this.paymentResult = null;
            this.paymentLoading = false;
            this.transactionId = null;
            this.cardHolderName = '';
            this.cardNumber = '';
            this.cardCvv = '';
            this.cardExpiry = '';
            this.isBpi = false;
            this.paymentIntentId = null;
            this.sourceId = null;
        },

        closePaymentModal() {
            this.paymentModal = null;
            this.selectedEpaymentCategory = 'ewallet';
            this.selectedEpaymentMethod = null;
            this.qrCodeUrl = null;
            this.checkoutUrl = null;
            this.paymentStatus = 'idle';
            this.paymentResult = null;
            this.paymentLoading = false;
            this.paymentIntentId = null;
            this.sourceId = null;
        },

        openReservationModal(item) {
            console.log('Opening reservation modal for item:', item.name);
            this.reservingItem = item;
            this.customerName = '';
            this.contactNumber = '';
            this.notes = '';
            this.durationDays = 1;
            this.reservationModal = true;
        },

        submitReservation() {
            if (!this.reservingItem || !this.customerName) {
                this.showToast('Please enter customer name', 'error');
                return;
            }
            if (!this.contactNumber) {
                this.showToast('Please enter contact number', 'error');
                return;
            }
            const sanitizedContact = this.contactNumber.replace(/\D/g, '');
            if (!/^\d{11}$/.test(sanitizedContact)) {
                this.showToast('Contact number must be exactly 11 digits', 'error');
                return;
            }
            this.durationDays = parseInt(this.durationDays, 10);
            if (isNaN(this.durationDays) || this.durationDays <= 0) {
                this.showToast('Reservation duration must be at least 1 day', 'error');
                return;
            }
            console.log('Submitting reservation for:', this.customerName);
            this.loading = true;
            
            const formData = new FormData();
            formData.append('item_id', this.reservingItem.id);
            formData.append('customer_name', this.customerName);
            formData.append('contact_number', sanitizedContact);
            formData.append('notes', this.notes);
            formData.append('duration_days', this.durationDays);

            const endpoint = this.resolveUrl('/reservations/add');
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.reservationModal = false;
                    this.showToast('Item reserved successfully!', 'success');
                    // Update item status locally in the items array
                    const item = this.items.find(i => i.id == this.reservingItem.id);
                    if (item) {
                        item.status = 'reserved';
                    }
                    // Also refresh items to be sure
                    this.fetchItems();
                } else {
                    this.showToast(data.message || 'Reservation failed', 'error');
                }
            })
            .catch(error => {
                console.error('Reservation error:', error);
                this.loading = false;
                this.showToast('An error occurred. Please try again.', 'error');
            });
        },

        calculateChange() {
            const total = parseFloat(this.cartTotal().total);
            const received = parseFloat(this.cashReceived) || 0;
            this.change = received >= total ? (received - total).toFixed(2) : 0;
        },

        processPayment(method) {
            if (method !== 'cash') {
                this.showToast('Unsupported payment method for direct checkout.', 'error');
                return;
            }

            const payload = {
                items: this.cart,
                total: this.cartTotal().total,
                payment_method: method,
                cash_received: parseFloat(this.cashReceived),
                change: parseFloat(this.change)
            };

            const endpoint = this.resolveUrl('/api/checkout');
            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.lastSaleId = data.sale_id;
                    this.receiptPaymentMethod = this.formatPaymentLabel(method);
                    this.paymentModal = null;
                    this.receiptModal = true;
                    this.showToast('Transaction completed!', 'success');
                } else {
                    this.showToast(data.message || 'Payment failed. Please check the items and try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Payment error:', error);
                this.showToast('Payment failed. Please try again.', 'error');
            });
        },

        submitEpayment(moduleType) {
            if (!this.selectedEpaymentMethod) {
                this.showToast('Select an E-Payment method first.', 'error');
                return;
            }

            this.paymentLoading = true;
            const payload = {
                module_type: moduleType,
                module_id: null,
                payment_channel: this.selectedEpaymentMethod,
                amount: parseFloat(this.cartTotal().total),
                items: this.cart,
            };

            const endpoint = this.resolveUrl('/api/paymongo/create');
            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                this.paymentLoading = false;
                if (data.success) {
                    this.transactionId = data.transaction_id;
                    this.lastSaleId = data.sale_id || this.lastSaleId;
                    this.checkoutUrl = data.checkout_url || null;
                    this.qrCodeUrl = data.qr_data;
                    this.isBpi = data.is_bpi || false;
                    this.paymentResult = 'pending';
                    this.showToast('E-Payment created. Staff will process manually.', 'success');
                } else {
                    this.showToast(data.message || 'Unable to create payment.', 'error');
                }
            })
            .catch(error => {
                this.paymentLoading = false;
                console.error('E-Payment error:', error);
                this.showToast('Unable to create payment. Please try again.', 'error');
            });
        },

        markAsPaid() {
            if (!this.transactionId) {
                this.showToast('No payment to mark.', 'error');
                return;
            }

            this.paymentLoading = true;
            fetch(this.resolveUrl('/api/paymongo/mark-paid'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ transaction_id: this.transactionId })
            })
            .then(res => res.json())
            .then(data => {
                this.paymentLoading = false;
                if (data.success) {
                    this.paymentResult = 'paid';
                    this.receiptPaymentMethod = this.formatPaymentLabel(this.selectedEpaymentMethod || 'e-payment');
                    this.showToast('Payment marked as paid.', 'success');
                    setTimeout(() => {
                        this.paymentModal = null;
                        this.receiptModal = true;
                    }, 1000);
                } else {
                    this.showToast(data.message || 'Failed to mark as paid.', 'error');
                }
            })
            .catch(error => {
                this.paymentLoading = false;
                console.error('Mark as paid error:', error);
                this.showToast('Failed to mark as paid.', 'error');
            });
        },

        cancelPayment() {
            this.paymentResult = null;
            this.transactionId = null;
            this.qrCodeUrl = null;
            this.checkoutUrl = null;
            this.cardHolderName = '';
            this.cardNumber = '';
            this.cardCvv = '';
            this.cardExpiry = '';
            this.showToast('Payment cancelled.', 'error');
        },

        resetPos() {
            this.cart = [];
            this.receiptModal = false;
            this.fetchItems();
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `px-5 py-2.5 rounded-lg shadow-xl text-white font-bold mb-3 transform transition-all duration-300 translate-y-10 opacity-0 text-xs ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
            toast.innerHTML = `<div class="flex items-center gap-2"><i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>${message}</div>`;
            
            document.getElementById('toast-container').appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-y-10', 'opacity-0');
            }, 100);

            setTimeout(() => {
                toast.classList.add('translate-y-10', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    };
}
</script>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    
    .scrollbar-thin::-webkit-scrollbar { width: 4px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 10px; }
    .dark .scrollbar-thin::-webkit-scrollbar-thumb { background: #374151; }

    @keyframes scale-in {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .scale-in { animation: scale-in 0.2s ease-out forwards; }

    @media print {
        body * { visibility: hidden; }
        #receipt-content, #receipt-content * { visibility: visible; }
        #receipt-content { position: absolute; left: 0; top: 0; width: 100%; }
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
