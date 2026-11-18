document.addEventListener('DOMContentLoaded', function() {
    const formProducto = document.getElementById('formProducto');
    if (formProducto) {
        formProducto.addEventListener('submit', function(e) {
            let esValido = true;
            let msgs = [];
            const codigo = document.getElementById('codigo');
            const precio = document.getElementById('precio');
            const stockActual = document.getElementById('stock_actual');

            if (codigo && codigo.value.trim().length < 3) {
                esValido = false;
                msgs.push("El código debe tener al menos 3 caracteres.");
            }
            if (precio && (isNaN(precio.value) || parseFloat(precio.value) < 0)) {
                esValido = false;
                msgs.push("El precio no puede ser negativo.");
            }
            if (stockActual && (isNaN(stockActual.value) || parseInt(stockActual.value) < 0)) {
                esValido = false;
                msgs.push("El stock actual no puede ser negativo.");
            }
            if (!esValido) {
                e.preventDefault();
                alert("Error de validación:\n" + msgs.join("\n"));
            }
        });
    }

    const formMovimiento = document.getElementById('formMovimiento');
    if (formMovimiento) {
        formMovimiento.addEventListener('submit', function(e) {
            const tipo = document.getElementById('tipo').value;
            const cantidad = parseInt(document.getElementById('cantidad').value || '0');
            const prodSelect = document.getElementById('id_producto');
            
            if (tipo === 'SALIDA' && prodSelect.selectedIndex >= 0) {
                const stockDisponible = parseInt(prodSelect.options[prodSelect.selectedIndex].getAttribute('data-stock') || '0');
                if (cantidad > stockDisponible) {
                    e.preventDefault();
                    alert(`No puede realizar la salida. Stock disponible (${stockDisponible}) es inferior a la cantidad solicitada (${cantidad}).`);
                }
            }
        });
    }
});