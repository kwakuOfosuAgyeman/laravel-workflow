<div class="mb-3">
    <label for="type" class="form-label">Step Type</label>
    <select name="type" id="step_type_select" class="form-select @error('type') is-invalid @enderror" required onchange="toggleFields()">
        <option value="">-- Select Type --</option>
        <option value="delay" {{ old('type') == 'delay' ? 'selected' : '' }}>Delay (Wait)</option>
        <option value="http_check" {{ old('type') == 'http_check' ? 'selected' : '' }}>HTTP Health Check</option>
    </select>
    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="step_order" class="form-label">Step Order</label>
    <input type="number" name="step_order" class="form-control @error('step_order') is-invalid @enderror" value="{{ old('step_order', 1) }}">
    @error('step_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div id="config_delay" class="type-fields" style="display: none;">
    <div class="mb-3">
        <label class="form-label">Seconds to Wait</label>
        <input type="number" name="config[seconds]" class="form-control @error('config.seconds') is-invalid @enderror" value="{{ old('config.seconds') }}">
        @error('config.seconds') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div id="config_http" class="type-fields" style="display: none;">
    <div class="mb-3">
        <label class="form-label">Target URL</label>
        <input type="url" name="config[url]" class="form-control @error('config.url') is-invalid @enderror" value="{{ old('config.url') }}" placeholder="https://api.example.com/status">
        @error('config.url') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<script>
    function toggleFields() {
        const type = document.getElementById('step_type_select').value;
        // Hide all
        document.querySelectorAll('.type-fields').forEach(el => el.style.display = 'none');
        // Show selected
        if(type === 'delay') document.getElementById('config_delay').style.display = 'block';
        if(type === 'http_check') document.getElementById('config_http').style.display = 'block';
    }
    // Run on load to handle validation redirects
    window.onload = toggleFields;
</script>
