<script>
    var $myModal = document.getElementById("myModal");
    var $modeloInput = document.querySelector("#modelo");
    var $placaInput = document.querySelector("#placa");
    var $precoInput = document.querySelector("#preco");
    var $entradaInput = document.querySelector("#entrada");
    var $tempoInput = document.querySelector("#tempo");


    // Selecione os botões que abrirão o modal
    var modalButtons = document.querySelectorAll(".modal-btn");

    modalButtons.forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            var carId = this.getAttribute("data-id");
            var url = "https://estacionamento-deploy.vercel.app/painel/cars/showmodal/" + carId;
            // Use uma requisição AJAX (XMLHttpRequest) para buscar os detalhes do carro
            var xhr = new XMLHttpRequest();
            xhr.open("GET", url, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        var carData = JSON.parse(xhr.responseText);
                                                    // Preencha os campos com os dados do carro
                        $modeloInput.value = carData.modelo;
                        $placaInput.value = carData.placa;
                        $precoInput.value = "R$ " + carData.price + ',00';
                        $entradaInput.value = carData.entrada;
                        $tempoInput.value =
                            (carData.mesT >= 1 ? carData.mesT + ' meses ' : '') +
                            (carData.diaT > 1 ? carData.diaT + ' dias ' : carData.diaT == 1 ? carData.diaT + ' dia ' : '') +
                            (carData.horaT >= 1 ?  carData.horaT + ' horas ' : '') +
                            (carData.minutoT >= 1 ? carData.minutoT + ' minutos' : '1 minuto');
                        // if(carData.mesT >= 1) {
                        //     if(carData.diaT > 1) {
                        //         $tempoInput.value = carData.mesT + ' meses ' +  carData.diaT + ' dias ' + carData.horaT + ' horas ' + carData.minutoT + ' minutos';
                        //     } else {
                        //         $tempoInput.value = carData.mesT + ' meses ' +  carData.horaT + ' horas ' + carData.minutoT + ' minutos';
                        //     }
                        // } else {
                        //     $tempoInput.value = carData.diaT + ' dias ' + carData.horaT + ' horas ' + carData.minutoT + ' minutos';
                        // }
                        // Abra o modal
                        $myModal.style.display = "block";
                    } else {
                        console.error("Erro na requisição AJAX:", xhr.statusText);
                        // Adicione aqui um tratamento de erro adequado, como exibir uma mensagem de erro para o usuário.
                    }
                }
            };
            xhr.send();
        });
    });

    // Event listener para fechar o modal quando o usuário clicar no botão "X"
    document.querySelector('.close').addEventListener('click', () => {
        $myModal.style.display = 'none';
    });

</script>
