@extends('layouts.main')

@section('title', __('Product Out'))

@include('components.assets._datatable')
@include('components.assets._select2')

@section('main-content')

    <div class="section-body">
        <h2 class="section-title">
            {{ __('Product Out List') }}
            <button type="button" class="ml-2 btn btn-success addProductOutsButton" data-toggle="modal"
                data-target="#productOutFormModal">
                <i class="fas fa-plus-circle"></i> Tambah
            </button>
        </h2>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="productOutDatatable" style="width:100%">
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <div class="modal fade" id="productOutFormModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="productFormModalLabel">{{ __('Add new product out') }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal_body_product">

                    <form method="POST" id="productOutForm" onsubmit="return validateInputs();">
                        @csrf

                        <input type="hidden" name="id" id="idIns">


                        <div class="row">
                            <div class="col form-group">
                                <label for="codeInsInput">{{ __('Code') }}</label>
                                <input type="text" class="form-control" name="code" id="codeInsInput">
                            </div>

                            <div class="col form-group">
                                <label for="typeSelect">{{ __('Type') }}</label>
                                <select id="typeSelect" name="type" required class="form-control select2"
                                    data-select2-opts='{"tags": "true"}'>
                                    @foreach ($types as $type)
                                        <option>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="atInput">{{ __('Date') }}</label>
                            <input type="date" class="form-control" name="at" required id="atInput">
                        </div>

                        <div class="form-group">
                            <label for="noteInsInput">{{ __('Note') }}</label>
                            <textarea class="form-control" name="note" id="noteInsInput" rows="3" style="height:100%;"></textarea>
                        </div>

                        <div class="px-1" style="overflow-x: auto">
                            <div id="productOutDetailsParent" style="width: 100%">
                                <div class="row m-0">
                                    <label class="col-4">{{ __('Name') }}</label>
                                    <label class="col-2">{{ __('Qty') }}</label>
                                    <label class="col-3">{{ __('Price') }}</label>
                                    <label class="col-2">{{ __('Subtotal') }}</label>
                                </div>
                            </div>
                        </div>


                        <div class="">
                            <a href="#" id="addProductButton" class="btn btn-success btn-sm mr-2"><i
                                    class="fas fa-plus"></i> {{ __('More') }}</a>
                            <a href="{{ route('products.index') }}">{{ __('New product out') }}?</a>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <button type="submit" form="productOutForm"
                            class="btn btn-outline-success">{{ __('Save') }}</button>
                    </div>
                    <form action="" method="post" id="deleteForm">
                        @csrf
                        @method('delete')
                        <input type="hidden" name="id" id="deleteInsId">
                        <button type="submit" class="btn btn-icon btn-outline-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let productOuts
        let productOutDatatable = $('#productOutDatatable')
        let selectedProductIds = [];

        const typeSelect = $('#typeSelect')
        const productFormModalLabel = $('#productFormModalLabel')

        const datatableSearch = tag => productOutDatatable.DataTable().search(tag).draw()
        const renderTagButton = text =>
            `<a href="#" onclick="datatableSearch('${text.split(' ')[0]}')" class="m-1 badge badge-primary">${text}</a>`

        function addProductInDetailRow(detail) {
            const nDetailInputSet = $('.detailInputSetDiv').length

            const detailRowDiv = document.createElement('div')
            detailRowDiv.setAttribute('class', 'form-group row mx-0 align-items-center detailInputSetDiv')
            productOutDetailsParent.append(detailRowDiv);

            function getProductSelect () {

                const initProductsSelect = $selectDom => $selectDom.select2({
                    dropdownParent: $('#modal_body_product'),
                    placeholder: '{{ __('Product') }}',
                    ajax: {
                        url: '/api/select2/ProductInDetail',
                        dataType: 'json',
                        beforeSend: function(request) {
                            request.setRequestHeader(
                                "Authorization",
                                'Bearer {{ Auth::user()->createToken('user_' . Auth::user()->id)->plainTextToken }}'
                            )
                        },
                        processResults: function(data) {
                            const theResults = data.map(productInDetail => {

                                return {
                                    id: productInDetail.id,
                                    text: `${productInDetail.product?.name} (${productInDetail.stock?.qty}) ${moment(productInDetail.at).format('DD-MM-YYYY')}`
                                }
                            })

                            return {
                                results: theResults
                            };
                        }
                    },
                    minimumInputLength: 3
                });

                const productSelectParentDiv = document.createElement('div')
                productSelectParentDiv.setAttribute('class', 'col-4 pl-0 pr-2')
                const $selectDom = $(`<select required placeholder="{{ __('Product name') }}"></select>`)
                    .addClass('form-control productSelect')
                    .attr('name', `details[${nDetailInputSet}][product_in_detail_id]`)
                $(productSelectParentDiv).append($selectDom)
                
                console.log(detail.product_in_detail)
                if (detail.product_in_detail_id) {
                    $selectDom.append(
                        `<option value="${detail.product_in_detail_id}">${detail.product_in_detail?.product.name}</option>`
                    );
                }

                initProductsSelect($selectDom);
                $selectDom.val(detail.product_in_detail_id).change();

                return productSelectParentDiv
            }

            function getSubtotalDiv() {
                const temp = document.createElement('div')
                temp.setAttribute('class', 'col-2 px-2')
                
                const subtotal = detail.qty * detail.price
                if (subtotal) {
                    temp.innerHTML = subtotal.toLocaleString('id', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0 
                    })
                }
                
                return temp;
            }


            $(detailRowDiv).append(getProductSelect())


            const qtyInputParentDiv = document.createElement('div')
            qtyInputParentDiv.setAttribute('class', 'col-2 px-2')
            $(qtyInputParentDiv).append(
                `<input class="form-control" name="details[${nDetailInputSet}][qty]" min="0" type="number" required placeholder="{{ __('Qty') }}" value="${detail.qty || ''}">`
            )

            const priceInputParentDiv = document.createElement('div')
            priceInputParentDiv.setAttribute('class', 'col-3 px-2')
            $(priceInputParentDiv).append(
                `<input class="form-control" name="details[${nDetailInputSet}][price]" min="0" type="number" required placeholder="{{ __('Price') }}" value="${detail.price || ''}">`
            )

            


            const getRemoveRowButtonParentDiv = () => {
                const temp = document.createElement('div')
                temp.setAttribute('class', 'col-1 pl-2 pr-0')
                $(temp).append($(
                    `<button class="btn btn-outline-danger btn-icon" tabindex="-1" onclick="this.parentNode.parentNode.remove()"><i class="fas fa-trash"></i></button>`
                ))

                return temp;
            }


            $(detailRowDiv).append(qtyInputParentDiv)
                .append(priceInputParentDiv)
                .append(getSubtotalDiv())

            if (nDetailInputSet !== 0) {
                $(detailRowDiv).append(getRemoveRowButtonParentDiv())
            }
        }

        const deletePutMethodInput = () => {
            $('[name="_method"][value="put"]').remove()
        }

        const addPutMethodInputInsert = () => {
            $('#productOutForm').append($('@method('put')'))
        }


        const setProductOutFormValue = productOut => {

            if (productOut.type) {
                const selectOpts = typeSelect.find('option');
                const optValues = selectOpts.map((i, select) => select.innerHTML);
                if ($.inArray(productOut.type, optValues) === -1) {
                    typeSelect.append(`<option>${productOut.type}</option>`);
                };
            }

            typeSelect.val(productOut.type || null).trigger('change');
            idIns.value = productOut.id || null
            codeInsInput.value = productOut.code || null
            noteInsInput.value = productOut.note || null
            deleteInsId.value = productOut.id || null

            if (productOut.at) {
                const dateObj = new Date(productOut.at);

                const month = dateObj.getMonth() + 1; //months from 1-12
                const day = dateObj.getDate();
                const year = dateObj.getFullYear();

                atInput.value = `${year}-${month}-${day}`
            } else {
                atInput.value = '{{ date('Y-m-d') }}'
            }

            productOut.details?.map(function(detail) {
                addProductInDetailRow(detail)
            })
        }


        $(document).on('click', '.addProductOutsButton', function() {
            deletePutMethodInput();
            setProductOutFormValue({});
            deleteForm.style.display = "none";

            $('.detailInputSetDiv').remove()


            for (let i = 0; i < 3; i++) {
                addProductInDetailRow({})
            }

            productOutForm.action = "{{ route('product-outs.store') }}";
        });

        $(document).on('click', '.editProductOutButton', function() {
            const productOutId = $(this).data('product-id');
            const productOut = productOuts.find(productOut => productOut.id === productOutId);
            deleteForm.style.display = "block";

            $('.detailInputSetDiv').remove()

            addPutMethodInputInsert();
            setProductOutFormValue(productOut);

            productOutForm.action = "{{ route('product-outs.update', '') }}/" + productOut.id;

            deleteForm.action = "{{ route('product-outs.destroy', '') }}/" + productOut
                .id;
        })

        $(document).on('click', '#addProductButton', function() {
            addProductInDetailRow({})
        })

        function validateInputs() {
            const selectedProductIds = []
            let isValid = true;

            $('.text-danger').remove();

            [...document.getElementsByClassName('productSelect')].map(selectEl => {
                if (selectedProductIds.includes(selectEl.value)) {
                    const errorTextDiv = document.createElement('div');
                    errorTextDiv.innerHTML = '{{ __('Product is duplicated') }}';
                    errorTextDiv.classList.add('text-danger')

                    selectEl.parentNode.append(errorTextDiv)
                    isValid = false;
                } else {
                    selectedProductIds.push(selectEl.value)
                }
            })
            
            return isValid;
        }

        $(document).ready(function() {
            $('#typeSelect').select2('destroy').select2({
                tags: true,
                dropdownParent: $('#modal_body_product')
            })

            productOutDatatable = productOutDatatable.dataTable({
                processing: true,
                search: {
                    return: true,
                },
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.1/i18n/{{ app()->getLocale() }}.json'
                },
                serverSide: true,
                ajax: {
                    url: '{{ action('\App\Http\Controllers\Api\DatatableController', 'ProductOut') }}?with=details.productInDetail.product',
                    dataSrc: json => {
                        productOuts = json.data;
                        return json.data;
                    },
                    beforeSend: function(request) {
                        request.setRequestHeader(
                            "Authorization",
                            'Bearer {{ Auth::user()->createToken('user_' . Auth::user()->id)->plainTextToken }}'
                        )
                    },
                    cache: true
                },
                order: [1, 'desc'],
                columns: [{
                    data: 'code',
                    title: '{{ __('Code') }}'
                }, {
                    data: 'at',
                    title: '{{ __('At') }}',
                    render: at => moment(at).format('DD-MM-YYYY')
                }, {
                    data: 'type',
                    title: '{{ __('Type') }}',
                }, {
                    orderable: false,
                    title: '{{ __('Items') }}',
                    data: 'details',
                    name: 'details.productInDetail.product.name',
                    width: '20%',
                    render: details => details.map(detail => renderTagButton(
                        `${detail.product_in_detail?.product.name} (${detail.qty})`)).join('')
                }, {
                    render: function(data, type, row) {
                        const editButton = $(
                            '<a class="btn-icon-custom" href="#"><i class="fas fa-cog"></i></a>'
                        )
                        editButton.attr('data-toggle', 'modal')
                        editButton.attr('data-target', '#productOutFormModal')
                        editButton.addClass('editProductOutButton');
                        editButton.attr('data-product-id', row.id)
                        return editButton.prop('outerHTML')
                    },
                    orderable: false
                }]
            });
        });
    </script>
@endpush
