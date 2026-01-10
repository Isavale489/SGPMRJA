<script>
    $(document).ready(function () {
        // Inicializar Select2 con dropdownParent para que funcione correctamente en modales
        $('#orden_id, #operario_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#showModal')
        });
        
        // Inicializar Select2 para el modal de edición
        $('#edit_orden_id, #edit_operario_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#editModal')
        });

        // DataTable
        var table = $('#produccion-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('produccion.diaria.data') }}",
            columns: [
                { 
                    data: 'fecha',
                    name: 'fecha',
                    className: 'align-middle'
                },
                { 
                    data: 'orden_producto',
                    name: 'orden_producto',
                    className: 'align-middle'
                },
                { 
                    data: 'operario_nombre',
                    name: 'operario_nombre',
                    className: 'align-middle'
                },
                { 
                    data: 'cantidad_producida',
                    name: 'cantidad_producida',
                    className: 'align-middle text-end'
                },
                { 
                    data: 'cantidad_defectuosa',
                    name: 'cantidad_defectuosa',
                    className: 'align-middle text-end'
                },
                { 
                    data: null,
                    className: 'align-middle',
                    render: function(data) {
                        let total = data.cantidad_producida + data.cantidad_defectuosa;
                        let eficiencia = ((data.cantidad_producida / total) * 100).toFixed(2);
                        let colorClass = eficiencia >= 90 ? 'bg-success' : 
                                       eficiencia >= 80 ? 'bg-info' : 
                                       eficiencia >= 70 ? 'bg-warning' : 'bg-danger';
                        return `<div class="progress" style="height: 15px;">
                            <div class="progress-bar ${colorClass}" role="progressbar" 
                                style="width: ${eficiencia}%" 
                                aria-valuenow="${eficiencia}" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                ${eficiencia}%
                            </div>
                        </div>`;
                    }
                },
                {
                data: 'id',
                name: 'actions',
                orderable: false,
                searchable: false,
                className: 'align-middle text-center',
                render: function(data) {
                return `
                <div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                <li>
                <button class="dropdown-item view-btn" data-id="${data}">
                <i class="ri-eye-fill align-bottom me-2 text-muted"></i> Ver
                </button>
                </li>
                <li>
                <button class="dropdown-item edit-btn" data-id="${data}">
                <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar
                </button>
                </li>
                <li>
                <button class="dropdown-item remove-btn" data-id="${data}">
                <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Eliminar
                </button>
                </li>
                </ul>
                </div>
                `;
                }
                }
            ],
            order: [[0, 'desc']],
            responsive: true,
            language: lenguajeData,
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-5"i><"col-sm-7"p>>'
        });

        // Registrar producción
        $('#produccionForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('produccion.diaria.store') }}",
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

        // Ver detalles
        $(document).on('click', '.view-btn', function() {
            let id = $(this).data('id');
            
            $.ajax({
                url: "{{ url('produccion/diaria') }}/" + id,
                method: 'GET',
                success: function(response) {
                    $('#view_orden_id').text(response.orden_id);
                    $('#view_producto').text(response.orden && response.orden.producto ? response.orden.producto.nombre : 'N/A');
                    $('#view_operario').text(response.operario.name);
                    $('#view_fecha').text(moment(response.created_at).format('DD/MM/YYYY'));
                    $('#view_cantidad_producida').text(response.cantidad_producida);
                    $('#view_cantidad_defectuosa').text(response.cantidad_defectuosa);
                    $('#view_observaciones').text(response.observaciones || 'Sin observaciones');

                    let total = response.cantidad_producida + response.cantidad_defectuosa;
                    let eficiencia = ((response.cantidad_producida / total) * 100).toFixed(2);
                    let colorClass = eficiencia >= 90 ? 'bg-success' : 
                                   eficiencia >= 80 ? 'bg-info' : 
                                   eficiencia >= 70 ? 'bg-warning' : 'bg-danger';
                    
                    $('#view_eficiencia')
                        .removeClass('bg-success bg-info bg-warning bg-danger')
                        .addClass(colorClass)
                        .css('width', eficiencia + '%')
                        .text(eficiencia + '%');

                    console.log('Intentando mostrar el modal de vista...');
                    $('#viewModal').modal('show');
                },
                error: function(xhr) {
                    Swal.fire('Error', 'No se pudo cargar la información', 'error');
                }
            });
        });

        // Mostrar modal de edición
        $(document).on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            
            $.ajax({
                url: "{{ url('produccion/diaria') }}/" + id,
                method: 'GET',
                success: function(response) {
                    $('#edit_id').val(response.id);
                    $('#edit_orden_id').text(response.orden_id);
                    $('#edit_producto').text(response.orden && response.orden.producto ? response.orden.producto.nombre : 'N/A');
                    $('#edit_operario').text(response.operario.name);
                    $('#edit_cantidad_producida').val(response.cantidad_producida);
                    $('#edit_cantidad_defectuosa').val(response.cantidad_defectuosa);
                    $('#edit_observaciones').val(response.observaciones);

                    $('#editModal').modal('show');
                },
                error: function(xhr) {
                    Swal.fire('Error', 'No se pudo cargar la información', 'error');
                }
            });
        });

        // Actualizar registro
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#edit_id').val();
            let formData = new FormData(this);

            $.ajax({
                url: "{{ url('produccion/diaria') }}/" + id,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#editModal').modal('hide');
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

        // Eliminar registro
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
                        url: "{{ url('produccion/diaria') }}/" + id,
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
            $('#produccionForm')[0].reset();
            $('#orden_id, #operario_id').val('').trigger('change');
        });
    });
</script>
