 const sizeRadios = document.querySelectorAll('input[name="size"]');
            const stockSpan = document.getElementById('stock-available');
            sizeRadios.forEach(r => r.addEventListener('change', () => {
                const stock = r.dataset.stock;
                stockSpan.textContent = stock;
                const qtyInput = document.querySelector('input[name="quantity"]');
                qtyInput.max = stock;
                if (qtyInput.value > stock) qtyInput.value = stock;
            }));