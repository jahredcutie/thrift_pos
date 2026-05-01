<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php $users = $users ?? []; ?>

<div x-data="usersApp()" class="flex min-h-screen" :class="{ 'dark': darkMode }">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <main :class="sidebarOpen ? 'ml-64' : 'ml-20 md:ml-64'" class="flex-1 bg-background min-h-screen p-4 md:p-8 transition-all duration-300">
        <header class="mb-6 md:mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-xl md:text-3xl font-extrabold text-primary tracking-tight">User Management</h1>
            </div>
            <button @click="editMode = false; currentUser = {}; showModal = true" 
                class="w-full md:w-auto bg-accent text-white px-4 md:px-6 py-2.5 md:py-3 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all shadow-sm flex items-center justify-center gap-2">
                <i class="fa-solid fa-user-plus"></i>
                Create New Account
            </button>
        </header>

        <!-- Users Table / Cards -->
        <div class="bg-surface rounded-xl border border-border overflow-hidden shadow-sm">
            <!-- Desktop Table -->
            <div class="hidden md:block">
                <table class="w-full">
                    <thead>
                        <tr class="bg-background border-b border-border">
                            <th class="px-6 py-4 text-left text-xs font-bold text-secondary uppercase tracking-wider">Username</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-secondary uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-secondary uppercase tracking-wider">Created At</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-secondary uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <template x-for="user in users" :key="user.id">
                        <tr class="hover:bg-background/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center text-accent font-bold text-sm">
                                        <span x-text="user.username ? user.username.substring(0, 2).toUpperCase() : 'US'"></span>
                                    </div>
                                    <span class="text-sm font-bold text-primary" x-text="user.username"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-secondary font-medium capitalize" x-text="user.role"></td>
                            <td class="px-6 py-4 text-secondary text-sm" x-text="new Date(user.created_at).toLocaleDateString()"></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button @click="editItem(user)" 
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-background text-secondary hover:text-accent border border-border hover:border-accent/30 transition-all">
                                        <i class="fa-solid fa-user-pen text-xs"></i>
                                    </button>
                                    <template x-if="user.id != <?php echo $_SESSION['user_id']; ?>">
                                    <form action="<?php echo $base_url; ?>/users/delete" method="POST" onsubmit="return confirm('Delete this account?')">
                                        <input type="hidden" name="id" :value="user.id">
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-background text-secondary hover:text-red-600 border border-border hover:border-red-100 transition-all">
                                            <i class="fa-solid fa-user-minus text-xs"></i>
                                        </button>
                                    </form>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Cards -->
            <div class="md:hidden divide-y divide-border">
                <template x-for="user in users" :key="user.id">
                    <div class="p-4 flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center text-accent font-bold text-base flex-shrink-0">
                                <span x-text="user.username ? user.username.substring(0, 2).toUpperCase() : 'US'"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-primary text-sm truncate" x-text="user.username"></p>
                                <p class="text-xs text-secondary capitalize" x-text="user.role"></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-secondary" x-text="'Created: ' + new Date(user.created_at).toLocaleDateString()"></p>
                            <div class="flex items-center gap-2">
                                <button @click="editItem(user)" 
                                    class="w-9 h-9 flex items-center justify-center rounded-lg bg-background text-secondary hover:text-accent border border-border hover:border-accent/30 transition-all">
                                    <i class="fa-solid fa-user-pen text-base"></i>
                                </button>
                                <template x-if="user.id != <?php echo $_SESSION['user_id']; ?>">
                                <form action="<?php echo $base_url; ?>/users/delete" method="POST" onsubmit="return confirm('Delete this account?')">
                                    <input type="hidden" name="id" :value="user.id">
                                    <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-lg bg-background text-secondary hover:text-red-600 border border-border hover:border-red-100 transition-all">
                                        <i class="fa-solid fa-user-minus text-base"></i>
                                    </button>
                                </form>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="users.length === 0" class="p-8 md:p-12 text-center text-secondary/30">
                <i class="fa-solid fa-users text-3xl md:text-4xl mb-3 block"></i>
                <p class="text-sm font-medium">No users yet</p>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-primary/40 backdrop-blur-sm">
            <div @click.away="showModal = false" class="bg-surface w-full max-w-md rounded-xl overflow-hidden shadow-xl scale-in border border-border">
                <div class="p-6 border-b border-border flex items-center justify-between">
                    <h3 class="text-lg font-bold text-primary" x-text="editMode ? 'Edit Account' : 'Create Account'"></h3>
                    <button @click="showModal = false" class="text-secondary hover:text-primary transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <form :action="editMode ? '<?php echo $base_url; ?>/users/update' : '<?php echo $base_url; ?>/users/add'" method="POST" class="p-6 md:p-8 space-y-5">
                    <input type="hidden" name="id" :value="currentUser.id">
                    
                    <div>
                        <label class="block text-xs font-bold text-secondary mb-1.5 uppercase tracking-widest">Username</label>
                        <input type="text" name="username" :value="currentUser.username" required
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-secondary mb-1.5 uppercase tracking-widest">Password <span x-show="editMode" class="text-[10px] font-normal text-secondary/40">(Leave blank to keep current)</span></label>
                        <input type="password" name="password" :required="!editMode"
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg text-sm font-medium outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-secondary mb-1.5 uppercase tracking-widest">Role</label>
                        <select name="role" required
                            class="w-full px-4 py-2.5 bg-background border border-border focus:ring-1 focus:ring-accent focus:border-accent rounded-lg outline-none transition-all appearance-none text-sm font-medium">
                            <option value="staff" :selected="currentUser.role === 'staff'">Staff (Cashier)</option>
                            <option value="admin" :selected="currentUser.role === 'admin'">Admin (Owner)</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full bg-accent text-white py-3 rounded-lg font-bold text-sm hover:bg-accent-hover transition-all shadow-sm">
                        <span x-text="editMode ? 'Save Changes' : 'Create Account'"></span>
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
function usersApp() {
    return {
        darkMode: localStorage.getItem('darkMode') === 'true', 
        showModal: false,
        editMode: false,
        currentUser: {},
        sidebarOpen: localStorage.getItem('sidebarOpen') === 'true',
        users: <?php echo json_encode($users ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
        init() {
            this.$watch('darkMode', val => localStorage.setItem('darkMode', val));
            this.$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val));
            window.addEventListener('darkModeChanged', (e) => {
                this.darkMode = e.detail;
            });
        },
        editItem(user) {
            this.editMode = true;
            this.currentUser = {...user};
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