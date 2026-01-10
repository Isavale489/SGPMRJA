<script>
    $(document).ready(function () {
        // Inicializar Select2
        $('#insumo_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Seleccione un insumo...',
            width: '100%'
        });

        // Actualizar información al cambiar el insumo
        $('#insumo_id').on('change', function() {
            let option = $(this).find('option:selected');
            $('#stock-disponible').text(option.data('stock') + ' ' + option.data('unidad'));
            $('#unidad-medida').text(option.data('unidad'));
        });

        // DataTable
        var table = $('#insumos-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('ordenes.insumos.data', $orden->id) }}",
            columns: [
                { 
                    data: 'insumo.nombre',
                    name: 'insumo.nombre',
                    className: 'align-middle'
                },
                { 
                    data: 'insumo.tipo',
                    name: 'insumo.tipo',
                    className: 'align-middle'
                },
                { 
                    data: 'insumo.unidad_medida',
                    name: 'insumo.unidad_medida',
                    className: 'align-middle text-center'
                },
                { 
                    data: 'cantidad_estimada',
                    name: 'cantidad_estimada',
                    className: 'align-middle text-end'
                },
                { 
                    data: 'cantidad_utilizada',
                    name: 'cantidad_utilizada',
                    className: 'align-middle text-end'
                },
                { 
                    data: null,
                    className: 'align-middle',
                    render: function(data) {
                        let porcentaje = (data.cantidad_utilizada / data.cantidad_estimada * 100).toFixed(2);
                        let colorClass = porcentaje >= 90 ? 'bg-success' : 
                                       porcentaje >= 60 ? 'bg-info' : 
                                       porcentaje >= 30 ? 'bg-warning' : 'bg-danger';
                        return `<div class="progress" style="height: 15px;">
                            <div class="progress-bar ${colorClass}" role="progressbar" 
                                style="width: ${porcentaje}%" 
                                aria-valuenow="${porcentaje}" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                ${porcentaje}%
                            </div>
                        </div>`;
                    }
                },
                { 
                    data: 'insumo.stock_actual',
                    name: 'insumo.stock_actual',
                    className: 'align-middle text-end',
                    render: function(data, type, row) {
                        let colorClass = data <= row.insumo.stock_minimo ? 'text-danger' : 'text-success';
                        return `<span class="${colorClass}">${data} ${row.insumo.unidad_medida}</span>`;
                    }
                },
                { 
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'align-middle text-center'
                }
            ],
            order: [[0, 'asc']],
            responsive: true,
            language: lenguajeData,
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-5"i><"col-sm-7"p>>'
        });

        // Agregar insumo
        $('#insumoForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('ordenes.insumos.store', $orden->id) }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#showModal').modal('hide');
                    table.ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.success
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON.error || 'Ocurrió un error al procesar la solicitud'
                    });
                }
            });
        });

        // Mostrar modal de actualización
        $(document).on('click', '.update-btn', function() {
            let row = table.row($(this).closest('tr')).data();
            
            $('#update_detalle_id').val(row.id);
            $('#update_insumo_nombre').text(row.insumo.nombre);
            $('#update_cantidad_estimada').text(row.cantidad_estimada + ' ' + row.insumo.unidad_medida);
            $('#update_unidad_medida').text(row.insumo.unidad_medida);
            $('#cantidad_utilizada').val(row.cantidad_utilizada);
            $('#cantidad_utilizada').attr('max', row.cantidad_estimada);

            let porcentaje = (row.cantidad_utilizada / row.cantidad_estimada * 100).toFixed(2);
            $('#update_progress')
                .css('width', porcentaje + '%')
                .attr('aria-valuenow', porcentaje)
                .text(porcentaje + '%');

            $('#updateModal').modal('show');
        });

        // Actualizar uso de insumo
        $('#updateForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#update_detalle_id').val();
            let formData = new FormData(this);

            $.ajax({
                url: "{{ url('ordenes/insumos') }}/" + id,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#updateModal').modal('hide');
                    table.ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.success
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON.error || 'Ocurrió un error al procesar la solicitud'
                    });
                }
            });
        });

        // Eliminar insumo
        $(document).on('click', '.remove-btn', function() {
            let id = $(this).data('id');
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('ordenes/insumos') }}/" + id,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            table.ajax.reload();
                            Swal.fire('Eliminado', response.success, 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON.error || 'Ocurrió un error', 'error');
                        }
                    });
                }
            });
        });

        // Reset form on modal close
        $('#showModal').on('hidden.bs.modal', function () {
            $('#insumoForm')[0].reset();
            $('#insumo_id').val('').trigger('change');
            $('#stock-disponible').text('-');
            $('#unidad-medida').text('-');
        });
    });
</script>
