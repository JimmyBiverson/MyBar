<div class="modal fade" id="paymentModal" tabindex="-1" x-data="paymentModalData()">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Process Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <small class="text-muted">Total Amount</small>
                    <h2 class="fw-bold text-primary" x-text="$parent.formatCurrency($parent.total)"></h2>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Payment Method</label>
                    <div class="payment-methods">
                        <input type="radio" class="btn-check" name="paymentMethod" id="methodCash" value="cash" x-model="paymentMethod" checked>
                        <label class="btn btn-outline-primary" for="methodCash"><i class="fas fa-money-bill me-1"></i> Cash</label>

                        <input type="radio" class="btn-check" name="paymentMethod" id="methodMobile" value="mobile_money" x-model="paymentMethod">
                        <label class="btn btn-outline-primary" for="methodMobile"><i class="fas fa-mobile-screen me-1"></i> Mobile Money</label>

                        <input type="radio" class="btn-check" name="paymentMethod" id="methodCard" value="card" x-model="paymentMethod">
                        <label class="btn btn-outline-primary" for="methodCard"><i class="fas fa-credit-card me-1"></i> Card</label>
                    </div>
                </div>

                <template x-if="paymentMethod === 'mobile_money'">
                    <div>
                        <div class="mb-3">
                            <label class="form-label small">Mobile Money Provider</label>
                            <select class="form-select" x-model="mobileProvider">
                                <option value="mtn">MTN Mobile Money</option>
                                <option value="airtel">Airtel Money</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Reference Number</label>
                            <input type="text" class="form-control" x-model="paymentReference" placeholder="Transaction reference (e.g. 1234567890)" required>
                        </div>
                    </div>
                </template>

                <div class="mb-3">
                    <label class="form-label small">Amount Received</label>
                    <div class="input-group">
                        <span class="input-group-text">{{ session('currency_symbol', 'UGX') }}</span>
                        <input type="number" class="form-control form-control-lg" x-model="amountReceived" min="0" step="any" placeholder="0.00">
                    </div>
                </div>

                <div class="d-flex justify-content-between py-2 px-3 rounded" style="background:#f8f9fa;">
                    <span class="fw-semibold">Change Due</span>
                    <span class="fw-bold fs-5" :class="change >= 0 ? 'text-success' : 'text-danger'" x-text="formatCurrency(change)"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" @click="submitPayment()" :disabled="amountReceived < total || (paymentMethod === 'mobile_money' && !paymentReference.trim())">
                    <i class="fas fa-check me-1"></i> Complete Payment
                </button>
            </div>
        </div>
    </div>

    <script>
        function paymentModalData() {
            return {
                paymentMethod: 'cash',
                mobileProvider: 'mtn',
                paymentReference: '',
                amountReceived: 0,
                init() {
                    this.$el.addEventListener('show.bs.modal', () => {
                        this.amountReceived = this.total;
                        this.paymentReference = '';
                    });
                },
                get total() {
                    return this.$parent ? this.$parent.total : 0;
                },
                get change() {
                    return parseFloat(this.amountReceived || 0) - this.total;
                },
                formatCurrency(val) {
                    const s = window.currencySettings || { symbol: 'UGX', position: 'before', thousand_separator: ',', decimal_separator: '.', decimal_digits: 0 };
                    const formatted = Number(val).toFixed(s.decimal_digits || 0).replace(/\B(?=(\d{3})+(?!\d))/g, s.thousand_separator || ',');
                    return s.position === 'before' ? s.symbol + ' ' + formatted : formatted + ' ' + s.symbol;
                },
                submitPayment() {
                    if (this.$parent) {
                        this.$parent.processPayment(this.paymentMethod, this.amountReceived, this.mobileProvider, this.paymentReference);
                        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                    }
                }
            }
        }
    </script>

    <style>
        .payment-methods {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .payment-methods .btn {
            flex: 1;
            min-width: 100px;
            white-space: nowrap;
        }
        .dark .modal-body .rounded { background: #1e2126 !important; }
        .payment-methods .btn-outline-primary { border-color: #dee2e6; }
        .btn-check:checked + .btn-outline-primary { background: #7367f0; border-color: #7367f0; color: #fff; }
        @media (max-width: 400px) {
            .payment-methods .btn { min-width: 100%; }
        }
    </style>
</div>
