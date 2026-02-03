@if (isset($attribute))
    <v-address-component :attribute='@json($attribute)' :validations="'{{ $validations }}'"
        :data='@json(old($attribute->code) ?: $value)'>
        <!-- Addresses Shimmer -->
        <x-admin::shimmer.common.address />
    </v-address-component>
@endif

@pushOnce('scripts')
    <script type="text/x-template" id="v-address-component-template">
                <div class="flex gap-4 max-md:flex-wrap">
                    <div class="w-full">
                        <!-- Address (Textarea field) -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.control
                                type="textarea"
                                ::name="attribute['code'] + '[address]'"
                                rows="10"
                                v-model="address"
                                :label="trans('admin::app.common.custom-attributes.address')"
                                ::rules="attribute.is_required ? 'required|' + validations : validations"
                            />

                            <x-admin::form.control-group.error ::name="attribute['code'] + '[address]'" />

                            <x-admin::form.control-group.error ::name="attribute['code'] + '.address'" />
                        </x-admin::form.control-group>
                    </div>

                    <div class="grid w-full">
                        <!-- Country Field -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.control
                                type="select"
                                ::name="attribute['code'] + '[country]'"
                                ::rules="attribute.is_required ? 'required|' + validations : validations"
                                :label="trans('admin::app.common.custom-attributes.country')"
                                v-model="country"
                            >
                                <option value="">@lang('admin::app.common.custom-attributes.select-country')</option>

                                @foreach (core()->countries() as $country)
                                    <option value="{{ $country->code }}">{{ $country->name }}</option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error ::name="attribute['code'] + '[country]'" />

                            <x-admin::form.control-group.error ::name="attribute['code'] + '.country'" />
                        </x-admin::form.control-group>

                        <!-- State Field -->
                        <template v-if="haveStates()">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.control
                                    type="select"
                                    ::name="attribute['code'] + '[state]'"
                                    v-model="state"
                                    :label="trans('admin::app.common.custom-attributes.state')"
                                    ::rules="attribute.is_required ? 'required|' + validations : validations"
                                >
                                    <option value="">@lang('admin::app.common.custom-attributes.select-state')</option>

                                    <option 
                                        v-for='(state, index) in countryStates[country]' 
                                        :value="state.code"
                                    >
                                        @{{ state.name }}
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error ::name="attribute['code'] + '[state]'" />

                                <x-admin::form.control-group.error ::name="attribute['code'] + '.state'" />
                            </x-admin::form.control-group>
                        </template>

                        <template v-else>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.control
                                    type="text"
                                    ::name="attribute['code'] + '[state]'"
                                    :placeholder="trans('admin::app.common.custom-attributes.state')"
                                    :label="trans('admin::app.common.custom-attributes.state')"
                                    ::rules="attribute.is_required ? 'required|' + validations : validations"
                                    v-model="state"
                                >
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error ::name="attribute['code'] + '[state]'" />

                                <x-admin::form.control-group.error ::name="attribute['code'] + '.state'" />
                            </x-admin::form.control-group>
                        </template>

                        <!-- City Field -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.control
                                type="text"
                                ::name="attribute['code'] + '[city]'"
                                v-model="city"
                                :placeholder="trans('admin::app.common.custom-attributes.city')"
                                :label="trans('admin::app.common.custom-attributes.city')"
                                ::rules="attribute.is_required ? 'required|' + validations : validations"
                            />

                            <x-admin::form.control-group.error ::name="attribute['code'] + '[city]'"/>

                            <x-admin::form.control-group.error ::name="attribute['code'] + '.city'" />
                        </x-admin::form.control-group>

                        <!-- Postcode Field -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.control
                                type="text"
                                ::name="attribute['code'] + '[postcode]'"
                                v-model="postcode"
                                :placeholder="trans('admin::app.common.custom-attributes.postcode')"
                                :label="trans('admin::app.common.custom-attributes.postcode')"
                                ::rules="attribute.is_required ? 'required|postcode' : 'postcode'"
                            />

                            <x-admin::form.control-group.error ::name="attribute['code'] + '[postcode]'" />

                            <x-admin::form.control-group.error ::name="attribute['code'] + '.postcode'" />
                        </x-admin::form.control-group>
                    </div>
                </div>
            </script>

    <script type="module">
        app.component('v-address-component', {
            template: '#v-address-component-template',

            props: ['attribute', 'data', 'validations'],

            data() {
                return {
                    country: this.data?.country || '',

                    state: this.data?.state || '',

                    city: this.data?.city || '',

                    postcode: this.data?.postcode || '',

                    address: this.data?.address || '',

                    countryStates: @json(core()->groupedStatesByCountries()),
                };
            },

            mounted() {
                window.addEventListener('update-address-' + this.attribute.code, this.handleAddressUpdate);

                // If it's the billing/shipping address, check if we need to pre-fill content
                if (this.data) {
                    this.city = this.data.city || '';
                    this.postcode = this.data.postcode || '';
                    this.address = this.data.address || '';
                }
            },

            beforeUnmount() {
                window.removeEventListener('update-address-' + this.attribute.code, this.handleAddressUpdate);
            },

            methods: {
                haveStates() {
                    return !!this.countryStates[this.country]?.length;
                },

                handleAddressUpdate(e) {
                    const data = e.detail;

                    if (data.address !== undefined) this.address = data.address;
                    if (data.city !== undefined) this.city = data.city;
                    if (data.postcode !== undefined) this.postcode = data.postcode;
                    if (data.country !== undefined) this.country = data.country;

                    this.$nextTick(() => {
                        if (data.state !== undefined) {
                            this.state = data.state;
                        }
                    });
                },

                searchCep(cep) {
                    cep = cep.replace(/\D/g, '');

                    if (cep !== "") {
                        const validacep = /^[0-9]{8}$/;

                        if (validacep.test(cep)) {
                            this.$root.$emit('show-ajax-loader');

                            axios.get(`https://viacep.com.br/ws/${cep}/json/`)
                                .then(response => {
                                    this.$root.$emit('hide-ajax-loader');

                                    if (!response.data.erro) {
                                        this.address = response.data.logradouro;
                                        this.city = response.data.localidade;
                                        this.country = 'BR';

                                        this.$nextTick(() => {
                                            const uf = response.data.uf;

                                            // Handle state selection (dropdown or text)
                                            if (this.haveStates()) {
                                                const states = this.countryStates[this.country];
                                                const stateObj = states.find(s => s.code === uf);
                                                if (stateObj) {
                                                    this.state = stateObj.code;
                                                }
                                            } else {
                                                this.state = uf;
                                            }
                                        });
                                    }
                                })
                                .catch(() => {
                                    this.$root.$emit('hide-ajax-loader');
                                });
                        }
                    }
                }
            },

            watch: {
                postcode(newVal) {
                    if (newVal && newVal.length >= 8) {
                        this.searchCep(newVal);
                    }
                }
            }
        });
    </script>
@endPushOnce