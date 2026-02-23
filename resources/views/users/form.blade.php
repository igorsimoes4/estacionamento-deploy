@php
    $isEdit = isset($user);
@endphp

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        Verifique os campos obrigatorios e tente novamente.
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Nome</label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $user->name ?? '') }}" required maxlength="255">
            @error('name')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $user->email ?? '') }}" required maxlength="255">
            @error('email')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="role">Perfil</label>
            <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
                @foreach ($roles as $roleValue => $label)
                    <option value="{{ $roleValue }}" {{ old('role', $user->role ?? '') === $roleValue ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('role')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="password">{{ $isEdit ? 'Nova senha (opcional)' : 'Senha' }}</label>
            <input type="password" id="password" name="password"
                class="form-control @error('password') is-invalid @enderror" {{ $isEdit ? '' : 'required' }}
                minlength="6">
            @error('password')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="password_confirmation">{{ $isEdit ? 'Confirmar nova senha' : 'Confirmar senha' }}</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                {{ $isEdit ? '' : 'required' }} minlength="6">
        </div>
    </div>
</div>

<div class="form-group">
    <input type="hidden" name="is_active" value="0">
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
            {{ old('is_active', isset($user) ? (int) $user->is_active : 1) ? 'checked' : '' }}>
        <label class="custom-control-label" for="is_active">Usuario ativo para login</label>
    </div>
    @if ($isEdit && auth()->id() === $user->id)
        <small class="text-muted d-block mt-2">Seu proprio usuario nao pode ser inativado nem perder perfil admin.</small>
    @endif
</div>
