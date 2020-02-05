@php
if (!isset($name)) $name = $column;
if (!isset($id)) $id = $name;
if (!isset($inputOptions)) $inputOptions = [];
if (!isset($errorName)) $errorName = $name;
if (!isset($data)) {
    $data = null;
} else {
    $data->prepend('Seleccione ...', '');
}
//$newData = $data;
@endphp
{{ html()->div()->attributes(['class' => 'form-group'])->open() }}
<div class="form-group">
    {{ html()->element('label')->attributes(['for' => $id])->text($label) }}
    @if(isset($model))
        {{
            html()
                ->select($name, $data, (old($name) ?? $model->{$column}))
                ->attributes(array_merge([
                    'id' => $id,
                    'class' => 'form-control' . ($errors->has($errorName) ? ' is-invalid' : '')
                ], $inputOptions))
        }}
    @else
        {{
            html()
                ->select($name, $data, (old($name) ?? $value))
                ->attributes(array_merge([
                    'id' => $id,
                    'class' => 'form-control' . ($errors->has($errorName) ? ' is-invalid' : '')
                ], $inputOptions))
        }}
    @endif
    @error($errorName)
        {{ html()->div($message)->attributes(['class' => 'invalid-feedback d-block']) }}
    @enderror
</div>
{{ html()->div()->close() }}