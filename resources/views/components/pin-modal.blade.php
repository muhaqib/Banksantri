@props([
    'title' => 'Verifikasi PIN',
    'action' => route('verify-pin'),
    'santriName' => null,
    'santriBalance' => null
])

<div x-data="pinModal()" 
     x-show="isOpen" 
     style="display: none;"
     class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 transform transition-all">
            <!-- Header -->
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>
                @if($santriName)
                    <p class="text-sm text-gray-500 mt-1">{{ $santriName }}</p>
                @endif
                @if($santriBalance !== null)
                    <p class="text-lg font-semibold text-emerald-600 mt-2">
                        Saldo: Rp {{ number_format($santriBalance, 0, ',', '.') }}
                    </p>
                @endif
            </div>

            <!-- PIN Input -->
            <form :action="action" method="POST">
                @csrf
                <input type="hidden" name="nominal" :value="nominal">
                <input type="hidden" name="kategori" :value="kategori">
                <input type="hidden" name="keterangan" :value="keterangan">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Masukkan PIN 6 Digit
                    </label>
                    <div class="flex justify-center space-x-2">
                        <template x-for="(digit, index) in 6">
                            <input type="password" 
                                   :ref="'pin' + index"
                                   x-model="pin[index]"
                                   @input="handleInput($event, index)"
                                   @keydown="handleKeydown($event, index)"
                                   maxlength="1"
                                   class="w-12 h-12 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition-all">
                        </template>
                    </div>
                    <p x-show="error" x-text="error" class="text-red-500 text-sm mt-2 text-center"></p>
                </div>

                <!-- Numeric Keypad -->
                <div class="grid grid-cols-3 gap-3 mb-6">
                    <template x-for="num in 9">
                        <button type="button" 
                                @click="addNumber(num)"
                                class="h-14 text-xl font-semibold bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                                x-text="num">
                        </button>
                    </template>
                    <button type="button" class="h-14 text-xl font-semibold bg-gray-100 hover:bg-gray-200 rounded-lg">
                    </button>
                    <button type="button" 
                            @click="addNumber(0)"
                            class="h-14 text-xl font-semibold bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        0
                    </button>
                    <button type="button" 
                            @click="clearPin"
                            class="h-14 text-lg font-semibold bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition-colors">
                        Clear
                    </button>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3">
                    <button type="button" 
                            @click="closeModal"
                            class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            :disabled="pin.length !== 6"
                            class="flex-1 px-4 py-3 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        Verifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function pinModal() {
    return {
        isOpen: false,
        pin: [],
        error: '',
        nominal: '',
        kategori: '',
        keterangan: '',
        action: '{{ $action }}',
        
        openModal(data = {}) {
            this.nominal = data.nominal || '';
            this.kategori = data.kategori || '';
            this.keterangan = data.keterangan || '';
            this.pin = [];
            this.error = '';
            this.isOpen = true;
            this.$nextTick(() => {
                this.$refs.pin0?.focus();
            });
        },
        
        closeModal() {
            this.isOpen = false;
            this.pin = [];
            this.error = '';
        },
        
        addNumber(num) {
            if (this.pin.length < 6) {
                this.pin.push(num.toString());
                this.$nextTick(() => {
                    const nextIndex = this.pin.length - 1;
                    this.$refs['pin' + nextIndex]?.focus();
                });
            }
        },
        
        clearPin() {
            this.pin = [];
            this.$nextTick(() => {
                this.$refs.pin0?.focus();
            });
        },
        
        handleInput(event, index) {
            const value = event.target.value;
            if (value && !this.pin.includes(value)) {
                this.pin[index] = value;
            }
            if (this.pin.length < 6) {
                this.$refs['pin' + this.pin.length]?.focus();
            }
        },
        
        handleKeydown(event, index) {
            if (event.key === 'Backspace' && !this.pin[index]) {
                if (index > 0) {
                    this.pin.pop();
                    this.$nextTick(() => {
                        this.$refs['pin' + (index - 1)]?.focus();
                    });
                }
            }
        }
    }
}
</script>
