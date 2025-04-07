<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Vehicle Price</div>

                    <div class="card-body">
                        <div class="form-group">
                            <label for="vehicle_type">Tipo de Veículo</label>
                            <select class="form-control" id="vehicle_type" v-model="vehicleType">
                                <option value="">Selecione...</option>
                                <option value="carro">Carro</option>
                                <option value="moto">Moto</option>
                                <option value="caminhonete">Caminhonete</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="monthly_fee">Valor Mensal</label>
                            <input type="text" class="form-control" id="monthly_fee" v-model="monthlyFee" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            vehicleType: '',
            monthlyFee: 0
        }
    },

    mounted() {
        console.log('Component VehiclePrice mounted.');

        // Inicializar Pusher
        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            encrypted: true
        });

        // Inscrever-se no canal de preços
        const channel = pusher.subscribe('vehicle-prices');
        
        // Ouvir eventos de atualização de preço
        channel.bind('price.updated', (data) => {
            console.log('Preço atualizado via WebSocket:', data);
            
            if (data.type === this.vehicleType) {
                this.monthlyFee = parseFloat(data.price).toFixed(2);
            }
        });
    },

    watch: {
        vehicleType(newType) {
            console.log('Tipo de veículo alterado:', newType);
            
            if (newType) {
                const url = `{{ route('get-vehicle-price', ['type' => '']) }}${newType}`;
                console.log('URL da requisição:', url);
                
                axios.get(url)
                    .then(response => {
                        console.log('Resposta do servidor:', response.data);
                        if (response.data.price) {
                            this.monthlyFee = parseFloat(response.data.price).toFixed(2);
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar preço:', error);
                    });
            }
        }
    }
}
</script> 