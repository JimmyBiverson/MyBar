import './bootstrap';
import $ from 'jquery';
import Alpine from 'alpinejs';
import * as bootstrap from 'bootstrap';
import Swal from 'sweetalert2';
import DataTable from 'datatables.net';
import 'datatables.net-bs5';
import Chart from 'chart.js/auto';

window.$ = window.jQuery = $;
window.Alpine = Alpine;
window.bootstrap = bootstrap;
window.Swal = Swal;
window.Chart = Chart;

window.formatCurrency = function(value) {
    const settings = window.currencySettings || { symbol: 'UGX', position: 'before', decimals: 0 };
    const num = parseFloat(value) || 0;
    const formatted = num.toFixed(settings.decimals);
    return settings.position === 'before' ? settings.symbol + ' ' + formatted : formatted + ' ' + settings.symbol;
};

window.cacheAppData = function(products, categories, settings) {
    try {
        if (products) localStorage.setItem('mybar_products', JSON.stringify(products));
        if (categories) localStorage.setItem('mybar_categories', JSON.stringify(categories));
        if (settings) localStorage.setItem('mybar_settings', JSON.stringify(settings));
    } catch (e) {
        console.warn('Failed to cache app data:', e);
    }
};

window.getCachedProducts = function() {
    try {
        const data = localStorage.getItem('mybar_products');
        return data ? JSON.parse(data) : [];
    } catch (e) {
        return [];
    }
};

window.getCachedCategories = function() {
    try {
        const data = localStorage.getItem('mybar_categories');
        return data ? JSON.parse(data) : [];
    } catch (e) {
        return [];
    }
};

window.confirmDelete = function(url) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = url;
            form.submit();
        }
    });
};

document.addEventListener('DOMContentLoaded', function() {
    const isDark = localStorage.getItem('darkMode') === 'true';
    if (isDark) {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
    }

    window.toggleDarkMode = function() {
        const isDark = localStorage.getItem('darkMode') !== 'true';
        localStorage.setItem('darkMode', isDark);
        document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
    };

    function updateOfflineStatus() {
        const banner = document.getElementById('offlineBanner');
        const badge = document.getElementById('offlineBadge');
        if (!navigator.onLine) {
            if (banner) banner.classList.remove('d-none');
            if (badge) badge.classList.remove('d-none');
        } else {
            if (banner) banner.classList.add('d-none');
            if (badge) badge.classList.add('d-none');
        }
    }

    window.addEventListener('online', updateOfflineStatus);
    window.addEventListener('offline', updateOfflineStatus);
    updateOfflineStatus();

    window.cacheAppData();

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
    }
});

function sidebarToggle() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('bodyWrapper').classList.toggle('active');
}
function mobileSidebarOpen() {
    document.getElementById('sidebar').classList.add('open');
}
function mobileSidebarClose() {
    document.getElementById('sidebar').classList.remove('open');
}
document.addEventListener('click', function(e) {
    if (window.innerWidth <= 991) {
        const sidebar = document.getElementById('sidebar');
        if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !e.target.closest('.res-sidebar-open-btn')) {
            sidebar.classList.remove('open');
        }
    }
});

window.sidebarToggle = sidebarToggle;
window.mobileSidebarOpen = mobileSidebarOpen;
window.mobileSidebarClose = mobileSidebarClose;

Alpine.start();
