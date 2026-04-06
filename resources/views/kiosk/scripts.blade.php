@push('scripts')
<script>
    document.getElementById('check-payment').addEventListener('click', function() {
        const printerCode = this.dataset.printerCode;
        const statusEl = document.getElementById('payment-status');
        
        statusEl.textContent = 'Checking...';
        statusEl.className = 'alert alert-warning';
        
        fetch('/kiosk/' + printerCode + '/payment/check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                statusEl.textContent = 'Payment successful! Redirecting...';
                statusEl.className = 'alert alert-success';
                window.location.href = data.redirect;
            } else if (data.status === 'expired') {
                statusEl.textContent = 'Payment expired. Please start over.';
                statusEl.className = 'alert alert-danger';
            } else {
                statusEl.textContent = 'Payment not yet received. Keep checking...';
                statusEl.className = 'alert alert-info';
            }
        })
        .catch(err => {
            statusEl.textContent = 'Error checking payment. Try again.';
            statusEl.className = 'alert alert-danger';
        });
    });

    setInterval(() => {
        document.getElementById('check-payment').click();
    }, 10000);
</script>
@endpush