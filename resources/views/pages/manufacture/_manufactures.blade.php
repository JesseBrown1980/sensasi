@include('components.assets._select2')
@include('components.alpine-data._crud')
@include('components.alpine-data._datatable')

@push('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

<h2 class="section-title text-capitalize">
    {{ __('manufacture list') }}
    <button x-data type="button" @@click="$dispatch('manufacture:open-modal', null)"
        class="ml-2 btn btn-primary">
        <i class="fas fa-plus-circle"></i> {{ __('Add') }}
    </button>
</h2>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table x-data="dataTable(manufactureInDatatableConfig)" @@manufacture:datatable-draw.document="draw"
                class="table table-striped" style="width:100%">
            </table>
        </div>
    </div>
</div>

@push('modal')
    <div x-data="crud(manufactureInCrudConfig)" @@manufacture:open-modal.document="openModal"
        @@manufacture:set-data-list.document="setDataList">
        <x-_modal centered>

            <form method="POST" @@submit.prevent="submitForm" id="{{ uniqid() }}"
                x-effect="formData.id; $nextTick(() => formData.manufacture?.id && formData.id ? $el.disableAll() : $el.enableAll())">

                <div class="row">
                    <div class="col form-group" x-id="['text-input']">
                        <label :for="$id('text-input')">{{ __('validation.attributes.code') }}</label>
                        <input type="text" class="form-control" x-model="formData.code" :id="$id('text-input')">
                    </div>

                    <div class="col form-group" x-id="['input']">
                        <label :for="$id('input')">{{ __('validation.attributes.at') }}</label>
                        <input type="date" class="form-control" required :id="$id('input')"
                            :value="formData.at ? moment(formData.at).format('YYYY-MM-DD') : ''"
                            @@change="formData.at = $event.target.value"
                            x-effect="formData.material_out?.details;
                            const detailDates = formData.material_out?.details?.map(detail => detail.material_in_detail?.material_in.at).filter(date => date);
                            
                            if (!detailDates || detailDates.length === 0) {
                                return;
                            }

                            if (detailDates?.length === 1) {
                                $el.min = detailDates[0] ? moment(detailDates[0]).format('YYYY-MM-DD') : null;
                                return;
                            }

                            $el.min = moment(detailDates.reduce((a,b) => a > b ? a : b).substr(0, 10)).format('YYYY-MM-DD');
                        ">
                    </div>
                </div>

                <div class="form-group" x-id="['textarea']">
                    <label :for="$id('textarea')">{{ __('validation.attributes.note') }}</label>
                    <textarea x-model="formData.note" class="form-control" name="note" :id="$id('textarea')" rows="3"
                        style="height:100%;"></textarea>
                </div>

                <!-- TABS LIST -->
                <ul class="nav nav-tabs text-capitalize" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="product-manufacture-material-out-tab" data-toggle="tab" data-target="#material-out"
                            type="button" role="tab" aria-controls="material-out"
                            aria-selected="true">{{ __('material out') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="product-in-tab" data-toggle="tab" data-target="#product-in"
                            type="button" role="tab" aria-controls="product-in"
                            aria-selected="false">{{ __('product in') }}</button>
                    </li>
                </ul>

                <!-- TAB CONTENT -->
                <div class="tab-content">

                    <!-- MATERIAL OUT TAB CONTENT -->
                    <div class="tab-pane fade show active" id="material-out" role="tabpanel"
                        aria-labelledby="product-manufacture-material-out-tab">

                        <div x-data="{ total_price: 0 }"
                            x-effect="total_price = 0; formData.details?.forEach(detail => total_price += (detail.qty * detail.material_in_detail?.price || 0));">

                            <div class="form-group">
                                <label class="text-capitalize">{{ __('items') }}</label>

                                <div class="list-group mb-4">
                                    <template x-for="(detail, $i) in formData.material_out?.details">
                                        <div class="list-group-item p-4">
                                            <select class="form-control" x-init="initMaterialInDetailSelect2;
                                            $($el).on('select2:select', (e) => {
                                                detail.material_in_detail = $(e.target).select2('data')[0].materialInDetail;
                                                detail.material_in_detail_id = e.target.value;
                                            })"
                                                x-effect="materialInDetailSelect2Effect($el, detail.material_in_detail_id, detail.material_in_detail)"
                                                required></select>

                                            <div class="row my-3">
                                                <div class="col d-flex align-items-center">
                                                    <label
                                                        class="mb-0 mr-2">{{ __('validation.attributes.price') }}</label>

                                                    <div x-data="{ priceText: null }"
                                                        x-effect="priceText = intToCurrency(detail.material_in_detail?.price || 0)"
                                                        x-text="priceText">
                                                    </div>
                                                </div>

                                                <div class="col d-flex align-items-center" x-id="['input']">
                                                    <label :for="$id('input')"
                                                        class="mb-0 mr-2">{{ __('validation.attributes.qty') }}</label>
                                                    <div class="input-group input-group-sm">
                                                        <input :id="$id('input')" class="form-control form-control-sm"
                                                            type="number" x-model="detail.qty" min="1"
                                                            :max="formData.id ? undefined : detail.material_in_detail?.stock
                                                                .qty"
                                                            required>

                                                        <div class="input-group-append">
                                                            <span class="input-group-text h-auto" x-data="{ unit: '' }"
                                                                x-effect="unit = detail.material_in_detail?.material.unit"
                                                                x-show="unit" x-text="unit"></span>
                                                        </div>



                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-end">
                                                <strong x-init="$($el).tooltip({ boundary: 'window' })" title="{{ __('Subtotal') }}"
                                                    x-data="{ subtotal_price: 0 }"
                                                    x-effect="subtotal_price = (detail.qty * detail.material_in_detail?.price || 0)"
                                                    x-text="intToCurrency(subtotal_price)">
                                                </strong>

                                                <button type="button" class="btn btn-icon btn-outline-danger"
                                                    tabindex="-1"
                                                    @@click.prevent="formData.material_out.details.splice($i, 1)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="form-group d-flex justify-content-between">
                                <div>
                                    <label class="text-capitalize">{{ __('total') }}</label>
                                    <div>
                                        <strong
                                            x-effect="$data.total_in_price = formData.material_out?.details?.reduce((a, b) => a + b.material_in_detail?.price * b.qty, 0)"
                                            x-text="intToCurrency($data.total_in_price || 0)"></strong>
                                    </div>
                                </div>

                                <div>
                                    <a href="javascript:;"
                                        @@click="formData.material_out.details.push({})"
                                        class="badge badge-danger text-capitalize"><i class="fas fa-plus mr-1"></i>
                                        {{ __('add material out') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PRODUCT TAB CONTENT -->
                    <div class="tab-pane fade" id="product-in" role="tabpanel" aria-labelledby="product-in-tab">
                        <div class="form-group">
                            <label class="text-capitalize">{{ __('items') }}</label>

                            <div class="list-group">
                                <template x-for="(detail, $i) in formData.product_in?.details">
                                    <div class="list-group-item p-4">
                                        <select class="form-control" :disabled="detail.out_details?.length > 0"
                                            :data-exclude-enabling="detail.out_details?.length > 0"
                                            x-effect="$($el).val(detail.product_id).change();" x-init="$($el).select2({
                                                dropdownParent: $el.closest('.modal-body'),
                                                placeholder: '{{ __('Product') }}',
                                                data: products.map(product => ({
                                                    id: product.id,
                                                    text: '',
                                                    product: product
                                                })),
                                                templateResult: productSelect2TemplateResultAndSelection,
                                                templateSelection: productSelect2TemplateResultAndSelection,
                                            }).on('select2:select', (e) => {
                                                detail.product_id = e.target.value;
                                            });"
                                            required>
                                        </select>

                                        <div class="row my-3">
                                            <div class="col d-flex align-items-center" x-id="['text-input']">
                                                <label :for="$id('text-input')"
                                                    class="mb-0 mr-2">{{ __('validation.attributes.qty') }}</label>
                                                <div class="input-group input-group-sm">
                                                    <input :id="$id('text-input')" class="form-control form-control-sm"
                                                        type="number" x-model="detail.qty"
                                                        :min="detail.out_details?.reduce((a, b) => a + b.qty, 0)" required>

                                                    <div class="input-group-append">
                                                        <span class="input-group-text h-auto" x-data="{ unit: '' }"
                                                            x-effect="unit = products.find(product => detail.product_id == product.id)?.unit"
                                                            x-show="unit" x-text="unit"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col d-flex align-items-center" x-id="['text-input']">
                                                <label :for="$id('text-input')"
                                                    class="mb-0 mr-2">{{ __('validation.attributes.price') }}</label>

                                                <input :id="$id('text-input')" x-model="detail.price"
                                                    class="form-control form-control-sm" min="0" type="number"
                                                    step="any" required>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-end">
                                            <strong x-init="$($el).tooltip()" title="{{ __('subtotal') }}"
                                                x-data="{ subtotal_price: 0 }"
                                                x-effect="subtotal_price = detail.price * detail.qty"
                                                x-text="intToCurrency(subtotal_price || 0)">
                                            </strong>

                                            <x-_disabled-delete-button x-show="detail.out_details?.length > 0"
                                                x-init="$($el).tooltip()" :title="__('cannot be deleted. Product(s) has been used')" />

                                            <button type="button" class="btn btn-icon btn-outline-danger" tabindex="-1"
                                                x-show="!(detail.out_details?.length > 0)"
                                                :disabled="detail.out_details?.length > 0"
                                                @@click.prevent="formData.product_in.details.splice($i, 1)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-between">
                            <div>
                                <label class="text-capitalize">{{ __('total') }}</label>
                                <div>
                                    <strong
                                        x-effect="$data.total_out_price = formData.product_in?.details?.reduce((a, b) => a + b.qty * b.price, 0)"
                                        x-text="intToCurrency(total_out_price || 0)"></strong>

                                    <div>
                                        <a x-init="$($el).tooltip({ boundary: 'window' })" title="{{ __('Auto calculate from material outs') }}"
                                            @@click="formData.product_in?.details.forEach(detail => detail.price = total_in_price / formData.product_in.details.length / (detail.qty || (detail.qty = 1)))"
                                            class="text-warning">
                                            <i class="fas fa-magic"></i>
                                        </a>

                                        <span
                                            x-show="total_in_price && total_out_price && intToCurrency(total_in_price) != intToCurrency(total_out_price)"
                                            x-transition class="text-danger">
                                            {{ __('Total cost is not equal to total price') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <a href="javascript:;" @@click="formData.product_in.details.push({})"
                                    class="badge badge-success mr-3 text-capitalize"><i class="fas fa-plus mr-1"></i>
                                    {{ __('add product in') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            @slot('footer')
                <div>
                    <button type="submit" class="btn btn-success"
                        :disabled="intToCurrency($data.total_in_price || ($data.total_in_price = 0)) != intToCurrency(
                            $data.total_out_price || ($data.total_out_price = 0))"
                        :class="isFormLoading ? 'btn-progress' : ''" :form="htmlElements.form.id">
                        {{ __('Save') }}
                    </button>

                    <button @@click="restore()" x-show="isDirty"
                        class="btn btn-icon btn-outline-warning"><i class="fas fa-undo"></i></button>
                </div>

                <div>
                    <x-_disabled-delete-button
                        x-show="formData.product_in?.details?.find(detail => detail.out_details?.length > 0)"
                        x-init="$($el).tooltip()" :title="__('cannot be deleted. Product(s) has been used')" />

                    <button type="button" class="btn btn-icon btn-outline-danger" tabindex="-1"
                        @@click="openDeleteModal"
                        x-show="formData.id && !(formData.product_in?.details?.find(detail => detail.out_details?.length > 0))">
                        <i class="fas fa-trash"></i>
                    </button>
                @endslot
        </x-_modal>

        <x-_delete-modal x-on:submit.prevent="submitDelete" />
    </div>
@endpush

@push('js')
    <script>
        const products = @json(App\Models\Product::all());

        function productSelect2TemplateResultAndSelection(data) {

            if (!data.id) {
                return data.text;
            }

            const product = data.product;

            const brandPrinted = product?.brand ?
                '<small class=\'text-muted\'>(' +
                product?.brand + ')</small>' : '';

            const codePrinted = product?.code ?
                '<small class=\'text-muted\'><b>' +
                product?.code + '</b></small> - ' : '';

            return $(`
				<div>
					${codePrinted}
					${product?.name}
					${brandPrinted}
				</div>
			`);
        }

        function initMaterialInDetailSelect2() {
            $(this.$el).select2({
                dropdownParent: $(this.$el).closest('.modal-body'),
                placeholder: '{{ __('Material') }}',
                ajax: {
                    delay: 750,
                    cache: true,
                    url: '/api/select2/MaterialInDetail',
                    dataType: 'json',
                    beforeSend: function(request) {
                        request.setRequestHeader(
                            'Authorization',
                            'Bearer {{ decrypt(request()->cookie('api-token')) }}'
                        )
                    },
                    processResults: materialInDetail => {
                        const data = materialInDetail.map(materialInDetail => {
                            return {
                                id: materialInDetail.id,
                                text: null,
                                materialInDetail: materialInDetail
                            }
                        });

                        return {
                            results: data
                        };
                    }
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    const datePrinted = data.materialInDetail?.material_in.at ? moment(data.materialInDetail
                        .material_in.at).format('DD-MM-YYYY') : null;

                    return $(`
                        <div style='line-height: 1em;'>
                            <small>${datePrinted}</small>
                            <p class='my-0' stlye='font-size: 1.1em'><b>${data.materialInDetail.material.id_for_human}<b></p>
                            <small><b>${data.materialInDetail.stock.qty}</b>/${data.materialInDetail.qty} ${data.materialInDetail.material.unit} @ ${intToCurrency(data.materialInDetail.price)}</small>
                        </div>
                    `);
                },
                templateSelection: function(data) {
                    if (data.text === '{{ __('Material') }}') {
                        return data.text;
                    }

                    const materialInDetail = data.materialInDetail || data.element.materialInDetail;

                    const codePrinted = materialInDetail.material?.code ?
                        '<small class=\'text-muted\'><b>' +
                        materialInDetail.material?.code + '</b></small> - ' : '';
                    const brandPrinted = materialInDetail.material?.code ?
                        '<small class=\'text-muted\'>(' +
                        materialInDetail.material?.brand + ')</small>' : '';
                    const namePrinted = materialInDetail.material?.name;
                    const atPrinted = materialInDetail.material_in?.at ? moment(materialInDetail.material_in
                        ?.at).format('DD-MM-YYYY') : null;

                    return $(`
                        <div>
                            ${codePrinted}
                            ${namePrinted}
                            ${brandPrinted}
                            <small class='text-muted ml-2'>
                                ${atPrinted}
                            </small>
                        </div>
                    `);
                },
                minimumInputLength: 3
            });
        }

        function materialInDetailSelect2Effect($el, material_in_detail_id, material_in_detail) {
            if ($($el).find(`option[value="${material_in_detail_id}"]`).length) {
                $($el).val(material_in_detail_id).trigger('change');
            } else {
                var newOption = new Option('', material_in_detail_id, true, true);
                newOption.materialInDetail = material_in_detail;
                $($el).append(newOption);
            }
        }

        const manufactureInCrudConfig = {
            blankData: {
                'id': null,
                'code': null,
                'at': null,
                'note': null,
                'material_out': {
                    'details': [{}]
                },
                'product_in': {
                    'details': [{}]
                }
            },

            dispatchEventsAfterSubmit: [
                'manufacture:datatable-draw'
            ],

            routes: {
                store: '{{ route('manufactures.store') }}',
                update: '{{ route('manufactures.update', '') }}/',
                destroy: '{{ route('manufactures.destroy', '') }}/',
            },

            getTitle(hasnotId) {
                return !hasnotId ? `{{ __('add new manufacture') }}` : `{{ __('edit manufacture') }}: ` + this
                    .formData.id_for_human;
            },

            getDeleteTitle() {
                return `{{ __('delete manufacture') }}: ` + this.formData.id_for_human;
            }
        };

        const manufactureInDatatableConfig = {
            serverSide: true,
            setDataListEventName: 'manufacture:set-data-list',
            token: '{{ decrypt(request()->cookie('api-token')) }}',
            ajaxUrl: '{{ $manufactureDatatableAjaxUrl }}',
            columns: [{
                data: 'code',
                title: '{{ __('validation.attributes.code') }}'
            }, {
                data: 'at',
                title: '{{ __('validation.attributes.at') }}',
                render: at => moment(at).format('DD-MM-YYYY')
            }, {
                data: 'note',
                title: '{{ __('validation.attributes.note') }}'
            }, {
                orderable: false,
                title: '{{ __('material') }}',
                data: 'material_out.details',
                name: 'materialOut.details.materialInDetail.material.name',
                render: details => details.map(detail => {
                    const materialName = detail.material_in_detail?.material.name;
                    const detailQty = detail.qty;

                    const text = `${materialName} (${detailQty})`;
                    return `<a href="javascript:;" class="m-1 badge badge-danger" @click="search('${materialName}')">${text}</a>`;
                }).join('')
            }, {
                orderable: false,
                title: '{{ __('product') }}',
                data: 'product_in.details',
                name: 'productIn.details.product.name',
                render: details => details.map(detail => {
                    const productName = detail.product?.name;
                    const stockQty = detail.stock?.qty;
                    const detailQty = detail.qty;

                    const text = `${productName} (${stockQty}/${detailQty})`;
                    return `<a href="javascript:;" class="m-1 badge badge-success" @click="search('${productName}')">${text}</a>`;
                }).join('')
            }, {
                render: function(data, type, row) {
                    return `<a class="btn-icon-custom" href="javascript:;" @click="$dispatch('manufacture:open-modal', ${row.id})"><i class="fas fa-cog"></i></a>`;
                },
                orderable: false
            }]
        };
    </script>
@endpush
