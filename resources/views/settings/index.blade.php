@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div x-data="{ activeTab: '{{ auth()->user()->isManager() ? 'inventory' : 'general' }}' }">
    <ul class="nav nav-tabs mb-4" role="tablist">
        @if(!auth()->user()->isManager())
        <li class="nav-item" role="presentation">
            <button class="nav-link" :class="{ active: activeTab === 'general' }" @click="activeTab = 'general'" type="button">
                <i class="fas fa-sliders-h me-1"></i> General
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" :class="{ active: activeTab === 'branding' }" @click="activeTab = 'branding'" type="button">
                <i class="fas fa-palette me-1"></i> Branding & Theme
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" :class="{ active: activeTab === 'billing' }" @click="activeTab = 'billing'" type="button">
                <i class="fas fa-receipt me-1"></i> Billing
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" :class="{ active: activeTab === 'receipt' }" @click="activeTab = 'receipt'" type="button">
                <i class="fas fa-print me-1"></i> Receipt
            </button>
        </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link" :class="{ active: activeTab === 'inventory' }" @click="activeTab = 'inventory'" type="button">
                <i class="fas fa-box me-1"></i> Inventory
            </button>
        </li>
    </ul>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
        @csrf

        <div x-show="activeTab === 'general'" x-transition:enter="fade-in">
            <div class="card">
                <div class="card-header">General Settings</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Business Name</label>
                            <input type="text" class="form-control" name="business_name" value="{{ old('business_name', $settings['business_name'] ?? config('app.name')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Business Email</label>
                            <input type="email" class="form-control" name="business_email" value="{{ old('business_email', $settings['business_email'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Business Phone</label>
                            <input type="text" class="form-control" name="business_phone" value="{{ old('business_phone', $settings['business_phone'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Business Address</label>
                            <input type="text" class="form-control" name="business_address" value="{{ old('business_address', $settings['business_address'] ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Currency</label>
                            <select class="form-select" name="currency">
                                <option value="UGX" {{ ($settings['currency'] ?? 'UGX') === 'UGX' ? 'selected' : '' }}>UGX - Uganda Shilling</option>
                                <option value="KES" {{ ($settings['currency'] ?? '') === 'KES' ? 'selected' : '' }}>KES - Kenyan Shilling</option>
                                <option value="TZS" {{ ($settings['currency'] ?? '') === 'TZS' ? 'selected' : '' }}>TZS - Tanzanian Shilling</option>
                                <option value="USD" {{ ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Currency Position</label>
                            <select class="form-select" name="currency_position">
                                <option value="before" {{ ($settings['currency_position'] ?? 'before') === 'before' ? 'selected' : '' }}>Before Amount (UGX 1,000)</option>
                                <option value="after" {{ ($settings['currency_position'] ?? '') === 'after' ? 'selected' : '' }}>After Amount (1,000 UGX)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Decimal Digits</label>
                            <input type="number" class="form-control" name="decimal_digits" value="{{ old('decimal_digits', $settings['decimal_digits'] ?? 0) }}" min="0" max="4">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Time Zone</label>
                            <select class="form-select" name="timezone">
                                @foreach(timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" {{ ($settings['timezone'] ?? 'Africa/Kampala') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Language</label>
                            <select class="form-select" name="language">
                                <option value="en" {{ ($settings['language'] ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                                <option value="sw" {{ ($settings['language'] ?? '') === 'sw' ? 'selected' : '' }}>Swahili</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'branding'" x-transition:enter="fade-in">
            <div class="card">
                <div class="card-header">Branding & Theme Settings</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label d-block">Favicon (.ico, .png, max 100kb)</label>
                            <input type="file" class="form-control" name="favicon" accept="image/*">
                            @if(\App\Models\Setting::get('favicon'))
                                <div class="mt-2">
                                    <span class="small text-muted">Current Favicon:</span>
                                    <img src="{{ \App\Models\Setting::get('favicon') }}" alt="favicon" style="height: 24px; width: 24px; object-fit: contain; margin-left: 10px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">Site Logo (max 500kb)</label>
                            <input type="file" class="form-control" name="site_logo" accept="image/*">
                            @if(\App\Models\Setting::get('site_logo'))
                                <div class="mt-2">
                                    <span class="small text-muted">Current Logo:</span>
                                    <img src="{{ \App\Models\Setting::get('site_logo') }}" alt="logo" style="height: 24px; width: 24px; object-fit: contain; margin-left: 10px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Theme Primary Accent Color</label>
                            <input type="color" class="form-control form-control-color w-100" style="height: 38px; padding: 4px;" name="accent_color" value="{{ old('accent_color', $settings['accent_color'] ?? '#7367f0') }}">
                            <small class="text-muted">Changes the site buttons, active menu states, etc.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Theme Primary Accent Color (Dark Hover)</label>
                            <input type="color" class="form-control form-control-color w-100" style="height: 38px; padding: 4px;" name="accent_color_dark" value="{{ old('accent_color_dark', $settings['accent_color_dark'] ?? '#5e50ee') }}">
                            <small class="text-muted">The darker hover color corresponding to the accent color above.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'billing'" x-transition:enter="fade-in">
            <div class="card">
                <div class="card-header">Billing Settings</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tax Rate (%)</label>
                            <input type="number" step="0.01" class="form-control" name="tax_rate" value="{{ old('tax_rate', $settings['tax_rate'] ?? 0) }}" min="0" max="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Service Charge (%)</label>
                            <input type="number" step="0.01" class="form-control" name="service_charge_rate" value="{{ old('service_charge_rate', $settings['service_charge_rate'] ?? 0) }}" min="0" max="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Default Payment Method</label>
                            <select class="form-select" name="default_payment_method">
                                <option value="cash" {{ ($settings['default_payment_method'] ?? 'cash') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="mobile_money" {{ ($settings['default_payment_method'] ?? '') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                <option value="card" {{ ($settings['default_payment_method'] ?? '') === 'card' ? 'selected' : '' }}>Card</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice Prefix</label>
                            <input type="text" class="form-control" name="invoice_prefix" value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? 'INV-') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Invoice Number</label>
                            <input type="number" class="form-control" name="next_invoice_no" value="{{ old('next_invoice_no', $settings['next_invoice_no'] ?? 1001) }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="enable_split_bill" value="1" id="enableSplit" {{ ($settings['enable_split_bill'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enableSplit">Enable Split Bill</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="enable_hold_bill" value="1" id="enableHold" {{ ($settings['enable_hold_bill'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enableHold">Enable Hold Bill</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'receipt'" x-transition:enter="fade-in">
            <div class="card">
                <div class="card-header">Receipt Settings</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Receipt Header</label>
                            <textarea class="form-control" name="receipt_header" rows="2">{{ old('receipt_header', $settings['receipt_header'] ?? 'Thank you for your patronage!') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Receipt Footer</label>
                            <textarea class="form-control" name="receipt_footer" rows="2">{{ old('receipt_footer', $settings['receipt_footer'] ?? 'Goods once sold cannot be returned') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Auto Print Receipt</label>
                            <select class="form-select" name="auto_print_receipt">
                                <option value="1" {{ ($settings['auto_print_receipt'] ?? true) ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !($settings['auto_print_receipt'] ?? true) ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thermal Printer Width (mm)</label>
                            <input type="number" class="form-control" name="thermal_width" value="{{ old('thermal_width', $settings['thermal_width'] ?? 80) }}" min="40" max="112">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Show Customer Details</label>
                            <select class="form-select" name="receipt_show_customer">
                                <option value="1" {{ ($settings['receipt_show_customer'] ?? true) ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !($settings['receipt_show_customer'] ?? true) ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Show Barcode</label>
                            <select class="form-select" name="receipt_show_barcode">
                                <option value="1" {{ ($settings['receipt_show_barcode'] ?? false) ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !($settings['receipt_show_barcode'] ?? false) ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="receipt_merge_duplicates" value="1" id="mergeDups" {{ ($settings['receipt_merge_duplicates'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="mergeDups">Merge Duplicate Items on Receipt</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'inventory'" x-transition:enter="fade-in">
            <div class="card">
                <div class="card-header">Inventory Settings</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Low Stock Threshold</label>
                            <input type="number" class="form-control" name="low_stock_threshold" value="{{ old('low_stock_threshold', $settings['low_stock_threshold'] ?? 10) }}" min="0">
                            <input type="hidden" name="group_low_stock_threshold" value="inventory">
                            <small class="text-muted">Stock at or below this is <span class="text-danger fw-medium">Low</span></small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Medium Stock Threshold</label>
                            <input type="number" class="form-control" name="medium_stock_threshold" value="{{ old('medium_stock_threshold', $settings['medium_stock_threshold'] ?? 20) }}" min="0">
                            <input type="hidden" name="group_medium_stock_threshold" value="inventory">
                            <small class="text-muted">Stock up to this is <span class="text-warning fw-medium">Medium</span></small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Default Reorder Level</label>
                            <input type="number" class="form-control" name="default_reorder_level" value="{{ old('default_reorder_level', $settings['default_reorder_level'] ?? 10) }}" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Stock Alert Frequency</label>
                            <select class="form-select" name="stock_alert_frequency">
                                <option value="daily" {{ ($settings['stock_alert_frequency'] ?? 'daily') === 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ ($settings['stock_alert_frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="realtime" {{ ($settings['stock_alert_frequency'] ?? '') === 'realtime' ? 'selected' : '' }}>Real-time</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Enable Batch Tracking</label>
                            <select class="form-select" name="enable_batch_tracking">
                                <option value="1" {{ ($settings['enable_batch_tracking'] ?? false) ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ !($settings['enable_batch_tracking'] ?? false) ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check pt-4">
                                <input type="checkbox" class="form-check-input" name="auto_adjust_stock" value="1" id="autoAdjust" {{ ($settings['auto_adjust_stock'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="autoAdjust">Auto-adjust Stock on Sale</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check pt-4">
                                <input type="checkbox" class="form-check-input" name="negative_stock" value="1" id="negStock" {{ ($settings['negative_stock'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="negStock">Allow Negative Stock</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .fade-in { animation: fadeIn 0.2s ease-in; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .nav-tabs .nav-link { color: #6c757d; font-weight: 500; }
    .nav-tabs .nav-link.active { color: #7367f0; border-bottom: 2px solid #7367f0; }
    .dark .nav-tabs .nav-link { color: #b2b9c5; }
    .dark .nav-tabs .nav-link.active { color: #7367f0; }
    .dark .nav-tabs { border-color: #3a3d45; }
</style>
@endpush
