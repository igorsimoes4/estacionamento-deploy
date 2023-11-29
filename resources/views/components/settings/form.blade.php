<form action="{{ route($route) }}" class="form-horizontal" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="nomeDaEmpresa">Nome da Empresa</label>
                <input type="text" placeholder="Digite o Nome da Empresa" name="nomeDaEmpresa" id="nomeDaEmpresa" class="form-control @error('nome_da_Empresa') is-invalid @enderror" value="{{ $estacionamento->nome_da_Empresa }}" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="cep">CEP</label>
                <input type="text" placeholder="Digite o CEP" name="cep" id="cep" class="form-control @error('cep') is-invalid @enderror" value="{{ $estacionamento->cep }}" />
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="endereco">Endereço</label>
                <input type="text" placeholder="Digite o Endereço da Empresa" name="endereco" id="endereco" class="form-control @error('endereco') is-invalid @enderror" value="{{ $estacionamento->endereco }}" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="cidade">Cidade</label>
                <input type="text" placeholder="Digite a Cidade" name="cidade" id="cidade" class="form-control @error('cidade') is-invalid @enderror" value="{{ $estacionamento->cidade }}" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="estado">Estado</label>
                <input type="text" placeholder="Digite o Estado" name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" value="{{ $estacionamento->estado }}" />
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="telefoneDaEmpresa">Telefone da Empresa</label>
                <input type="text" placeholder="Digite o Telefone" name="telefone_da_empresa" id="telefone_da_empresa" class="form-control @error('telefone_da_empresa') is-invalid @enderror" value="{{ $estacionamento->telefone_da_empresa }}" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="emailDaEmpresa">Email da Empresa</label>
                <input type="email" placeholder="Digite o Email da Empresa" name="email_da_empresa" id="email_da_empresa" class="form-control @error('email_da_empresa') is-invalid @enderror" value="{{ $estacionamento->email_da_empresa }}" />
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="numeroDeRegistroDaEmpresa">Número de Registro da Empresa</label>
                <input type="text" placeholder="Digite o Número de Registro da Empresa" name="numero_de_registro_da_Empresa" id="numero_de_registro_da_Empresa" class="form-control @error('numero_de_registro_da_Empresa') is-invalid @enderror" value="{{ $estacionamento->numero_de_registro_da_Empresa }}" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="cnpjCpfDaEmpresa">CNPJ da Empresa</label>
                <input type="text" placeholder="Digite o CNPJ da Empresa" name="cnpj_Cpf_da_empresa" id="cnpj_Cpf_da_empresa" class="form-control @error('cnpj_Cpf_da_empresa') is-invalid @enderror" value="{{ $estacionamento->cnpj_Cpf_da_empresa }}" />
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="descricaoDaEmpresa">Descrição da Empresa</label>
                <textarea name="descricao_da_empresa" placeholder="Digite uma Descrição para Empresa" id="descricao_da_empresa" class="form-control @error('descricao_da_empresa') is-invalid @enderror">{{ $estacionamento->descricao_da_empresa }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="coordenadasGps">Coordenadas de GPS</label>
                <input type="text" placeholder="Digite as Coordenadas de GPS" name="coordenadas_gps" id="coordenadas_gps" class="form-control @error('coordenadas_gps') is-invalid @enderror" value="{{ $estacionamento->coordenadas_gps }}" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-md-9"></div>
            <div class="col-md-3">
                <button class="btn btn-success btn-block">
                    <i style="margin-right: 5px; font-size: 15px;" class="fa fa-save" aria-hidden="true"></i> Salvar
                </button>
            </div>
        </div>

    </div>
</form>
