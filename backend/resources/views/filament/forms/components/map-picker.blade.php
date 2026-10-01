<div
    wire:ignore
    x-data="{
        map: null,
        marker: null,
        results: [],
        query: '',
        busy: false,
        async ensureLeaflet() {
            if (window.L) {
                return
            }

            if (!document.querySelector('link[data-nh-leaflet]')) {
                const css = document.createElement('link')
                css.rel = 'stylesheet'
                css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
                css.dataset.nhLeaflet = '1'
                document.head.appendChild(css)
            }

            await new Promise((resolve, reject) => {
                const script = document.createElement('script')
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
                script.onload = () => resolve()
                script.onerror = () => reject()
                document.head.appendChild(script)
            })
        },
        place(latitude, longitude, zoom = 17) {
            const point = [latitude, longitude]
            this.marker.setLatLng(point)
            this.map.setView(point, zoom)
            $wire.set('data.site.latitude', latitude.toFixed(7))
            $wire.set('data.site.longitude', longitude.toFixed(7))
        },
        error: '',
        async init() {
            const canvas = this.$refs.canvas
            if (!canvas || canvas._leaflet_id || canvas.dataset.nhBooting === '1' || this.map) {
                this.error = ''
                return
            }
            canvas.dataset.nhBooting = '1'
            try {
                await this.ensureLeaflet()
                if (canvas._leaflet_id || this.map) {
                    this.error = ''
                    return
                }
                delete L.Icon.Default.prototype._getIconUrl
                L.Icon.Default.mergeOptions({
                    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                })
                const latitude = parseFloat($wire.get('data.site.latitude')) || 14.7437965
                const longitude = parseFloat($wire.get('data.site.longitude')) || -17.4674915
                this.map = L.map(this.$refs.canvas, { scrollWheelZoom: true }).setView([latitude, longitude], 16)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap',
                }).addTo(this.map)
                this.marker = L.marker([latitude, longitude], { draggable: true }).addTo(this.map)
                this.marker.on('dragend', () => {
                    const point = this.marker.getLatLng()
                    this.place(point.lat, point.lng, this.map.getZoom())
                })
                this.map.on('click', (event) => {
                    this.place(event.latlng.lat, event.latlng.lng, this.map.getZoom())
                })
                setTimeout(() => this.map?.invalidateSize(), 150)
                setTimeout(() => this.map?.invalidateSize(), 500)
                this.error = ''
            } catch (e) {
                this.error = this.map || canvas._leaflet_id
                    ? ''
                    : 'La carte n’a pas pu se charger. Rechargez la page.'
            }
        },
        async search(text) {
            const query = (text || '').trim()
            if (query.length < 3) {
                this.results = []
                return
            }
            this.busy = true
            this.results = await $wire.searchPlace(query)
            this.busy = false
            if (this.results.length === 1) {
                this.choose(this.results[0])
            }
        },
        choose(result) {
            this.place(result.latitude, result.longitude)
            this.results = []
            this.query = result.label
        },
        searchWrittenAddress() {
            const address = $wire.get('data.site.address') || ''
            const city = $wire.get('data.site.city') || ''
            this.query = [address, city].filter(Boolean).join(', ')
            this.search(this.query)
        },
    }"
    x-init="init()"
    class="space-y-3"
>
    <div class="flex flex-col gap-2 sm:flex-row">
        <input
            x-model="query"
            type="search"
            placeholder="Rechercher un lieu, une rue, un quartier"
            class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:text-white dark:ring-white/20"
            x-on:keydown.enter.prevent="search(query)"
        />
        <button
            type="button"
            class="shrink-0 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white"
            x-on:click="search(query)"
        >
            Chercher
        </button>
        <button
            type="button"
            class="shrink-0 rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-950/10 dark:text-gray-200 dark:ring-white/20"
            x-on:click="searchWrittenAddress()"
        >
            Adresse saisie
        </button>
    </div>

    <p class="text-sm text-gray-500 dark:text-gray-400" x-show="busy">Recherche en cours…</p>

    <ul class="overflow-hidden rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10" x-show="results.length > 1">
        <template x-for="result in results" :key="result.latitude + ',' + result.longitude">
            <li>
                <button
                    type="button"
                    class="block w-full px-3 py-2 text-start text-sm hover:bg-gray-50 dark:hover:bg-white/5"
                    x-text="result.label"
                    x-on:click="choose(result)"
                ></button>
            </li>
        </template>
    </ul>

    <div
        x-ref="canvas"
        style="height: 22rem; width: 100%; background: #e4efe8; border-radius: 0.75rem; overflow: hidden;"
    ></div>
    <p class="text-sm text-danger-600" x-show="error" x-text="error"></p>
    <p class="text-sm text-gray-500 dark:text-gray-400">Cliquez sur la carte ou déplacez le repère pour choisir le lieu.</p>
</div>
