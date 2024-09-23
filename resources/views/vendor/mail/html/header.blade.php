@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Consultas')
<img src="https://fiap.vagas.solides.com.br/_next/image?url=https%3A%2F%2Fc5gwmsmjx1.execute-api.us-east-1.amazonaws.com%2Fprod%2Fdados_processo_seletivo%2Flogo_empresa%2F124918%2Flogo-420x100px.png_name_20221121-18288-5b9rii.png&w=640&q=75" class="logo" alt="Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
