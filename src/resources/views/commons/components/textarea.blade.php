@php
if (!isset($name)) $name = $column;
if (!isset($id)) $id = $name;
if (!isset($inputOptions)) $inputOptions = [];
if (!isset($errorName)) $errorName = $name;
if (!isset($type)) $type = 'text';

if (isset($inputOptions['required']) && !$inputOptions['required']) {
    unset($inputOptions['required']);
}
@endphp
{{ html()->div()->attributes(['class' => 'form-group'])->open() }}
    {{ html()->element('label')->attributes(['for' => $id])->text($label) }}
    @if(isset($model))
        {{
            html()
                ->textarea($name, (old($name) ?? $model->{$column}))
                ->attributes(array_merge([
                    'id' => $id,
                    'class' => 'form-control' . ($errors->has($errorName) ? ' is-invalid' : '')
                ], $inputOptions))
        }}
    @else
        {{
            html()
                ->textarea($name, (old($name) ?? $value))
                ->attributes(array_merge([
                    'id' => $id,
                    'class' => 'form-control' . ($errors->has($errorName) ? ' is-invalid' : '')
                ], $inputOptions))
        }}
    @endif
    @error($errorName)
        {{ html()->div($message)->attributes(['class' => 'invalid-feedback d-block']) }}
    @enderror
{{ html()->div()->close() }}