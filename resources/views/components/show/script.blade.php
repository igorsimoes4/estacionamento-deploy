<script>
    const myModal = document.getElementById('myModal');
    const modeloInput = document.querySelector('#modelo');
    const placaInput = document.querySelector('#placa');
    const precoInput = document.querySelector('#preco');
    const entradaInput = document.querySelector('#entrada');
    const tempoInput = document.querySelector('#tempo');

    document.querySelectorAll('.modal-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            const carId = this.getAttribute('data-id');
            const url = window.location.origin + '/painel/cars/showmodal/' + carId;

            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) {
                    return;
                }

                if (xhr.status === 200) {
                    const carData = JSON.parse(xhr.responseText);

                    modeloInput.value = carData.modelo;
                    placaInput.value = carData.placa;
                    precoInput.value = 'R$ ' + carData.price;
                    entradaInput.value = carData.entrada;
                    tempoInput.value =
                        (carData.mesT >= 1 ? carData.mesT + ' meses ' : '') +
                        (carData.diaT > 1 ? carData.diaT + ' dias ' : carData.diaT === 1 ? carData.diaT + ' dia ' : '') +
                        (carData.horaT >= 1 ? carData.horaT + ' horas ' : '') +
                        (carData.minutoT >= 1 ? carData.minutoT + ' minutos' : '1 minuto');

                    if (window.$ && typeof window.$.fn.modal === 'function') {
                        window.$('#myModal').modal('show');
                    } else {
                        myModal.style.display = 'block';
                    }
                }
            };
            xhr.send();
        });
    });
</script>
